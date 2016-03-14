<?php

namespace Simplon\Twitter;

use Simplon\Request\Request;
use Simplon\Request\RequestResponse;

/**
 * Class Twitter
 * @package Simplon\Twitter
 */
class Twitter
{
    const URL_API = 'https://api.twitter.com';
    const URL_UPLOAD = 'https://upload.twitter.com';
    const API_VERSION = '1.1';
    const OAUTH_VERSION = '1.0';
    const PATH_OAUTH_REQUEST_TOKEN = '/oauth/request_token';
    const PATH_OAUTH_AUTHENTICATE = '/oauth/authenticate';
    const PATH_OAUTH_ACCESS_TOKEN = '/oauth/access_token';
    const PATH_MEDIA = '/media/upload';

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiSecret;

    /**
     * @var array
     */
    protected $oauthCredentials = [];

    /**
     * @var string
     */
    protected $oauthToken;

    /**
     * @var string
     */
    protected $oauthSecret;

    /**
     * @param string $apiKey
     * @param string $apiSecret
     */
    public function __construct($apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * @param string $token
     * @param string $secret
     */
    public function setOauthTokens($token, $secret)
    {
        $this->oauthToken = $token;
        $this->oauthSecret = $secret;
    }

    /**
     * @param string $callbackUrl
     *
     * @return OauthRequestTokenVo
     * @throws TwitterException
     */
    public function requestOauthRequestToken($callbackUrl)
    {
        $baseUrl = $this->buildUrl(
            [
                self::URL_API,
                self::PATH_OAUTH_REQUEST_TOKEN,
            ]
        );

        // create signature
        $signature = $this
            ->getTwitterSignature()
            ->renderForOauthRequestToken($baseUrl, $callbackUrl);

        // send request to twitter
        $response = $this->formatResponse(
            $this->sendPostRequest($baseUrl, $signature)
        );

        // verify response
        if (isset($response['oauth_callback_confirmed']) === false || $response['oauth_callback_confirmed'] === false)
        {
            throw new TwitterException('oauth_callback_confirmed returned FALSE. Required value should be TRUE');
        }

        return (new OauthRequestTokenVo())->fromArray($response);
    }

    /**
     * @param string $oauthToken
     * @param bool $forceLogin
     *
     * @return string
     */
    public function getAuthenticationUrl($oauthToken, $forceLogin = true)
    {
        return $this->buildUrl(
            [
                self::URL_API,
                self::PATH_OAUTH_AUTHENTICATE,
                '?oauth_token=' . $oauthToken . '&force_login=' . ($forceLogin ? 1 : 0),
            ]
        );
    }

    /**
     * @param string $oauthToken
     * @param string $oauthVerifier
     *
     * @return OauthAccessTokenVo
     * @throws TwitterException
     */
    public function requestOauthAccessToken($oauthToken, $oauthVerifier)
    {
        $baseUrl = $this->buildUrl(
            [
                self::URL_API,
                self::PATH_OAUTH_ACCESS_TOKEN,
            ]
        );

        // create signature
        $signature = $this
            ->getTwitterSignature()
            ->renderForOauthAccessToken($baseUrl, $oauthToken);

        // send request to twitter
        $response = $this->formatResponse(
            $this->sendPostRequest($baseUrl, $signature, ['oauth_verifier' => $oauthVerifier])
        );

        return (new OauthAccessTokenVo())->fromArray($response);
    }

    /**
     * @param string $path
     * @param array $params
     *
     * @return array
     * @throws TwitterException
     */
    public function get($path, array $params = [])
    {
        $baseUrl = $this->buildJsonExtensionUrl(
            [
                self::URL_API,
                self::API_VERSION,
                $path,
            ]
        );

        // create signature
        $signature = $this->getTwitterSignature()->renderForApiGet($baseUrl, $params);

        // send request to twitter
        $response = $this->sendGetRequest($baseUrl, $signature, $params);

        // verify response

        return $this->formatResponse($response);
    }

    /**
     * @param string $path
     * @param array $params
     *
     * @return array
     * @throws TwitterException
     */
    public function post($path, array $params = [])
    {
        $baseUrl = $this->buildJsonExtensionUrl(
            [
                self::URL_API,
                self::API_VERSION,
                $path,
            ]
        );

        // create signature
        $signature = $this->getTwitterSignature()->renderForApiPost($baseUrl, $params);

        // send request to twitter
        $response = $this->sendPostRequest($baseUrl, $signature, $params);

        return $this->formatResponse($response);
    }

    /**
     * @param string $urlImage
     *
     * @return array
     * @throws TwitterException
     */
    public function upload($urlImage)
    {
        $baseUrl = $this->buildJsonExtensionUrl(
            [
                self::URL_UPLOAD,
                self::API_VERSION,
                self::PATH_MEDIA,
            ]
        );

        // fetch image contents
        $rawFile = file_get_contents($urlImage);

        // encode image to bas64
        $params = ['media_data' => base64_encode($rawFile)];

        // create signature
        $signature = $this->getTwitterSignature()->renderForApiPost($baseUrl, $params);

        // send request to twitter
        $response = $this->sendPostRequest($baseUrl, $signature, $params);

        return $this->formatResponse($response);
    }

    /**
     * @return TwitterSignature
     */
    private function getTwitterSignature()
    {
        return new TwitterSignature($this->apiKey, $this->apiSecret, $this->oauthToken, $this->oauthSecret);
    }

    /**
     * @param string $url
     * @param string $signature
     * @param array $data
     *
     * @return RequestResponse
     */
    private function sendGetRequest($url, $signature, array $data = [])
    {
        return Request::get($url, $data, $this->getCustomCurlOptions($signature));
    }

    /**
     * @param string $url
     * @param string $signature
     * @param array $data
     *
     * @return RequestResponse
     */
    private function sendPostRequest($url, $signature, array $data = [])
    {
        return Request::post($url, $data, $this->getCustomCurlOptions($signature));
    }

    /**
     * @param RequestResponse $response
     *
     * @return array
     * @throws TwitterException
     */
    private function formatResponse(RequestResponse $response)
    {
        $data = [];
        $rawContent = $response->getBody();

        if ($response->getHttpCode() !== 200)
        {
            throw new TwitterException($rawContent);
        }

        // handle json response
        if (stripos($response->getHeader()->getContentType(), 'application/json') !== false)
        {
            return json_decode($rawContent, true);
        }

        parse_str($rawContent, $data);

        return $data;
    }

    /**
     * @param array $parts
     *
     * @return string
     */
    private function buildUrl(array $parts)
    {
        $uri = [];

        foreach ($parts as $part)
        {
            $uri[] = trim($part, '/');
        }

        return join('/', $uri);
    }

    /**
     * @param array $parts
     *
     * @return string
     */
    private function buildJsonExtensionUrl(array $parts)
    {
        return trim($this->buildUrl($parts), '/') . '.json';
    }

    /**
     * @param string $signature
     *
     * @return array
     */
    private function getCustomCurlOptions($signature)
    {
        return [
            CURLOPT_HTTPHEADER => [$signature],
        ];
    }
}