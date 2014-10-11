<?php

namespace LVPHP\Models;

Use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="topics")
 **/
class Topic
{
    const DELETED = 0; // Cause some topic ideas might be irrelevant
    const ACTIVE = 1;
    const CLOSED = 2; // If we talked about it

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     **/
    protected $id;

    /** @ORM\Column(type="string", unique=true, nullable=false) **/
    protected $header;

    /** @ORM\Column(type="string", unique=true, nullable=false) **/
    protected $body;

    /** @ORM\Column(type="datetime") **/
    protected $createdTimestamp;

    /** @ORM\Column(type="integer") **/
    protected $ip;

    /** @ORM\Column(type="integer") **/
    protected $status;

    /**
     * @param $header
     * @param $body
     * @param $ip
     * @param int $status
     */
    public function __construct($header, $body, $ip, $status = self::ACTIVE)
    {
        $this->header = $header;
        $this->body = $body;
        $this->ip = ip2long($ip);
        $this->status = $status;
        $this->createdTimestamp = new DateTime();
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getCreatedTimestamp()
    {
        return $this->createdTimestamp;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function setStatusToDeleted()
    {
        $this->status = self::DELETED;
    }

    public function setStatusToActive()
    {
        $this->status = self::ACTIVE;
    }

    public function setStatusToClosed()
    {
        $this->status = self::CLOSED;
    }
}