<?php
$missing = DB::select('
    SELECT DISTINCT d.product_id, d.size_set_id 
    FROM domestic_inventories d 
    LEFT JOIN production_goods_variants v 
    ON d.product_id = v.production_goods_id AND d.size_set_id = v.master_size_measurement_id 
    WHERE v.id IS NULL
');
echo json_encode($missing);
