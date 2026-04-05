<?php
$sizes = \App\Models\MasterSizeMeasurement::limit(5)->get();
foreach($sizes as $s) {
    echo "ID: {$s->id}, Name: {$s->name}\n";
}
