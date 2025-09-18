@extends('admin.layouts.app')
@section('content')
<style>
   .card{
      box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,0.8);
   }
</style>
<div class="content-wrapper">
   <!-- Content Header (Page header) -->
   <div class="content-header">
      <div class="container-fluid">
         <div class="row mb-2">
            <div class="col-sm-6">
               <h1 class="m-0">Dashboard</h1>
            </div>
            <div class="col-sm-6">
               <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active">Dashboard</li>
               </ol>
            </div>
         </div>
      </div>
   </div>
   <!-- /.content-header -->
   <!-- Main content -->
   <div class="content" style="display:none;">
      <div class="container-fluid">
         <form method="GET" action="{{ route('admin.dashboard') }}" class="mb-3">
            <div class="row">
               
               <div class="col-md-9">
                   <h5 class="text-muted">Welcome to the Dashboard, Admin!</h5>
               </div>
               <div class="col-md-3">
                  <span>Filter By Year</span>
                     <select name="year" class="form-control" onchange="this.form.submit()">
                        @for($y = date('Y'); $y >= 2020; $y--)
                           <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                     </select>
               </div>
            </div>
         </form>
         
         <div class="">
            <div class="row">
               <div class="col-sm-4">
                  <div class="card">
                     <div class="card-body count">
                        <div class="row" style="justify-content: center">
                           <div class="col-auto">
                                 <h4 class="text-muted mb-3">Total Users</h4>
                           </div>
                        </div>
                        <div class="row" style="justify-content: center">
                           <div class="col-auto">
                                 <h2>{{$total_users}}</h2>
                           </div>
                        </div> 
                     </div> 
                  </div>
               </div> 

               <div class="col-md-4">
                  <div class="card">
                        <div class="card-header bg-primary text-white">Patients by Month ({{ $year }})</div>
                        <div class="card-body">
                           <canvas id="usersChart" height="200"></canvas>
                        </div>
                  </div>
               </div>
               

               
            </div> 
              
        </div>
         
      </div>
      <!-- /.container-fluid -->
   </div>
   <!-- /.content -->
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    const users = new Array(12).fill(0);
    const bookings = new Array(12).fill(0);
    const income = new Array(12).fill(0);

    @foreach($chartData['users'] as $month => $count)
        users[{{ $month - 1 }}] = {{ $count }};
    @endforeach

    

    const createChart = (id, label, data, color) => {
        new Chart(document.getElementById(id).getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: color
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    };

    createChart("usersChart", "Users", users, "#007bff");

</script>



@endsection