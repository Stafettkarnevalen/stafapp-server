<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 29/10/2017
 * Time: 13.43
 */

namespace App\Repository;


use Doctrine\ORM\EntityRepository;

class DocumentationRepository extends EntityRepository
{
    public function findByOrderBetween($from, $until, $parent = null)
    {

        $qb = $this->createQueryBuilder('doc');

        if ($parent) {
            $qb->where('doc.parent = :parent AND doc.order >= :from AND doc.order <= :until')
                ->setParameter('parent', $parent);
        } else {
            $qb->where('doc.parent IS NULL AND doc.order >= :from AND doc.order <= :until');
        }

        $qb
            ->setParameter('from', $from)
            ->setParameter('until', $until);

        $result = $qb->getQuery()->getResult();

        return $result;
    }
}