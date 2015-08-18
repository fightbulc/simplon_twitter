<?php

namespace Simplon\Twitter;

use Simplon\Helper\CastAway;
use Simplon\Helper\DataIoVoTrait;

/**
 * Class OauthAccessTokenVo
 * @package Simplon\Twitter
 */
class OauthAccessTokenVo
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
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $screenName;

    /**
     * @var int
     */
    protected $xAuthExpires;

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
     * @return int
     */
    public function getUserId()
    {
        return CastAway::toInt($this->userId);
    }

    /**
     * @return string
     */
    public function getScreenName()
    {
        return $this->screenName;
    }

    /**
     * @return int
     */
    public function getXAuthExpires()
    {
        return CastAway::toInt($this->xAuthExpires);
    }
}