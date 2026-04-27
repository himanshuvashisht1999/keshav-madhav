<?php
$file = 'app/Http/Controllers/Unit/UnitAuthController.php';
$content = file_get_contents($file);

// Fix remaining variable name inconsistencies
$content = str_replace("'% . \$orderNo . %'", "'% . \$customerSearch . %'", $content);

file_put_contents($file, $content);
echo "Cleaned up variable names in $file\n";
