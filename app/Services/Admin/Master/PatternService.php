<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\MasterPattern;
use App\Models\MasterPatternPart;
use App\Http\DataTable\Admin\Master\MasterPatternDataTable as DataTable;
use DB;
class PatternService {
    public function __construct(
        DataTable $datatable,
        MasterPattern $pattern
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
            if (
                count($request->part_no) === count($request->part_name) &&
                count($request->part_no) === count($request->is_printing) &&
                count($request->part_no) === count($request->is_embroidery) &&
                count($request->part_no) === count($request->file('part_img'))
            ) {
                
                // upload pattern image
                if($request->file('pattern_img')){
                    $imagePattern = $request->file('pattern_img');
                    $extImage = $imagePattern->getClientOriginalExtension();
                    $pattern_img = strtolower($request->sku."-". count($request->part_no) ."-".time().".".$extImage);
                    $destinationPath = public_path().'/assets/pattern-img';
                    $imagePattern->move($destinationPath, $pattern_img);
                }

                $save_data = new MasterPattern;
                $save_data->name = $request->name;
                $save_data->sku = $request->sku;
                $save_data->pattern_img = $pattern_img;
                $save_data->status = 1;
                
                if ($save_data->save()){
                    foreach($request->part_no as $key => $part_no){
                        // upload part images
                        $parts_sku = strtoupper($request->sku."-". $part_no ."-".$request->part_name[$key]);
                        if($request->file('part_img')[$key]){
                            $image = $request->file('part_img')[$key];
                            $extImage = $image->getClientOriginalExtension();
                            $parts_img = strtolower($request->sku."-parts-".$parts_sku."-".time().".".$extImage);
                            $parts_img = str_replace('/', '', $parts_img);
                            $destinationPath = public_path().'/assets/pattern-img';
                            $image->move($destinationPath, $parts_img);
                        }
                        
                        $save_part_data = new MasterPatternPart;
                        $save_part_data->name = $request->part_name[$key];
                        $save_part_data->sku = $parts_sku;
                        $save_part_data->pattern_id = $save_data->id;
                        $save_part_data->part_no = $part_no;
                        $save_part_data->parts_img = $parts_img;
                        $save_part_data->is_printing = $request->is_printing[$key];
                        $save_part_data->is_embroidery = $request->is_embroidery[$key];
                        $save_part_data->status = 1;
                        $save_part_data->save();
                    }
                } else {
                    throw new \Exception("Something Wrong");
                }

            } else {
                throw new \Exception("Something Wrong");
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(Request $request){
        $data = MasterPattern::where('id',$request->id)->first();
        return $data;
    }
    public function editParts(Request $request){
        $data = MasterPatternPart::where('pattern_id',$request->id)->where('status',1)->get();
        return $data;
    }
    public function update(Request $request){
        DB::beginTransaction();
        try {  

            /// update existing data 
            $pattern = MasterPattern::find($request->id);   // id comes from route

            // Base data (always updated)
            $pattern->name   = $request->name;
            $pattern->sku    = $request->sku;
            $pattern->status = 1;
        
            // upload pattern image
            if($request->file('pattern_img')){
                $imagePattern = $request->file('pattern_img');
                $extImage = $imagePattern->getClientOriginalExtension();
                $pattern_img = strtolower($request->sku."-". count($request->part_no) ."-".time().".".$extImage);
                $destinationPath = public_path().'/assets/pattern-img';
                $imagePattern->move($destinationPath, $pattern_img);
                $pattern->pattern_img = $pattern_img;
            }
            $pattern->save();
            // fabric logic 
            if (!empty($request->old_parts_id) && is_array($request->old_parts_id)) {
                $deleted_parts_id = [];
                foreach ($request->old_parts_id as  $parts_id) {
                    if (array_key_exists($parts_id, $request->old_part_no) && array_key_exists($parts_id, $request->old_part_name)){
                        $parts_data = MasterPatternPart::find($parts_id);
                        if ($parts_data) {
                            $updateData = [
                                'name'          => $request->old_part_name[$parts_id],
                                'sku'           => strtoupper($request->old_part_name[$parts_id]),
                                'part_no'       => $request->old_part_no[$parts_id],
                                'is_printing'   => $request->old_is_printing[$parts_id] ?? 0,
                                'is_embroidery' => $request->old_is_embroidery[$parts_id] ?? 0,
                            ];

                            // ✔ If new image uploaded → update
                            if ($request->hasFile("old_part_img.$parts_id")) {

                                $file = $request->file("old_part_img.$parts_id");
                                $ext  = $file->getClientOriginalExtension();

                                $fileName = strtolower($request->sku . "-parts-" . time() . "." . $ext);
                                $fileName = str_replace('/', '', $fileName);
                                $file->move(public_path("assets/pattern-img"), $fileName);

                                $updateData['parts_img'] = $fileName;
                            }

                            $parts_data->update($updateData);
                        }
                    } else {
                        $deleted_parts_id[] = $parts_id;
                    } 
                }
            }

            if (!empty($request->part_no) &&
                !empty($request->part_name) &&
                !empty($request->is_printing) &&
                !empty($request->is_embroidery) && 
                count($request->part_no) === count($request->part_name) &&
                count($request->part_no) === count($request->is_printing) &&
                count($request->part_no) === count($request->is_embroidery) &&
                count($request->part_no) === count($request->file('part_img'))
            ) {
                foreach($request->part_no as $key => $part_no){
                    // upload part images
                    $parts_sku = strtoupper($request->sku."-". $part_no ."-".$request->part_name[$key]);
                    if($request->file('part_img')[$key]){
                        $image = $request->file('part_img')[$key];
                        $extImage = $image->getClientOriginalExtension();
                        $parts_img = strtolower($request->sku."-parts-".$parts_sku."-".time().".".$extImage);
                        $parts_img = str_replace('/', '', $parts_img);
                        $destinationPath = public_path().'/assets/pattern-img';
                        $image->move($destinationPath, $parts_img);
                    }
                    $save_part_data = new MasterPatternPart;
                    $save_part_data->name = $request->part_name[$key];
                    $save_part_data->sku = $parts_sku;
                    $save_part_data->pattern_id = $pattern->id;
                    $save_part_data->part_no = $part_no;
                    $save_part_data->parts_img = $parts_img;
                    $save_part_data->is_printing = $request->is_printing[$key];
                    $save_part_data->is_embroidery = $request->is_embroidery[$key];
                    $save_part_data->status = 1;
                    $save_part_data->save();
                }
            } 
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(Request $request){
        $data = MasterPattern::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

}