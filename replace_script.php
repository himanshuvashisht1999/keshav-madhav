<?php
$dirs = [
    'product-nature' => ['master.colors', 'master/colors', 'Master Color', 'Color Name', 'color name', 'Color', 'color', 'Product Nature'],
    'fabric-type' => ['master.colors', 'master/colors', 'Master Color', 'Color Name', 'color name', 'Color', 'color', 'Fabric Type']
];

foreach ($dirs as $dir => $replacements) {
    $files = glob("resources/views/admin/master/$dir/*.php");
    foreach ($files as $f) {
        $c = file_get_contents($f);
        $c = str_replace(
            ['master.colors', 'master/colors', 'Master Color', 'Color Name', 'color name', 'Color', 'color'], 
            ["master.$dir", "master/$dir", $replacements[7], $replacements[7] . ' Name', strtolower($replacements[7]) . ' name', $replacements[7], strtolower($replacements[7])], 
            $c
        );
        file_put_contents($f, $c);
    }
}
echo "Replaced strings successfully.\n";
