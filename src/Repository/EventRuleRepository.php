<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 29/10/2017
 * Time: 13.43
 */

namespace App\Repository;


use Doctrine\ORM\EntityRepository;

class EventRuleRepository extends EntityRepository
{
    public function findByOrderBetween($from, $until, $event)
    {

        $qb = $this->createQueryBuilder('rule');

        $qb->where('rule.event = :event AND rule.order >= :from AND rule.order <= :until')
            ->setParameter('event', $event)
            ->setParameter('from', $from)
            ->setParameter('until', $until);

        $result = $qb->getQuery()->getResult();

        return $result;
    }
}