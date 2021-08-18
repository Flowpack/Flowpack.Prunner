<?php

namespace Flowpack\Prunner;

use Firebase\JWT\JWT;
use Flowpack\Prunner\Dto\Job;
use Flowpack\Prunner\Dto\JobLogs;
use Flowpack\Prunner\Dto\PipelinesAndJobsResponse;
use Flowpack\Prunner\ValueObject\JobId;
use Flowpack\Prunner\ValueObject\PipelineName;
use GuzzleHttp\Client;
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
        $result = json_decode($resultString, true);
        return PipelinesAndJobsResponse::fromJsonArray($result);
    }

    public function loadJobDetail(JobId $jobId): Job
    {
        $resultString = $this->apiCall('GET', 'job/detail?' . http_build_query(['id' => $jobId->getId()]), null)->getBody()->getContents();
        $result = json_decode($resultString, true);
        return Job::fromJsonArray($result);
    }

    public function loadJobLogs(JobId $jobId, string $taskName): JobLogs
    {
        $resultString = $this->apiCall('GET', 'job/logs?' . http_build_query(['id' => $jobId->getId(), 'task' => $taskName]), null)->getBody()->getContents();
        $result = json_decode($resultString, true);
        return JobLogs::fromJsonArray($result);
    }

    public function schedulePipeline(PipelineName $pipeline, array $variables): JobId
    {
        $response = $this->apiCall('POST', 'pipelines/schedule', json_encode([
            'pipeline' => $pipeline->getName(),
            'variables' => $variables
        ]));
        if ($response->getStatusCode() !== 202) {
            throw new \RuntimeException('Scheduling a new pipeline run should have returned status code 202, but got: ' . $response->getStatusCode());
        }
        $contents = $response->getBody()->getContents();
        $tmp = json_decode($contents, true);
        return JobId::create($tmp['jobId']);
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
     * @param string $method
     * @param string $subpath
     * @param string|null $body
     */
    public function apiCall(string $method, string $subpath, ?string $body): ResponseInterface
    {
        $url = rtrim($this->apiBaseUrl, '/') . '/' . $subpath;
        if (!empty($this->jwtSecret)) {
            $jwtSecret = $this->jwtSecret;
        } else {
            try {
                // Try to parse prunner config to get JWT secret
                $config = Yaml::parseFile($this->directory . '/.prunner.yml');
                $jwtSecret = $config['jwt_secret'];
            } catch (ParseException $e) {
                throw new \RuntimeException('Invalid prunner configuration (could not read JWT secret)');
            }
        }
        $accountIdentifier = $this->context->getAccount()->getAccountIdentifier();
        // Generate JWT token on the fly with expiration in 60 seconds
        $authToken = JWT::encode(['sub' => $accountIdentifier, 'exp' => time() + 60], $jwtSecret, 'HS256');
        $client = new Client();
        return $client->request($method, $url, ['headers' => ['Authorization' => 'Bearer ' . $authToken], 'body' => $body, 'http_errors' => false]);
    }

}
