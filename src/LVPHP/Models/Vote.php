<?php

namespace LVPHP\Models;

Use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="LVPHP\Repositories\VoteRepository")
 * @ORM\Table(name="votes")
 **/
class Vote
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     **/
    protected $id;

    /** @ORM\Column(type="datetime") **/
    protected $createdTimestamp;

    /** @ORM\Column(type="integer") **/
    protected $ip;

    /** @ORM\ManyToOne(targetEntity="Topic", cascade={"all"}, fetch="EAGER") */
    protected $topic;

    /**
     * @param Topic $topic
     * @param $ip
     */
    public function __construct(Topic $topic, $ip)
    {
        $this->topic = $topic;
        $this->ip = ip2long($ip);
        $this->createdTimestamp = new DateTime();
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
    public function getTopic()
    {
        return $this->topic;
    }


}