<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

// Auth as agent 1
$agent = \App\Models\SalesAgent::find(1);
Auth::guard('sales_agent')->login($agent);

$request = Request::create('/agent/orders/340/edit', 'GET');
app()->instance('request', $request);

try {
    $response = app()->handle($request);
    $html = $response->getContent();
    echo "Status: " . $response->getStatusCode() . "\n";
    if (strpos($html, 'variation-container') !== false) {
        $matches = [];
        preg_match_all('/data-key="([^"]+)"/', $html, $matches);
        echo "Keys in HTML: " . implode(', ', array_unique($matches[1])) . "\n";
    } else {
        echo "No variation-container found.\n";
        echo substr($html, 0, 500);
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
