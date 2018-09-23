<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 10/06/2018
 * Time: 16.25
 */

namespace App\Controller\Traits;

use Symfony\Component\HttpFoundation\Request;

trait TableSortingControllerTrait
{
    /**
     * Handles table sorting
     *
     * @param Request $request
     * @param array $fields
     * @param string $forTable
     * @param string $defaultKey
     * @param string $defaultOrder
     * @param array $aliases
     * @return array
     */
    public function handleSorting(Request $request, array $fields, $forTable, $defaultKey, $defaultOrder = 'ASC', $aliases = [])
    {
        // forTable format 'admin_school_unit_addresses_sort_'
        $session = $request->getSession();
        $sortKey = $request->get('sort', $session->get("{$forTable}_sort_key", $defaultKey));
        $order = $request->get('order', $session->get("{$forTable}_sort_order", $defaultOrder));

        $sort = $this->handleAliases($sortKey, $order, $aliases);
        if ($sortKey != $defaultKey && count($fields) > 1) {
            array_merge($sort, $this->handleAliases($defaultKey, $order, $aliases));
        }
        $orders = [];
        foreach($fields as $key) {
            $orders[$key] = ($sortKey == $key ? ($order == 'ASC' ? 'DESC' : 'ASC') : $order);
        }
        $session->set("{$forTable}_sort_key", $sortKey);
        $session->set("{$forTable}_sort_order", $order);

        return [$sort, $sortKey,$order, $orders];
    }

    protected function handleAliases($sortKey, $order, $aliases)
    {
        if (array_key_exists($sortKey, $aliases)) {
            $sort = [];
            foreach ($aliases[$sortKey] as $key)
                $sort[$key] = $order;
            return $sort;
        } else {
            return [$sortKey => $order];
        }
    }
}