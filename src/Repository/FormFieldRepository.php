<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 14/12/2016
 * Time: 21.34
 */

namespace App\Repository;


use Doctrine\ORM\EntityRepository;



class FormFieldRepository extends EntityRepository
{

    public function findFieldsAfter($form, $order)
    {
        $qb = $this->createQueryBuilder('ff')
            ->where('ff.form = :form AND ff.order > :order')
            ->setParameter('form', $form)
            ->setParameter('order', $order);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function findByOrderBetween($from, $until, $form)
    {

        $qb = $this->createQueryBuilder('ff');

        $where = 'ff.form = :form AND ff.order >= :from AND ff.order <= :until';

        $qb->where($where)
            ->setParameter('from', $from)
            ->setParameter('until', $until)
            ->setParameter('form', $form);

        $result = $qb->getQuery()->getResult();

        return $result;
    }
}