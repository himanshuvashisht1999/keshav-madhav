<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\MasterDesignPattern;
use App\Http\DataTable\Admin\Master\MasterDesignPatternDataTable as DataTable;
use DB;
class DesignPatternService {
    public function __construct(
        DataTable $datatable,
        MasterDesignPattern $pattern
    ) {
        $this->datatable= $datatable;
        $this->pattern= $pattern;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        DB::beginTransaction();
        try {  
             
            
            $save_data = new MasterDesignPattern;
            $save_data->name = $request->name;
            $save_data->sku = $request->sku;
            $save_data->status = 1;
            // upload pattern image
            if($request->file('pattern_img')){
                $imagePattern = $request->file('pattern_img');
                $extImage = $imagePattern->getClientOriginalExtension();
                $pattern_img = strtolower($request->sku."-".time().".".$extImage);
                $destinationPath = public_path().'/assets/pattern-img';
                $imagePattern->move($destinationPath, $pattern_img);
                $save_data->pattern_img = $pattern_img;
            }
 
            $save_data->save();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(Request $request){
        $data = MasterDesignPattern::where('id',$request->id)->first();
        return $data;
    }
   
    public function update(Request $request){
        DB::beginTransaction();
        try {  

            /// update existing data 
            $pattern = MasterDesignPattern::find($request->id);   // id comes from route

            // Base data (always updated)
            $pattern->name   = $request->name;
            $pattern->sku    = $request->sku;
            $pattern->status = 1;
        
            // upload pattern image
            if($request->file('pattern_img')){
                $imagePattern = $request->file('pattern_img');
                $extImage = $imagePattern->getClientOriginalExtension();
                $pattern_img = strtolower($request->sku."-".time().".".$extImage);
                $destinationPath = public_path().'/assets/pattern-img';
                $imagePattern->move($destinationPath, $pattern_img);
                $pattern->pattern_img = $pattern_img;
            }
            $pattern->save();
           
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(Request $request){
        $data = MasterDesignPattern::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

}