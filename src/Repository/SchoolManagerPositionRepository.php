<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 14/12/2016
 * Time: 21.34
 */

namespace App\Repository;

use App\Entity\Schools\SchoolUnit;
use Doctrine\ORM\EntityRepository;

class SchoolManagerPositionRepository extends EntityRepository
{
    /**
     * @param SchoolUnit $schoolUnit
     * @param array $orderBy
     * @return array
     */
    public function findBySchoolUnit(SchoolUnit $schoolUnit, array $orderBy)
    {
        $q = $this->createQueryBuilder('smp')
            ->leftJoin('smp.schoolUnit', 'su')
            ->leftJoin('smp.manager', 'sm')
            ->addSelect('su')
            ->where('su.id = :unit')
            ->setParameter('unit', $schoolUnit->getId())
        ;

        foreach ($orderBy as $key => $order) {
            if ($key == 'firstname' || $key == 'lastname')
                $q->addOrderBy('sm.' . $key, $order);
            else
                $q->addOrderBy('smp.' . $key, $order);
        }

        return $q->getQuery()->getResult();
    }
}