<?php
$base = 'c:\\xampp\\htdocs\\keshav-madhav';

$model = <<<EOD
<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSeries extends Model
{
    use HasFactory;
    protected \$table = 'master_series';
    protected \$fillable = [
        'id', 'sno', 'company_id', 'sub_company_id', 'project_id',
        'sku', 'name', 'status', 'created_at', 'updated_at'
    ];
}
EOD;

$controller = <<<EOD
<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\MasterSeriesService as Service;
use Auth;

class MasterSeriesController extends Controller { 
    protected \$service;
    public function __construct(Service \$service) {
        \$this->service = \$service;
    }
    public function index(){
        return view('admin.master.series.index');
    } 
    public function indexList(Request \$request){
        return \$this->service->indexList(\$request);
    }
    public function create(){
        return view('admin.master.series.create');
    }
    public function store(Request \$request){
        \$request->validate([
            'name' => 'required'
        ]);
        \$data = \$this->service->store(\$request);
        return redirect()->route('admin.master.series.index')->withSuccess('The series has been successfully created.');
    }
    public function delete(Request \$request){
        \$data = \$this->service->delete(\$request);
        return redirect()->route('admin.master.series.index')->withSuccess('The series has been successfully deleted.'); 
    }
    public function edit(Request \$request){
        \$response['data'] = \$this->service->edit(\$request);
        return view('admin.master.series.edit', \$response);
    }
    public function update(Request \$request){
        \$request->validate([
            'name' => 'required'
        ]);
        \$data = \$this->service->update(\$request);
        return redirect()->route('admin.master.series.index')->withSuccess('The series has been successfully updated.');
    }
}
EOD;

$service = <<<EOD
<?php
namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use App\Models\MasterSeries;
use App\Http\DataTable\Admin\Master\MasterSeriesDataTable as DataTable;

class MasterSeriesService {
    public function __construct(
        DataTable \$datatable,
        MasterSeries \$masterSeries
    ) {
        \$this->datatable = \$datatable;
        \$this->masterSeries = \$masterSeries;
    }

    public function indexList(Request \$request){
        return \$this->datatable->indexList(\$request);
    }

    public function store(Request \$request){
        \$save_data = new MasterSeries;
        \$save_data->name = \$request->name;
        \$save_data->sku = \$request->sku;
        \$save_data->status = 1;
        \$save_data->save();
        return true;
    }

    public function edit(Request \$request){
        return MasterSeries::where('id', \$request->id)->first();
    }
    public function update(Request \$request){
        \$update_data = MasterSeries::find(\$request->id);
        \$update_data->name = \$request->name;
        \$update_data->save();
        return true;
    }

    public function delete(Request \$request){
        return MasterSeries::where('id', \$request->id)->update([
            'status' => 0,
        ]);
    }
}
EOD;

$datatable = <<<EOD
<?php
namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\MasterSeries;
use Yajra\DataTables\Facades\DataTables;

class MasterSeriesDataTable  {
    public function indexList(\$request){
        \$queue = MasterSeries::query();

        return DataTables::of(\$queue)->addIndexColumn()
            ->filter(function (\$query) use (\$request) {
                \$query->orderBy('id','asc');
                \$query->orWhere('name', 'like', "%{\(\$request->get('search')['value'])}\%");
                if (\$request->has('name') && !empty(\$request->name)) {
                    \$query->where('name', 'like', "%{\(\$request->get('name'))}\%");
                }
                if (\$request->has('sku') && !empty(\$request->sku)) {
                    \$query->where('sku', 'like', "%{\(\$request->get('sku'))}\%");
                }
            }) 
            ->editColumn('status', function (\$queue) {
				\$status= \$queue->status;
                return (\$status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('action', function (\$queue) {
				\$parameter= \$queue->id;
                return '<a href="' . route('admin.master.series.edit', ['id' => \$parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-muted"></i></a>';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}
EOD;

file_put_contents("$base/app/Models/MasterSeries.php", $model);
file_put_contents("$base/app/Http/Controllers/Admin/Master/MasterSeriesController.php", $controller);
file_put_contents("$base/app/Services/Admin/Master/MasterSeriesService.php", $service);
file_put_contents("$base/app/Http/DataTable/Admin/Master/MasterSeriesDataTable.php", $datatable);

@mkdir("$base/resources/views/admin/master/series", 0777, true);
$str = file_get_contents("$base/resources/views/admin/master/designs/index.blade.php");
$str = str_replace(['designs', 'Design', 'design'], ['series', 'Series', 'series'], $str);
file_put_contents("$base/resources/views/admin/master/series/index.blade.php", $str);

$str = file_get_contents("$base/resources/views/admin/master/designs/create.blade.php");
$str = str_replace(['designs', 'Design', 'design'], ['series', 'Series', 'series'], $str);
file_put_contents("$base/resources/views/admin/master/series/create.blade.php", $str);

$str = file_get_contents("$base/resources/views/admin/master/designs/edit.blade.php");
$str = str_replace(['designs', 'Design', 'design'], ['series', 'Series', 'series'], $str);
file_put_contents("$base/resources/views/admin/master/series/edit.blade.php", $str);

$routes = file_get_contents("$base/routes/web.php");
$seriesRoute = <<<EOD
Route::prefix('master/series')->name('master.series.')->group(function () {
            Route::get('/index', [AdminMasterSeriesController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterSeriesController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminMasterSeriesController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterSeriesController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterSeriesController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterSeriesController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterSeriesController::class, 'delete'])->name('delete');
        });
EOD;
if(strpos($routes, "AdminMasterSeriesController") === false) {
    if(preg_match('/use App\\\\Http\\\\Controllers\\\\Admin\\\\Master\\\\MasterDesignController\s*as\s*AdminMasterDesignController;/', $routes)){
        $routes = str_replace("use App\Http\Controllers\Admin\Master\MasterDesignController as AdminMasterDesignController;", "use App\Http\Controllers\Admin\Master\MasterDesignController as AdminMasterDesignController;\nuse App\Http\Controllers\Admin\Master\MasterSeriesController as AdminMasterSeriesController;", $routes);
    } else {
        $routes = str_replace("use App\Http\Controllers\Admin\Master\MasterDesignController;", "use App\Http\Controllers\Admin\Master\MasterDesignController;\nuse App\Http\Controllers\Admin\Master\MasterSeriesController as AdminMasterSeriesController;", $routes);
    }
    
    $routes = str_replace("Route::prefix('master/designs')->name('master.designs.')->group(function () {", $seriesRoute."\n\n        Route::prefix('master/designs')->name('master.designs.')->group(function () {", $routes);
    file_put_contents("$base/routes/web.php", $routes);
}

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\DB::statement("CREATE TABLE IF NOT EXISTS master_series (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sno INT NULL,
        company_id INT NULL,
        sub_company_id INT NULL,
        project_id INT NULL,
        sku VARCHAR(255) NULL,
        name VARCHAR(255) NULL,
        status INT DEFAULT 1,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );");
} catch(Exception $e) {}

try {
    Illuminate\Support\Facades\DB::statement('ALTER TABLE production_goods DROP COLUMN series_name;');
} catch(Exception $e) {}

try {
    Illuminate\Support\Facades\DB::statement('ALTER TABLE production_goods ADD master_series_id INT NULL AFTER name_of_garment;');
} catch(Exception $e) {}

echo "Script complete";
