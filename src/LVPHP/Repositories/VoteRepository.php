<?php

namespace LVPHP\Repositories;

use Doctrine\ORM\EntityRepository;
use LVPHP\Models\Topic;

class VoteRepository extends EntityRepository
{
    public function findAllVotesForTopic(Topic $topic)
    {
        return $this->findBy(array('topic' => $topic));
    }

    public function findVoteFromTopicBasedOnIP(Topic $topic, $ip)
    {
        return $this->findOneBy(array('topic' => $topic, 'ip' => ip2long($ip)));
    }
}