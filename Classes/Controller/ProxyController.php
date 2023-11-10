<?php
namespace Flowpack\Prunner\Controller;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Neos\Flow\Annotations as Flow;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ProxyController extends \Neos\Flow\Mvc\Controller\ActionController
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

    public function indexAction(string $path, string $subpath)
    {
        // Simple workaround to have slashes in route parts...
        $apiPath = $path;
        if ($subpath !== '') {
            $apiPath .= '/' . $subpath;
        }
        $queryString = $this->request->getHttpRequest()->getUri()->getQuery();
        $url = rtrim($this->apiBaseUrl, '/') . '/' . $apiPath;

        if (strlen($queryString) > 0) {
            $url .= '?' . $queryString;
        }


        // Create JWT token for user

        if (!empty($this->jwtSecret)) {
            $jwtSecret = $this->jwtSecret;
        } else {
            try {
                // Try to parse prunner config to get JWT secret
                $jwtSecret = $this->loadJwtSecretFromConfigFile();
            } catch (\RuntimeException $e) {
                $this->response->setContentType('application/json');
                $this->response->setStatusCode(500);
                return json_encode(['error' => 'Invalid prunner configuration (could not read JWT secret)']);
            }
        }

        $accountIdentifier = $this->context->getAccount()->getAccountIdentifier();

        // Generate JWT token on the fly with expiration in 60 seconds
        $authToken = JWT::encode([
            'sub' => $accountIdentifier,
            'exp' => time() + 60
        ], $jwtSecret, 'HS256');

        $client = new Client();
        $response = $client->request($this->request->getHttpRequest()->getMethod(), $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $authToken
            ],
            'body' => $this->request->getHttpRequest()->getBody(),
            'http_errors' => false
        ]);

        $this->response->setContentType('application/json');
        $this->response->setStatusCode($response->getStatusCode());

        return $response->getBody();
    }

    /**
     * @return string
     */
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
