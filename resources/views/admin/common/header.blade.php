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
          // Result of the last balance check (daily schedule or "Run check now")
          $balanceReportFile = storage_path('app/' . \App\Services\Ledger\BalanceReconciler::REPORT_FILE);
          $balanceReport = is_file($balanceReportFile) ? json_decode(@file_get_contents($balanceReportFile), true) : null;
          $balanceMismatches = $balanceReport['mismatch_count'] ?? null;
        @endphp
        <li class="nav-item">
          @if($balanceMismatches === null)
            <a class="nav-link text-muted" href="{{ route('admin.balance-issues') }}" title="Balances have not been checked yet">
              <i class="fas fa-balance-scale"></i> Balance check
            </a>
          @elseif($balanceMismatches > 0)
            <a class="nav-link text-danger font-weight-bold" href="{{ route('admin.balance-issues') }}" title="{{ $balanceMismatches }} account(s) differ from their ledger">
              <i class="fas fa-exclamation-triangle"></i> Balances: {{ $balanceMismatches }} differ
            </a>
          @else
            <a class="nav-link text-success" href="{{ route('admin.balance-issues') }}" title="All balances match their ledgers">
              <i class="fas fa-check-circle"></i> Balances OK
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
            <a class="nav-link text-info" href="#" id="global_auto_assign_stock" title="Auto Assign Stock for orders waiting in Order-more-than-stock storerooms">
                <i class="fas fa-magic"></i>
            </a>
        </li>
      </ul>
    </nav>
    <!-- /.navbar -->