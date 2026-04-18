<?php
$file = 'resources/views/admin/agent_orders/create.blade.php';
$content = file_get_contents($file);
$search = 'order_type: "{{ request(\'order_type\', \'normal\') }}",';
$replace = 'order_type: "{{ request(\'order_type\', \'normal\') }}",' . "\n" . '                                 sale_type: "{{ request(\'sale_type\', \'item\') }}",';
$newContent = str_replace($search, $replace, $content);
file_put_contents($file, $newContent);
echo "Successfully updated " . $file;
