<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\LoginService as Service;
use App\Requests\Admin\LoginRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\StageMasterUnit;
use App\Models\FabricRollAssigning;
use App\Models\ProductionSlipDigitization;
use Illuminate\Support\Facades\File;

class LoginController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    //// Admin login page
    public function login(){
        if(Auth::guard('admin')->user()){
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }
    /// admin check login details
    public function postLogin(LoginRequest $request){

        $data = $this->service->postLogin($request); 
        
        if($data == 'success'){
            // return redirect()->route('admin.dashboard')->withSuccess('You have successfully logged in.');
            return redirect()->route('admin.product_order.indexOrder')->withSuccess('You have successfully logged in.');
        }else{
            return redirect()->back()->withError('Invalid email or password. Please try again.');
        }
    }
    public function logout(){
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login')->withSuccess('You have successfully logged out.');
    }

    public function uploadProductionSlip($encryptedId){
        $stageMasterUnitId = Crypt::decryptString($encryptedId);
        $response['data'] = StageMasterUnit::findOrFail($stageMasterUnitId);
        $response['stage_master_unit_id'] = $encryptedId;
        return view('admin.upload_production_slip',$response);
    }
    public function submitProductionSlip(Request $request)
    {
        // 1️⃣ Decrypt stage_master_unit_id
        $stageMasterUnitId = Crypt::decryptString($request->stage_master_unit_id);

        $data = StageMasterUnit::findOrFail($stageMasterUnitId);

        // 2️⃣ Validate image
        $request->validate([
            'photo_data' => 'required'
        ]);

        // 3️⃣ Store image (BASE64 → FILE)
        $slip_file = null;

        if ($request->photo_data) {

            // Remove base64 header
            $image = $request->photo_data;
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $image);
            $image = str_replace(' ', '+', $image);

            $imageData = base64_decode($image);

            // Generate file name
            $slip_file = 'production-slip-' . rand(1000,9999) . '_' . time() . '.jpg';

            // Destination path (same style as your example)
            $destinationPath = public_path('assets/production_slips');

            // Create folder if not exists
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true);
            }

            // Save image
            file_put_contents($destinationPath . '/' . $slip_file, $imageData);
        }

        if($request->type == 1){

            $save_data = new FabricRollAssigning;
            $save_data->stage_master_unit_id = $data->id;
            $save_data->slip_file = $slip_file;
            $save_data->status = 0;
            $save_data->save();

        }else{
            $save_data = new ProductionSlipDigitization;
            $save_data->from_stage_id = $data->master_stage_id;
            $save_data->stage_master_unit_id = $data->id;
            $save_data->slip_file = $slip_file;
            $save_data->status = 0;
            $save_data->save();
        }
        

        return redirect()->back()->withSuccess('Production slip uploaded successfully.');
    }

}