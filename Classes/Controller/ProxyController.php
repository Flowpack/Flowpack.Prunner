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
        $url = rtrim($this->apiBaseUrl, '/') . '/' . $apiPath;

        // Create JWT token for user

        if (!empty($this->jwtSecret)) {
            $jwtSecret = $this->jwtSecret;
        } else {
            try {
                // Try to parse prunner config to get JWT secret
                $config = Yaml::parseFile($this->directory . '/.prunner.yml');
                $jwtSecret = $config['jwt_secret'];
            } catch (ParseException $e) {
                $this->response->setContentType('application/json');
                $this->response->setStatusCode(500);
                return json_encode(['error' => 'Invalid prunner configuration']);
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
}
