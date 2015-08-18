<?php

namespace Simplon\Twitter;

use Simplon\Helper\CastAway;
use Simplon\Helper\DataIoVoTrait;

/**
 * Class OauthRequestTokenVo
 * @package Simplon\Twitter
 */
class OauthRequestTokenVo
{
    use DataIoVoTrait;

    /**
     * @var string
     */
    protected $oauthToken;

    /**
     * @var string
     */
    protected $oauthTokenSecret;

    /**
     * @var bool
     */
    protected $oauthCallbackConfirmed;

    /**
     * @return string
     */
    public function getOauthToken()
    {
        return $this->oauthToken;
    }

    /**
     * @return string
     */
    public function getOauthTokenSecret()
    {
        return $this->oauthTokenSecret;
    }

    /**
     * @return boolean
     */
    public function getOauthCallbackConfirmed()
    {
        return CastAway::toBool($this->oauthCallbackConfirmed);
    }
}