<?php
$c = file_get_contents('app/Http/Controllers/Admin/PackingController.php');

$c = preg_replace(
    '/(.*?\$unit_available = \$this->service->getAvailableQuantitiesAtUnit.*?;)/',
    "$1\n        \$unit_available_per_lot = \$this->service->getAvailableQuantitiesAtUnitPerLot(\$order->id, \$slip->stage_master_unit_id);",
    $c
);

$c = preg_replace(
    '/compact\((.*?)\)/',
    "compact($1, 'unit_available_per_lot')",
    $c
);

file_put_contents('app/Http/Controllers/Admin/PackingController.php', $c);
echo "Added unit_available_per_lot to controller";
