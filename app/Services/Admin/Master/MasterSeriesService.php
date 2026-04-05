<?php
namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use App\Models\MasterSeries;
use App\Http\DataTable\Admin\Master\MasterSeriesDataTable as DataTable;

class MasterSeriesService {
    protected $datatable;
    protected $masterSeries;
    public function __construct(
        DataTable $datatable,
        MasterSeries $masterSeries
    ) {
        $this->datatable = $datatable;
        $this->masterSeries = $masterSeries;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        $save_data = new MasterSeries;
        $save_data->name = $request->name;
        $save_data->sku = NULL;
        $save_data->status = $request->status;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        return MasterSeries::where('id', $request->id)->first();
    }
    public function update(Request $request){
        $update_data = MasterSeries::find($request->id);
        $update_data->name = $request->name;
        $update_data->sku = NULL;
        $update_data->status = $request->status;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        return MasterSeries::where('id', $request->id)->update([
            'status' => 3,
        ]);
    }
}