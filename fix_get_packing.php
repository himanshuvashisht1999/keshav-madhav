<?php
$c = file_get_contents('app/Services/Admin/PackingService.php');

$new = <<<'EOD'
    public function getPackingMainWithStructure($slip_id)
    {
        return PackingMain::where('slip_id', $slip_id)
            ->with([
                'cartons.items.detail.orderProductSet.product',
                'cartons.items.detail.orderProductSet.colors',
                'cartons.items.detail.orderProductSet.size_measurement'
            ])
            ->first();
    }
EOD;

$c = preg_replace('/public function getPackingMainWithStructure\(.*?\).*?->first\(\);\s*\}/s', $new, $c, 1);
file_put_contents('app/Services/Admin/PackingService.php', $c);
echo "Fixed getPackingMainWithStructure";
