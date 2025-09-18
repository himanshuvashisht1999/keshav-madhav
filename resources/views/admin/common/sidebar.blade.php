<?php
    $page_url = ($_SERVER['REQUEST_URI']);
    $general_setting = App\Models\GeneralSettings::where('status',1)->first();
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{route('admin.user.profileEdit')}}" class="brand-link">
        <img src="{{$general_setting->logo}}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light">{{Auth::guard('admin')->user()->first_name}}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="{{route('admin.dashboard')}}" class="{{str_contains($page_url,'admin/dashboard') ? 'nav-link active' : 'nav-link'}}" style="position:static;">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    
                    
                    <!-- Master Settings (Dropdown) -->
                    <li class="{{str_contains($page_url,'admin/master') ? 'nav-item menu-open' : 'nav-item'}}">
                        <a href="#" class="{{str_contains($page_url,'admin/master') ? 'nav-link active' : 'nav-link'}}">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>
                                Master Settings
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            
                            <li class="nav-item">
                                <a href="{{route('admin.master.vendor.index')}}" class="{{str_contains($page_url,'admin/master/vendors') ? 'nav-link active' : 'nav-link'}}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Vendors</p>
                                </a>
                            </li>
                            
                            <!-- General Settings -->
                            <li class="nav-item">
                                <a href="{{route('admin.settings.edit')}}" class="{{str_contains($page_url,'admin/master/setting') ? 'nav-link active' : 'nav-link'}}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>General Settings</p>
                                </a>
                            </li>
                            
                        </ul>
                    </li>

                    <!-- Logout -->
                    <li class="nav-item">
                        <a href="{{route('admin.logout')}}" class="nav-link" style="position:static;">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                    </li>

                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </div>
</aside>
