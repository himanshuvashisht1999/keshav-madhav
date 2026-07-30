<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

$request = Request::create('/agent/orders/340/edit', 'GET');
app()->instance('request', $request);

$id = 340;
$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";

$html = $response->getContent();
$count = substr_count($html, 'variation-card');
echo "Variation cards in HTML: " . $count . "\n";

$boxes = [];
preg_match_all('/data-key="([^"]+)"/', $html, $matches);
print_r(array_unique($matches[1]));
