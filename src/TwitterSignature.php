<?php

namespace Simplon\Twitter;

/**
 * Class TwitterSignature
 * @package Simplon\Twitter
 */
class TwitterSignature
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiSecret;

    /**
     * @var string
     */
    protected $oauthCredentials = [];
    /**
     * @var null|string
     */
    private $oauthToken;
    /**
     * @var null|string
     */
    private $oauthSecret;

    /**
     * @param string $apiKey
     * @param string $apiSecret
     * @param null $oauthToken
     * @param null $oauthSecret
     */
    public function __construct($apiKey, $apiSecret, $oauthToken = null, $oauthSecret = null)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->oauthToken = $oauthToken;
        $this->oauthSecret = $oauthSecret;

        $this->oauthCredentials = [
            'oauth_nonce'            => md5(microtime() . mt_rand()),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_version'          => Twitter::OAUTH_VERSION,
        ];
    }

    /**
     * @param $baseUrl
     * @param $callbackUrl
     *
     * @return string
     */
    public function renderForOauthRequestToken($baseUrl, $callbackUrl)
    {
        $this
            ->addOauthConsumerKey($this->apiKey)
            ->addOauthCallback($callbackUrl);

        $baseString = $this->buildBaseString('POST', $baseUrl, $this->oauthCredentials);
        $compositeKey = $this->getCompositeKey($this->apiSecret);

        $this->addOauthSignature(
            $this->buildOauthSignature($baseString, $compositeKey)
        );

        return $this->buildAuthorizationHeader($this->oauthCredentials);
    }

    /**
     * @param $baseUrl
     * @param $oauthToken
     *
     * @return string
     */
    public function renderForOauthAccessToken($baseUrl, $oauthToken)
    {
        $this
            ->addOauthConsumerKey($this->apiKey)
            ->addOauthToken($oauthToken);

        $baseString = $this->buildBaseString('POST', $baseUrl, $this->oauthCredentials);
        $compositeKey = $this->getCompositeKey($this->apiSecret);

        $this->addOauthSignature(
            $this->buildOauthSignature($baseString, $compositeKey)
        );

        return $this->buildAuthorizationHeader($this->oauthCredentials);
    }

    /**
     * @param $baseUrl
     * @param array $params
     *
     * @return string
     */
    public function renderForApiGet($baseUrl, array $params = [])
    {
        return $this->renderForApi('GET', $baseUrl, $params);
    }

    /**
     * @param $baseUrl
     * @param array $params
     *
     * @return string
     */
    public function renderForApiPost($baseUrl, array $params = [])
    {
        return $this->renderForApi('POST', $baseUrl, $params);
    }

    /**
     * @param $requestMethod
     * @param $baseUrl
     * @param array $params
     *
     * @return string
     */
    private function renderForApi($requestMethod, $baseUrl, array $params = [])
    {
        $this
            ->addOauthConsumerKey($this->apiKey)
            ->addOauthToken($this->oauthToken);

        $baseString = $this->buildBaseString($requestMethod, $baseUrl, array_merge($this->oauthCredentials, $params));
        $compositeKey = $this->getCompositeKey($this->apiSecret, $this->oauthSecret);

        $this->addOauthSignature(
            $this->buildOauthSignature($baseString, $compositeKey)
        );

        return $this->buildAuthorizationHeader($this->oauthCredentials);
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    private function addOauthConsumerKey($key)
    {
        $this->oauthCredentials['oauth_consumer_key'] = $key;

        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    private function addOauthCallback($url)
    {
        $this->oauthCredentials['oauth_callback'] = $url;

        return $this;
    }

    /**
     * @param string $sig
     *
     * @return $this
     */
    private function addOauthSignature($sig)
    {
        $this->oauthCredentials['oauth_signature'] = $sig;

        return $this;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    private function addOauthToken($token)
    {
        $this->oauthCredentials['oauth_token'] = $token;

        return $this;
    }

    /**
     * @param string $requestType
     * @param string $uri
     * @param array $params
     *
     * @return string
     */
    private function buildBaseString($requestType, $uri, array $params)
    {
        $r = [];
        ksort($params);

        foreach ($params as $key => $value)
        {
            $r[] = $key . "=" . rawurlencode($value);
        }

        return strtoupper($requestType) . '&' . rawurlencode($uri) . '&' . rawurlencode(join('&', $r));
    }

    /**
     * @param string $apiSecret
     * @param string|null $secretAccessToken
     *
     * @return string
     */
    private function getCompositeKey($apiSecret, $secretAccessToken = null)
    {
        return rawurlencode($apiSecret) . '&' . rawurlencode($secretAccessToken);
    }

    /**
     * @param string $baseString
     * @param string $compositeKey
     *
     * @return string
     */
    private function buildOauthSignature($baseString, $compositeKey)
    {
        return base64_encode(hash_hmac('sha1', $baseString, $compositeKey, true));
    }

    /**
     * @param array $oauthCredentials
     *
     * @return string
     */
    private function buildAuthorizationHeader(array $oauthCredentials)
    {
        $values = [];
        ksort($oauthCredentials);
        $r = 'Authorization: OAuth ';

        foreach ($oauthCredentials as $key => $value)
        {
            $values[] = $key . "=\"" . rawurlencode($value) . "\"";
        }

        $r .= join(', ', $values);

        return $r;
    }
}