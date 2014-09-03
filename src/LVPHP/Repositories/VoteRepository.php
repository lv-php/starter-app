<?php

namespace LVPHP\Repositories;

use Doctrine\ORM\EntityRepository;
use LVPHP\Models\Topic;

class VoteRepository extends EntityRepository
{
    public function getTotalVotesForTopic(Topic $topic)
    {
        $votes = $this->findBy(array('topic' => $topic));
        return count($votes);
    }

    public function userHasVoted(Topic $topic, $ip)
    {
        $vote = $this->findBy(array('topic' => $topic, 'ip' => $ip));
        return !empty($vote);
    }
}