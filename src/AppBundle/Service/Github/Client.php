<?php

namespace AppBundle\Service\Github;

use Buzz\Message\RequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Client
{
    /**
     * @var Buzz\Browser
     */
    protected $client;

    /**
     * @var string
     */
    protected $siteUrl;

    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param Buzz\Browser $client
     */
    public function __construct(
        \Buzz\Browser $client,
        $siteUrl,
        $apiUrl,
        $clientId,
        $clientSecret,
        $accessToken = null,
        EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->client = $client;
        $this->siteUrl = $siteUrl;
        $this->apiUrl = $apiUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken = $accessToken;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function authUser($code) : array
    {
        $authResponse = $this->request(
            $this->siteUrl.'login/oauth/access_token',
            RequestInterface::METHOD_POST,
            [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code
            ]
        );

        if (is_null($authResponse) || empty($authResponse['access_token'])) {
            throw new Exception\AuthFailException(sprintf(
                'Github authentication failed, %s',
                lcfirst($authResponse['error_description'] ?? 'unknow reason')
            ));
        }

        $this->accessToken = $authResponse['access_token'];

        if (!is_null($this->eventDispatcher)) {
            $this->eventDispatcher->dispatch(
                Event\UserLogin::NAME,
                $event = new Event\UserLogin($this, $authResponse)
            );
            $authResponse = $event->getResponse();
        }

        return $authResponse;
    }

    public function getCurrentUser() : array
    {
        return $this->apiRequest('user');
    }

    public function apiRequest($url, $method = RequestInterface::METHOD_GET, $content = '', $headers = []) : array
    {
        return $this->request($this->apiUrl.$url, $method, $content, $headers);
    }

    public function request($url, $method = RequestInterface::METHOD_GET, $content = '', $headers = []) : array
    {
        if (!is_null($this->accessToken)) {
            $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?').'access_token='.$this->accessToken;
        }

        try {
            return json_decode(
                $this->client->call(
                    $url,
                    $method,
                    array_merge($headers, ['accept' => 'application/json']),
                    $content
                )->getContent(),
                true
            );
        } catch (\Buzz\Exception\RequestException $e) {
            throw new Exception\NetworkException(sprintf(
                'An error occurred while trying to join Github, %s',
                lcfirst($e->getMessage())
            ));
        }
    }
}
