<?php

namespace App\Traits;

trait DatatablesSortable
{

    public function sortByColumn($order, $columns, $query)
    {
        // order is an array like [{"column":"11","dir":"asc"}]
        if ($order) {
            $order = $order[0];
            $column = $columns[$order['column']]['data'];
            $direction = $order['dir'];

            $query->orderBy($column, $direction);
        }

        return $query;
    }
}
