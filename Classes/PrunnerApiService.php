<?php

namespace Flowpack\Prunner;

use Firebase\JWT\JWT;
use Flowpack\Prunner\Dto\Job;
use Flowpack\Prunner\Dto\JobLogs;
use Flowpack\Prunner\Dto\PipelinesAndJobsResponse;
use Flowpack\Prunner\ValueObject\JobId;
use Flowpack\Prunner\ValueObject\PipelineName;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @Flow\Scope("singleton")
 */
class PrunnerApiService
{

    /**
     * @Flow\InjectConfiguration(path="apiBaseUrl")
     * @var string
     */
    protected $apiBaseUrl;

    /**
     * @Flow\InjectConfiguration(path="directory")
     * @var string
     */
    protected $directory;

    /**
     * @Flow\InjectConfiguration(path="configFile")
     * @var string
     */
    protected $configFile;

    /**
     * @Flow\InjectConfiguration(path="jwtSecret")
     * @var string
     */
    protected $jwtSecret;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Security\Context
     */
    protected $context;

    public function loadPipelinesAndJobs(): PipelinesAndJobsResponse
    {
        $resultString = $this->apiCall('GET', 'pipelines/jobs', null)->getBody()->getContents();
        try {
            $result = json_decode($resultString, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Could not decode JSON response from prunner API: ' . $e->getMessage(), 1707485801);
        }
        return PipelinesAndJobsResponse::fromJsonArray($result);
    }

    /**
     * @throws \JsonException
     */
    public function loadJobDetail(JobId $jobId): ?Job
    {
        $resultString = $this->apiCall('GET', 'job/detail?' . http_build_query(['id' => $jobId->getId()]), null)->getBody()->getContents();
        try {
            $result = json_decode($resultString, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Could not decode JSON response from prunner API: ' . $e->getMessage(), 1707485815);
        }
        if (isset($result['error'])) {
            return null;
        }
        return Job::fromJsonArray($result);
    }

    public function loadJobLogs(JobId $jobId, string $taskName): JobLogs
    {
        $resultString = $this->apiCall('GET', 'job/logs?' . http_build_query(['id' => $jobId->getId(), 'task' => $taskName]), null)->getBody()->getContents();
        try {
            $result = json_decode($resultString, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Could not decode JSON response from prunner API: ' . $e->getMessage(), 1707485821);
        }
        return JobLogs::fromJsonArray($result);
    }

    /**
     * @throws \JsonException
     */
    public function schedulePipeline(PipelineName $pipeline, array $variables): JobId
    {
        $response = $this->apiCall('POST', 'pipelines/schedule', json_encode([
            'pipeline' => $pipeline->getName(),
            'variables' => $variables
        ], JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT));
        if ($response->getStatusCode() !== 202) {
            throw new \RuntimeException('Scheduling a new pipeline run should have returned status code 202, but got: ' . $response->getStatusCode());
        }
        $contents = $response->getBody()->getContents();
        try {
            $result = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Could not decode JSON response from prunner API: ' . $e->getMessage(), 1707485793);
        }
        return JobId::create($result['jobId']);
    }

    public function cancelJob(Job $job): void
    {
        $response = $this->apiCall('POST', 'job/cancel?' . http_build_query(['id' => $job->getId()->getId()]), '');
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Cancelling a job should have returned status code 200, but got: ' . $response->getStatusCode());
        }
    }

    /**
     * Low-Level method, handling only the authentication.
     *
     * @throws GuzzleException
     */
    public function apiCall(string $method, string $subpath, ?string $body): ResponseInterface
    {
        $url = rtrim($this->apiBaseUrl, '/') . '/' . $subpath;
        if (!empty($this->jwtSecret)) {
            $jwtSecret = $this->jwtSecret;
        } else {
            $jwtSecret = $this->loadJwtSecretFromConfigFile();
        }

        // There are usecases where we want to call prunner from the CLI. We don't have an initialized user there, thus we
        // hardcode it to 'cli' in these cases. The account identifier in prunner is only used for e.g. log messages.
        $account = $this->context->isInitialized() ? $this->context->getAccount() : null;
        $accountIdentifier = $account ? $account->getAccountIdentifier() : 'cli';
        // Generate JWT token on the fly with expiration in 60 seconds
        $authToken = JWT::encode(['sub' => $accountIdentifier, 'exp' => time() + 60], $jwtSecret, 'HS256');
        $client = new Client();
        return $client->request($method, $url, ['headers' => ['Authorization' => 'Bearer ' . $authToken], 'body' => $body, 'http_errors' => false]);
    }

    private function loadJwtSecretFromConfigFile(): string
    {
        if ($this->configFile && file_exists($this->configFile)) {
            $path = $this->configFile;
        } elseif ($this->directory && file_exists($this->directory . '/.prunner.yml')) {
            $path = $this->directory . '/.prunner.yml';
        } else {
            throw new \RuntimeException("Failed to locate prunner config file at " . $this->configFile . " or " . $this->directory . '/.prunner.yml');
        }
        try {
            // Try to parse prunner config to get JWT secret
            $config = Yaml::parseFile($path);
            $jwtSecret = $config['jwt_secret'];
        } catch (ParseException $e) {
            throw new \RuntimeException('Invalid prunner configuration (could not read JWT secret)');
        }
        return $jwtSecret;
    }
}
