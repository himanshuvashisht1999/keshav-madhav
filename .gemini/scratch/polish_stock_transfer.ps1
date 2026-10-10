$path = "c:\xampp\htdocs\keshav-madhav\resources\views\admin\inventory\stock_transfer\index.blade.php"
$content = [System.IO.File]::ReadAllText($path)

# Replace card-header patterns
$content = $content.Replace('class="card-header bg-white border-0 py-3"', 'class="p-2 border-bottom bg-light"')
$content = $content.Replace('class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center"', 'class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center"')

# Replace text-primary in card titles with inline #05421c
$content = $content.Replace('class="mb-0 font-weight-bold text-primary"', 'class="mb-0 font-weight-bold" style="color: #05421c;"')

# Replace large padded card bodies
$content = $content.Replace('class="card-body bg-light rounded-bottom p-4"', 'class="p-3"')
$content = $content.Replace('class="card-body bg-light rounded-bottom py-3 px-4"', 'class="p-3"')
$content = $content.Replace('class="card-body py-3 px-4"', 'class="p-3"')

# Replace input-group prepend background
$content = $content.Replace('class="input-group-text bg-primary text-white border-primary"', 'class="input-group-text" style="background: #05421c; color: #fff; border-color: #05421c;"')
$content = $content.Replace('class="badge badge-primary ml-2"', 'class="badge-erp-status badge-erp-primary ml-2"')
$content = $content.Replace('class="btn btn-outline-secondary btn-block"', 'class="btn-erp btn-erp-secondary btn-block"')

[System.IO.File]::WriteAllText($path, $content)
Write-Output "Successfully updated stock_transfer/index.blade.php"
