@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Add Adjustment Master</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <form action="{{ route('admin.payment.master.adjustment_master.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>Display Name (e.g. Committee) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Model Class (e.g. App\Models\Committee) <span class="text-danger">*</span></label>
                            <select name="model_name" class="form-control" required>
                                <option value="">-- Select Model --</option>
                                <option value="App\Models\Committee">Committee</option>
                                <option value="App\Models\Commission">Commission</option>
                                <option value="App\Models\GeneralExpense">General Expense</option>
                                <option value="App\Models\ElectricityExpense">Electricity Expense</option>
                                <option value="App\Models\Rent">Rent</option>
                                <option value="App\Models\TelephoneExpense">Telephone Expense</option>
                                <option value="App\Models\Tax">Tax</option>
                                <option value="App\Models\Interest">Interest</option>
                                <option value="App\Models\TourExpense">Tour Expense</option>
                                <option value="App\Models\Contractor">Contractor</option>
                                <option value="App\Models\ConsumableGood">Consumable Good</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">Create Master</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
