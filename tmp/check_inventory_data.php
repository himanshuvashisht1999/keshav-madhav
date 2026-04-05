<?php
$inv = \App\Models\DomesticInventory::limit(5)->get();
$out = "";
foreach($inv as $i) {
    $out .= "ID: {$i->id}, Prod: {$i->product_id}, Color: {$i->color_id}, SizeSet: {$i->size_set_id}, Cart: {$i->packing_carton_id}\n";
}
file_put_contents('c:\xampp\htdocs\keshav-madhav\tmp\data_out.txt', $out);
