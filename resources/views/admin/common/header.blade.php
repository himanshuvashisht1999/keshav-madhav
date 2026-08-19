<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="{{route('admin.dashboard')}}" class="nav-link">Home</a>
        </li>
       
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        <li class="nav-item">
          <a class="nav-link" href="javascript:history.back()" role="button">
            <i class="fas fa-arrow-left"></i>
          </a>
        </li>
        @php
          $isCarriedForward = \App\Models\CarryForwardLog::where('financial_year', \App\Models\MasterOpeningBalance::getCurrentFinancialYear())->exists();
        @endphp
        <li class="nav-item">
          @if(!$isCarriedForward)
            <a class="nav-link text-primary" href="{{ route('admin.carry-forward-balances') }}" 
               onclick="return confirm('Are you sure you want to carry forward all current balances as opening balances for the financial year {{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }}? This will update existing opening balances for this year.')" 
               title="Carry Forward Balances">
              <i class="fas fa-file-invoice-dollar"></i> Carry Forward
            </a>
          @else
            <a class="nav-link text-muted disabled" href="javascript:void(0)" title="Balances already carried forward for this year">
              <i class="fas fa-check-circle"></i> Carried Forward
            </a>
          @endif
        </li>
        <li class="nav-item">
          <a class="nav-link text-success font-weight-bold" href="{{ route('admin.download-db') }}" title="Download Database SQL Backup">
            <i class="fas fa-database"></i> Download DB
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
            <i class="fas fa-expand-arrows-alt"></i>
          </a>
        </li>
        <!-- 
        <li class="nav-item">
          <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
            <i class="fas fa-th-large"></i>
          </a>
        </li>
        -->
        <li class="nav-item">
            <a class="nav-link text-info" href="#" id="global_auto_assign_stock" title="Auto Assign Stock for ADVANCE SAMPLE">
                <i class="fas fa-magic"></i>
            </a>
        </li>
      </ul>
    </nav>
    <!-- /.navbar -->