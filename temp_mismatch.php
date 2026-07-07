<?php
$mismatches = DB::select('
    SELECT d.id, d.product_id, d.size_set_id, d.quantity, s.name, s.no_of_pcs
    FROM domestic_inventories d
    JOIN master_size_measurements s ON d.size_set_id = s.id
    WHERE d.quantity != s.no_of_pcs
');
echo json_encode($mismatches);
