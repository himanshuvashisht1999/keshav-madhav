<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService as Service;
use App\Models\User;

class DashboardController extends Controller { 
    protected $service; 
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function dashboard(Request $request)
    {

        $year = $request->input('year') ?? date('Y');

        $total_users = User::whereYear('created_at', $year)->count();


        // Grouped data for chart
        $chartData = [
            'users' => User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                            ->whereYear('created_at', $year)
                            ->groupBy('month')->pluck('count', 'month')->toArray(),
        ];

        return view('admin.dashboard', compact(
            'total_users','year', 'chartData'
        ));
    }

    public function dashboard1(Request $request)
    {

        $accessToken = 'EAFdjyjFcJZAUBP9NIvna2pGX0Q7DH2huFpl9aLkeRyePWXfqvrfFWoU0McdKi3iojSYZArs40ZCvBShJsgyUn7SyZBZAZC0MLqCbJger1qWHbqoJ0XF5ymlKYDYdVsCHVMBs7OaLKGZAtcQhAiZCbgzmnUT3PESKZAMUXLrm3ZBQXI0t2QcGAR9rFNC095a3ZCf0ZCxQggZDZD'; // Replace with your access token
        $phoneNumberId = '661493770388470';
        $apiVersion = 'v22.0';
        $userPhone = '918839146038'; // e.g. 919876543210

        $url = "https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages";

        $data = [
            "messaging_product" => "whatsapp",
            "to" => $userPhone,
            "type" => "template",
            "template" => [
                "name" => "hello_world",
                "language" => [
                    "code" => "en_US"
                ]
            ]
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $accessToken",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        dd($response);
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        } else {
            echo "HTTP Status Code: " . $httpcode . "\n";
            echo "Response: " . $response . "\n";
        }

        curl_close($ch);
        dd($response);

        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        } else {
            echo 'Response: ' . $response;
        }

        curl_close($response);

    }

    public function getDashboardData(Request $request)
    {
        $fabricStock = $this->service->fabricStock($request)->original;
        $itemStock   = $this->service->itemStock($request)->original;

        return response()->json([
            'itemStock' => [
                'labels' => $itemStock['labels'],   // Original SKUs for tooltip
                'data'        => $itemStock['data']
            ],
            'fabricStock' => [
                'labels' => $fabricStock['labels'], // Original SKUs for tooltip
                'data'        => $fabricStock['data']
            ]
        ]);
    }

    
}