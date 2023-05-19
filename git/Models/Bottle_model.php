<?php
namespace App\Models;

use CodeIgniter\Model;

final class Bottle_model extends Model
{
    public function getBottles($filtering, $sorting, $page)
    {
        $query = 'SELECT * FROM public.view_bottle';
        $total_rows = 'SELECT COUNT(*) AS total_rows FROM public.view_bottle';

        if (!empty($filtering))
        {
            $where_clause = [];

            foreach ($filtering as $column => $value)
            {
                if (in_array($column, ['id', 'purchase_order', 'palette_id', 'palette_c', 'box_id', 'box_c', 'banderol_id']))
                {
                    $value = abs(intval($value));

                    $where_clause[] = "{$column} = {$value}";
                }
                else
                {
                    $value = str_replace("'", "''", $value);

                    $where_clause[] = "{$column}::varchar LIKE '%{$value}%'";
                }
            }

            $query .= ' WHERE '. implode(' AND ', $where_clause);
            $total_rows .= ' WHERE '. implode(' AND ', $where_clause);
        }

        $query .= " ORDER BY {$sorting['name']} {$sorting['type']}";

        $limit = 500;
        $offset = $limit * ($page - 1);

        $query .= " LIMIT $limit OFFSET $offset";

        $result = $this->db->query($query)->getResultArray();

        $bottles = [];

        foreach ($result as $row)
        {
            $bottles[] = [
                'id' => $row['id'],
                'purchase_order' => $row['purchase_order'] ?: '',
                'palette_id' => $row['palette_id'] ?: '',
                'palette_c' => $row['palette_c'] ?: '',
                'palette' => $row['palette'] ?: '',
                'box_id' => $row['box_id'] ?: '',
                'box_c' => $row['box_c'] ?: '',
                'box' => $row['box'] ?: '',
                'bottle_type' => $row['bottle_type'] ?: '',
                'banderol_id' => $row['banderol_id'] ?: '',
                'banderol' => $row['banderol'] ?: '',
                'created' => $row['created'] ? implode(PHP_EOL, explode(' ', $row['created'])) : ''
            ];
        }

        return [
            'data' => $bottles,
            'rows' => $this->db->query($total_rows)->getRowArray()['total_rows']
        ];
    }

    public function getPurchaseOrders()
    {
        $purchase_orders = [];

        $result = $this->db->query("SELECT * FROM public.purchase_order ORDER BY date_created DESC LIMIT 20")->getResultArray();

        foreach ($result as $row)
        {
            $date_object = date_create_from_format('Y-m-d H:i:s', $row['date_created']);
            $date_created = date_format($date_object, 'd.m.Y H:i');

            $order = "Поръчка &numero; {$row['purchase_order_id']} създадена на {$date_created}";

            if ($row['date_finalized'])
            {
                $date_object = date_create_from_format('Y-m-d H:i:s', $row['date_finalized']);
                $date_finalized = date_format($date_object, 'd.m.Y H:i');

                $order .= " приключена на {$date_finalized}";
            }

            $purchase_orders[$row['purchase_order_id']] = $order;
        }

        return $purchase_orders;
    }
}