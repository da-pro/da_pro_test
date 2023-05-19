<?php
require_once(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR .'app/Libraries/PHPExcel.php');

$filter = [];

if (!empty($_SERVER['QUERY_STRING']))
{
    parse_str($_SERVER['QUERY_STRING'], $filter);
}

$connection = @pg_connect("host=wine.mumbg.com port=44446 user=wineuser password='w1n3us3r' dbname=winedb connect_timeout='10' sslmode='prefer'");

if ($connection)
{
    $query = 'SELECT purchase_order, banderol, box, palette FROM public.view_bottle';

    $order_by = PHP_EOL ."ORDER BY {$filter['column']} {$filter['order']}";

    unset($filter['column'], $filter['order']);

    $where_clause = [];

    foreach ($filter as $column => $value)
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

    if (!empty($where_clause))
    {
        $query .= PHP_EOL .'WHERE '. implode(' AND ', $where_clause);
    }

    $query .= $order_by;

    $result = pg_query($query);

    $request = [];

    while ($row = pg_fetch_assoc($result))
    {
        $request[] = [
            'purchase_order' => $row['purchase_order'] ?: '',
            'box' => $row['box'] ?: '',
            'palette' => $row['palette'] ?: '',
            'banderol' => $row['banderol'] ?: ''
        ];
    }

    if (!empty($request))
    {
        $object = new PHPExcel();

        $object->setActiveSheetIndex(0);

        $object->getDefaultStyle()->getFont()->setName('Arial');

        $object->getDefaultStyle()->getFont()->setSize(10);

        $object->getDefaultStyle()->getFont()->setSize(11);
        $object->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
        $object->getActiveSheet()->getStyle('A1:D1')->getFont()->setSize(12);

        $alignment['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
        $alignment['alignment']['vertical'] = PHPExcel_Style_Alignment::VERTICAL_CENTER;

        $object->getActiveSheet()->getStyle('A1:D1')->applyFromArray($alignment);
        $object->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setWrapText(true);

        $object->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $object->getActiveSheet()->getColumnDimension('B')->setWidth(120);
        $object->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $object->getActiveSheet()->getColumnDimension('D')->setWidth(40);

        $table_columns = ['PO_number', 'PDF_417', 'Box_number', 'Pallet_Number'];

        $column = 0;

        foreach($table_columns as $field)
        {
            $object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
            $column++;
        }

        $excel_row = 1;

        foreach($request as $row)
        {
            ++$excel_row;

            $object->getActiveSheet()->setCellValueByColumnAndRow(0, $excel_row, $row['purchase_order']);
            $object->getActiveSheet()->setCellValueByColumnAndRow(1, $excel_row, $row['banderol']);
            $object->getActiveSheet()->getCellByColumnAndRow(2, $excel_row)->setValueExplicit($row['box'], PHPExcel_Cell_DataType::TYPE_STRING);
            $object->getActiveSheet()->getCellByColumnAndRow(3, $excel_row)->setValueExplicit($row['palette'], PHPExcel_Cell_DataType::TYPE_STRING);
        }

        $file_name = 'Excel Export '. date('Y-m-d H:i:s') .'.xls';
        $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $file_name . '"');
        $object_writer->save('php://output');
    }
    else
    {
        exit('<h1>няма данни</h1>');
    }
}
else
{
    exit('<h1>няма връзка към базата</h1>');
}

pg_close($connection);