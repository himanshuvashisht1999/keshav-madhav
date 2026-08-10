<?php
$content = file_get_contents('resources/views/admin/packing/process.blade.php');
$content = preg_replace('/\/\/ Boxes \(Unpacked\) - Removed since boxes are deprecated.*?\}\s*if \(html === \'\'\)/s', "// Boxes deprecated\n                if (html === '')", $content, 1);
file_put_contents('resources/views/admin/packing/process.blade.php', $content);
echo "Fixed JS";
