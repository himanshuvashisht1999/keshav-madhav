<?php
$file = 'resources/views/admin/packing/process.blade.php';
$content = file_get_contents($file);

// 1. Remove duplicate dropdowns
$prefixes = ['dead', 'sampling', 'debit', 'rework'];
foreach ($prefixes as $prefix) {
    $duplicate_html = <<<HTML
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small">Select Lot Number <span class="text-danger">*</span></label>
                                <select id="{$prefix}_lot_no" class="form-control custom-select" onchange="render{$prefix}Grid()">
                                    <option value="">-- Select Lot --</option>
                                </select>
                            </div>
HTML;
    
    // Replace all occurrences with empty string, then add it back once
    $regex = '/<div class="form-group mb-3">\s*<label class="font-weight-bold text-muted small">Select Lot Number <span class="text-danger">\*<\/span><\/label>\s*<select id="' . $prefix . '_lot_no" class="form-control custom-select">\s*<option value="">-- Select Lot --<\/option>\s*<\/select>\s*<\/div>/s';
    $content = preg_replace($regex, '', $content);
    
    // Also remove any previously injected ones with onchange if I ran this before
    $regex_onchange = '/<div class="form-group mb-3">\s*<label class="font-weight-bold text-muted small">Select Lot Number <span class="text-danger">\*<\/span><\/label>\s*<select id="' . $prefix . '_lot_no" class="form-control custom-select" onchange="render' . $prefix . 'Grid\(\)">\s*<option value="">-- Select Lot --<\/option>\s*<\/select>\s*<\/div>/s';
    $content = preg_replace($regex_onchange, '', $content);
    
    if ($prefix === 'dead') {
        $content = str_replace('<h6>Select Pieces to Mark as Dead Stock</h6>', $duplicate_html . "\n                            <h6>Select Pieces to Mark as Dead Stock</h6>", $content);
    } else if ($prefix === 'sampling') {
        $content = str_replace('<h6>Select Pieces for Sampling</h6>', $duplicate_html . "\n                            <h6>Select Pieces for Sampling</h6>", $content);
    } else if ($prefix === 'debit') {
        $content = str_replace('<h6>2. Select Damaged Pieces</h6>', "<h6>2. Select Damaged Pieces</h6>\n" . $duplicate_html, $content);
    } else if ($prefix === 'rework') {
        $content = str_replace('<h6>Select Pieces for Rework</h6>', $duplicate_html . "\n                            <h6>Select Pieces for Rework</h6>", $content);
    }
}

file_put_contents($file, $content);
echo "Fixed duplicates\n";
?>
