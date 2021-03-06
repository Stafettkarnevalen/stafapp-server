<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 31/12/2017
 * Time: 22.01
 */

namespace App\Repository;

use App\Entity\Services\ServiceCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class ServiceCategoryRepository extends EntityRepository
{
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $result = parent::findBy($criteria, [], $limit, $offset);
        if ($orderBy && array_key_exists('children', $orderBy)) {
            $inv = (strtoupper($orderBy['children']) !== 'ASC');
            usort($result,
                function(ServiceCategory $a, ServiceCategory $b) use($inv) {
                    return ($inv ? -1 : 1) * ($a->getChildren()->count() - $b->getChildren()->count());
            });
        } else if ($orderBy) {
            $criteria = Criteria::create()->orderBy($orderBy);
            $result =  (new ArrayCollection($result))->matching($criteria)->toArray();
        }
        return $result;
    }
}