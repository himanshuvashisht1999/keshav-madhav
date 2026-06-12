<?php
$targetFile = 'c:\\xampp\\htdocs\\keshav-madhav\\resources\\views\\admin\\inventory\\fair_product\\create.blade.php';
$content = file_get_contents($targetFile);
$content = str_replace("\r\n", "\n", $content);

$edits = json_decode(file_get_contents('all_edits.json'), true);

$appliedCount = 0;
foreach ($edits as $edit) {
    $target = str_replace("\r\n", "\n", $edit['target']);
    $replacement = str_replace("\r\n", "\n", $edit['replacement']);
    
    // Strip leading and trailing quotes if present
    if (str_starts_with($target, '"') && str_ends_with($target, '"')) {
        $target = substr($target, 1, -1);
    }
    if (str_starts_with($replacement, '"') && str_ends_with($replacement, '"')) {
        $replacement = substr($replacement, 1, -1);
    }
    
    // Also strip single quotes if the LLM used them
    if (str_starts_with($target, "'") && str_ends_with($target, "'")) {
        $target = substr($target, 1, -1);
    }
    if (str_starts_with($replacement, "'") && str_ends_with($replacement, "'")) {
        $replacement = substr($replacement, 1, -1);
    }
    
    // Unescape anything like \" inside the string
    $target = stripslashes($target);
    $replacement = stripslashes($replacement);
    
    // Check for broken replace logic (undo broken replace)
    if (strpos($replacement, "toastr.error(\"{{ session('error') }}\");") !== false && strpos($replacement, "toastr.success(\"{{ session('success') }}\");") !== false) {
        echo "Skipping broken edit\n";
        continue;
    }
    
    if (strpos($content, $target) !== false) {
        $content = str_replace($target, $replacement, $content);
        $appliedCount++;
    } else {
        echo "Failed to apply edit length " . strlen($target) . "\n";
    }
}

file_put_contents($targetFile, $content);
echo "Applied $appliedCount edits. New length: " . strlen($content) . "\n";
