<?php
$content = file_get_contents('resources/views/admin/packing/process.blade.php');
preg_match_all('/<script>(.*?)<\/script>/s', $content, $matches);
file_put_contents('scripts.js', implode("\n", $matches[1]));
