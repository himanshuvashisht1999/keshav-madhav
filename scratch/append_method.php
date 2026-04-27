<?php
$file = 'app/Http/Controllers/Admin/OrderDigitalizationController.php';
$content = file_get_contents($file);

// Check if method already exists (to avoid duplicates)
if (strpos($content, 'getAssignmentDetails') === false) {
    // Replace the last closing brace
    $newMethod = "    public function getAssignmentDetails(Request \$request)
    {
        \$response = \$this->service->getAssignmentDetails(\$request);
        return response()->json(\$response);
    }
}\n";
    
    // Find the last '}'
    $pos = strrpos($content, '}');
    if ($pos !== false) {
        $content = substr($content, 0, $pos) . $newMethod;
        file_put_contents($file, $content);
        echo "Successfully added getAssignmentDetails to $file\n";
    } else {
        echo "Error: Could not find closing brace\n";
    }
} else {
    echo "Method already exists\n";
}
