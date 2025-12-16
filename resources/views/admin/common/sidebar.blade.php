<?php
$page_url = $_SERVER['REQUEST_URI'];
$general_setting = App\Models\GeneralSettings::where('status', 1)->first();
$stage_data = App\Models\MasterProductStage::orderBy('status','desc')->get();
?>
<style>
    /* Base links (Dashboard, Purchase, Stock, etc.) */
    .main-sidebar .nav-sidebar > .nav-item > .nav-link {
        padding-left: 18px;   /* default: ~12px, adjust as you like */
    }

    /* First level inside treeview (Fabric, Item under Purchase / Stock / Receipt / etc.) */
    .main-sidebar .nav-treeview > .nav-item > .nav-link {
        padding-left: 34px;   /* more indent than parent */
        font-size: 0.92rem;   /* optional: slightly smaller */
    }

    /* Second-level (if you ever have nested inside nested) */
    .main-sidebar .nav-treeview .nav-treeview > .nav-item > .nav-link {
        padding-left: 46px;
    }

    /* Give the icons a bit of space from text */
    .main-sidebar .nav-link .nav-icon,
    .main-sidebar .nav-link i.far,
    .main-sidebar .nav-link i.fas {
        margin-right: 8px;
    }
</style>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.user.profileEdit') }}" class="brand-link">
        <img src="{{ $general_setting->logo }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">{{ Auth::guard('admin')->user()->first_name }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false">
                    <!-- Dashboard -->
                    <!-- <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}"
                            class="{{ str_contains($page_url, 'admin/dashboard') ? 'nav-link active' : 'nav-link' }} border_class"
                            style="position:static;">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>DASHBOARD</p>
                        </a>
                    </li> -->
                    <li class="{{ (str_contains($page_url, 'admin/purchase-order') || str_contains($page_url, 'admin/purchase-order')) ? 'nav-item menu-open' : 'nav-item' }} ">
                        <a href="#"
                            class="{{ str_contains($page_url, 'admin/purchase-order') ? 'nav-link active' : 'nav-link' }} border_class" >
                            <i class="nav-icon fas fa-cube"></i>
                            <p>
                                PURCHASE ORDER
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                                <li class="nav-item">
                                <a href="{{ route('admin.purchase_order.estimation') }}" class="{{ (str_contains($page_url, 'admin/purchase-order/estimation')) ? 'nav-link active' : 'nav-link' }}" style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Create PO For Fabric</p>
                                </a>
                            </li>
                        </ul>

                        <ul class="nav nav-treeview">
                                <li class="nav-item">
                                <a href="{{ route('admin.purchase_order.index') }}" class="{{ (str_contains($page_url, 'admin/purchase-order/index') || str_contains($page_url, 'admin/purchase-order/view')) ? 'nav-link active' : 'nav-link' }}" style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Fabric POs</p>
                                </a>
                            </li>
                        </ul>
                       
                        <ul class="nav nav-treeview">
                                <li class="nav-item">
                                <a href="{{ route('admin.purchase_order.adjustment') }}" class="{{ (str_contains($page_url, 'admin/purchase-order/adjustment') ) ? 'nav-link active' : 'nav-link' }}" style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Adjust POs With Fabric Shipments</p>
                                </a>
                            </li>
                        </ul>

                        

                        <ul class="nav nav-treeview">
                                <li class="nav-item">
                                <a href="{{ route('admin.purchase_order.index') }}" class="{{ (str_contains($page_url, 'admin/purchase-order/receipts') && !str_contains($page_url, 'admin/purchase-order-material')) ? 'nav-link active' : 'nav-link' }}" style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>PO Reports</p>
                                </a>
                            </li>
                        </ul>
                       
                        <!-- <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.purchase_order_material.index') }}" class="{{ str_contains($page_url, 'admin/purchase-order-material') ? 'nav-link active' : 'nav-link' }}" style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Items</p>
                                </a>
                            </li>
                        </ul> -->
                    </li> 
                    
                    <li class="{{ (str_contains($page_url, 'admin/fabric-receipt') || str_contains($page_url, 'admin/stock/fabricIndex')) ? 'nav-item menu-open' : 'nav-item' }} ">
                        <a href="#"
                            class="{{ str_contains($page_url, 'admin/fabric-receipt') || str_contains($page_url, 'admin/stock/fabricIndex') ? 'nav-link active' : 'nav-link' }} border_class" >
                            <i class="nav-icon fas fa-cube"></i>
                            <p>
                                FABRIC SHIPMENTS & STOCK
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                                <li class="nav-item">
                                <a href="{{ route('admin.fabric_receipt.index') }}" class="{{ (str_contains($page_url, 'admin/fabric-receipt')) ? 'nav-link active' : 'nav-link' }}" style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Fabric Shipment Receipt</p>
                                </a>
                            </li>
                        </ul>
                       
                        <ul class="nav nav-treeview">
                                <li class="nav-item">
                                <a href="{{ route('admin.stock.fabricIndex') }}" class="{{ (str_contains($page_url, 'admin/stock') ) ? 'nav-link active' : 'nav-link' }}" style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Stock Reports</p>
                                </a>
                            </li>
                        </ul>
                       
                    </li>
                    
                    <!-- <li class="nav-item">
                        <a href="{{ route('admin.fabric_receipt.index') }}"
                            class="{{ str_contains($page_url, 'admin/fabric-receipt') ? 'nav-link active' : 'nav-link' }}  border_class"
                            style="position:static;">
                            <i class="nav-icon fas fa-receipt"></i>
                            <p>FABRIC SHIPMENTS & STOCK</p>
                        </a>
                    </li> -->

                    <!-- <li class="nav-item">
                        <a href="{{ route('admin.stock.fabricIndex') }}"
                            class="{{ str_contains($page_url, 'admin/stock') ? 'nav-link active' : 'nav-link' }}"
                            style="position:static;">
                            <i class="fas fa-store nav-icon"></i>
                            <p>Fabric Stock</p>
                        </a>
                    </li> -->
                
                    
                    <!-- <li class="{{ (str_contains($page_url, 'admin/stock') || str_contains($page_url, 'admin/item-stock')) ? 'nav-item menu-open' : 'nav-item' }}">
                        <a href="#"
                            class="{{ (str_contains($page_url, 'admin/stock') || str_contains($page_url, 'admin/item-stock')) ? 'nav-link active' : 'nav-link' }}">
                            <i class="fas fa-store nav-icon"></i>
                            <p>
                                Stock
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                               <a href="{{ route('admin.stock.fabricIndex') }}"
                                    class="{{ str_contains($page_url, 'admin/stock') ? 'nav-link active' : 'nav-link' }}"
                                    style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Fabric</p>
                                </a>
                            </li>
                        </ul> -->
                        <!-- <ul class="nav nav-treeview">
                            <li class="nav-item">
                               <a href="{{ route('admin.item_stock.itemIndex') }}"
                                    class="{{ str_contains($page_url, 'admin/item-stock') ? 'nav-link active' : 'nav-link' }}"
                                    style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Item</p>
                                </a>
                            </li>
                        </ul> -->
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.sales_order.create') }}"
                            class="{{ str_contains($page_url, 'admin/sales-order') ? 'nav-link active' : 'nav-link' }} border_class"
                            style="position:static;">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Sales Order</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.product_order.indexOrder') }}"
                            class="{{ str_contains($page_url, 'admin/production-order') ? 'nav-link active' : 'nav-link' }} border_class"
                            style="position:static;">
                            <i class="nav-icon fas fa-industry"></i>
                            <p>List Sales Order</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.order_digitalization.create-slips-production') }}"
                            class="{{ str_contains($page_url, 'admin.order_digitalization.create-slips-production') ? 'nav-link active' : 'nav-link' }} border_class"
                            style="position:static;">
                            <i class="nav-icon fas fa-industry"></i>
                            <p>Hand Slip Digitalization</p>
                        </a>
                    </li>
                    <li class="{{ str_contains($page_url, 'admin/report') ? 'nav-item menu-open' : 'nav-item' }}">
                        <a href="#"
                            class="{{ str_contains($page_url, 'admin/report') ? 'nav-link active' : 'nav-link' }}">
                            <i class="nav-icon fa fa-print" aria-hidden="true"></i>
                            <p>
                                Reports
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.report.sales-order') }}"
                                    class="{{ str_contains(strtolower($page_url), 'admin/reports/sales-order')  ? 'nav-link active' : 'nav-link' }}"
                                    >
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Sales Order</p>
                                </a>
                            </li>
                            
                        </ul>
                    </li>
                     {{-- <li class="{{ (str_contains($page_url, 'admin/order_digitalization') || str_contains($page_url, 'admin/order_digitalization/create-slips-production')) ? 'nav-item menu-open' : 'nav-item' }} ">
                        <a href="#"
                            class="{{ str_contains($page_url, 'admin/order_digitalization') || str_contains($page_url, 'admin/order_digitalization/create-slips-production') ? 'nav-link active' : 'nav-link' }} border_class" >
                            <i class="nav-icon fas fa-cube"></i>
                            <p>
                                ORDER DIGITALIZATION
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                                <li class="nav-item">
                                <a href="{{ route('admin.order_digitalization.create-slips-production') }}" class="{{ (str_contains($page_url, 'admin/order_digitalization')) ? 'nav-link active' : 'nav-link' }}" style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Production Slips Digitalization</p>
                                </a>
                            </li>
                        </ul>
                       
                        <ul class="nav nav-treeview">
                                <li class="nav-item">
                                <a href="{{ route('admin.order_digitalization.create-rolls-assign') }}" class="{{ (str_contains($page_url, 'admin/order_digitalization') ) ? 'nav-link active' : 'nav-link' }}" style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Fabric Rolls Assigning</p>
                                </a>
                            </li>
                        </ul>
                       
                    </li> --}}
                    {{-- <li class="nav-item">
                        <a href="{{ route('admin.product_order.indexOrder') }}"
                            class="{{ str_contains($page_url, 'admin/production-order') ? 'nav-link active' : 'nav-link' }}"
                            style="position:static;">
                            <i class="nav-icon fas fa-industry"></i>
                            <p>Production</p>
                        </a>
                    </li> --}}
                    <li class="{{ str_contains($page_url, 'admin/order-stages') ? 'nav-item menu-open' : 'nav-item' }}" style="display:none;">
                        <a href="#"
                            class="{{ str_contains($page_url, 'admin/order-stages') ? 'nav-link active' : 'nav-link' }}">
                            <i class="nav-icon fas fa-cube"></i>
                            <p>
                                Stages
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        
                        <ul class="nav nav-treeview">
                            @foreach($stage_data as $stage)
                                
                            <li class="nav-item">
                                <a href="{{ route('admin.order-stages.index',['stage_id' => $stage->id]) }}"
                                    class="{{ str_contains(strtolower($page_url), 'admin/order-stages') &&  (request('stage_id') == $stage->id)  ? 'nav-link active' : 'nav-link' }}"
                                    >
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{$stage->name}}</p>
                                </a>
                            </li>

                            @endforeach
                        </ul>
                    </li>

                    <li class="{{ (str_contains($page_url, 'admin/warehouse') || str_contains($page_url, 'admin/warehouse')) ? 'nav-item menu-open' : 'nav-item' }}"  style="display:none;">
                        <a href="#"
                            class="{{ (str_contains($page_url, 'admin/warehouse') || str_contains($page_url, 'admin/item-receipt')) ? 'nav-link active' : 'nav-link' }}">
                            <i class="nav-icon fas fa-receipt"></i>
                            <p>
                                Packaging 
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                       
                        <ul class="nav nav-treeview">
                                <li class="nav-item">
                                <a href="{{ route('admin.warehouse.indexOrder') }}"
                                    class="{{ str_contains($page_url, 'admin/warehouse/index') ? 'nav-link active' : 'nav-link' }}"
                                    style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Packaging</p>
                                </a>
                            </li>
                        </ul>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.warehouse.listing') }}"
                                    class="{{ str_contains($page_url, 'admin/warehouse/listing') ? 'nav-link active' : 'nav-link' }}"
                                    style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Stock</p>
                                </a>
                            </li>
                        </ul>
                        
                    </li>

                    <!-- Master Settings (Dropdown) -->
                    <li class="{{ str_contains($page_url, 'admin/master') ? 'nav-item menu-open' : 'nav-item' }}">
                        <a href="#" class="{{ str_contains($page_url, 'admin/master') ? 'nav-link active' : 'nav-link' }}">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>
                                Masters
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            {{-- ================= FABRIC MASTER ================= --}}
                            <li class="{{ (
                                    str_contains($page_url, 'admin/master/fabric_dye') ||
                                    str_contains($page_url, 'admin/master/fabric_composition') ||
                                    str_contains($page_url, 'admin/master/fabric_gsm') ||
                                    str_contains($page_url, 'admin/master/fabric_weave') ||
                                    str_contains($page_url, 'admin/master/fabric_width') ||
                                    ($page_url === '/admin/master/fabric' || str_starts_with($page_url, '/admin/master/fabric/'))
                                ) ? 'nav-item menu-open' : 'nav-item' }}">
                                <a href="#"
                                    class="{{ (
                                            str_contains($page_url, 'admin/master/fabric_dye') ||
                                            str_contains($page_url, 'admin/master/fabric_composition') ||
                                            str_contains($page_url, 'admin/master/fabric_gsm') ||
                                            str_contains($page_url, 'admin/master/fabric_weave') ||
                                            str_contains($page_url, 'admin/master/fabric_width') ||
                                            ($page_url === '/admin/master/fabric' || str_starts_with($page_url, '/admin/master/fabric/'))
                                        ) ? 'nav-link active' : 'nav-link' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>
                                        Fabric Master
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>

                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.master.fabric_dye.index') }}"
                                            class="{{ str_contains($page_url, 'admin/master/fabric_dye') ? 'nav-link active' : 'nav-link' }}">
                                            <i class="fas fa-circle"></i>
                                            <p>Dye</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('admin.master.fabric_composition.index') }}"
                                            class="{{ str_contains($page_url, 'admin/master/fabric_composition') ? 'nav-link active' : 'nav-link' }}">
                                            <i class="fas fa-circle"></i>
                                            <p>Composition</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('admin.master.fabric_gsm.index') }}"
                                            class="{{ str_contains($page_url, 'admin/master/fabric_gsm') ? 'nav-link active' : 'nav-link' }}">
                                            <i class="fas fa-circle"></i>
                                            <p>GSM</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('admin.master.fabric_weave.index') }}"
                                            class="{{ str_contains($page_url, 'admin/master/fabric_weave') ? 'nav-link active' : 'nav-link' }}">
                                            <i class="fas fa-circle"></i>
                                            <p>Weave</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('admin.master.fabric_width.index') }}"
                                            class="{{ str_contains($page_url, 'admin/master/fabric_width') ? 'nav-link active' : 'nav-link' }}">
                                            <i class="fas fa-circle"></i>
                                            <p>Width</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('admin.master.fabric.index') }}"
                                            class="{{ $page_url === '/admin/master/fabric' || str_starts_with($page_url, '/admin/master/fabric/') ? 'nav-link active' : 'nav-link' }}">
                                            <i class="fas fa-circle"></i>
                                            <p>Fabric</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            {{-- ================= PRODUCT MASTER ================= --}}
                            <li class="{{ (
                                    str_contains($page_url, 'admin/master/colors') ||
                                    str_contains($page_url, 'admin/master/product-types') ||
                                    str_contains($page_url, 'admin/master/size-measurement') ||
                                    str_contains($page_url, 'admin/master/size') ||
                                    str_contains($page_url, 'admin/master/product-stage') ||
                                    str_contains($page_url, 'admin/master/product-sub-stage') ||
                                    str_contains($page_url, 'admin/master/pattern')
                                ) ? 'nav-item menu-open' : 'nav-item' }}">
                                <a href="#"
                                    class="{{ (
                                            str_contains($page_url, 'admin/master/colors') ||
                                            str_contains($page_url, 'admin/master/product-types') ||
                                            str_contains($page_url, 'admin/master/size-measurement') ||
                                            str_contains($page_url, 'admin/master/size') ||
                                            str_contains($page_url, 'admin/master/product-stage') ||
                                            str_contains($page_url, 'admin/master/product-sub-stage') ||
                                            str_contains($page_url, 'admin/master/pattern')
                                        ) ? 'nav-link active' : 'nav-link' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>
                                        Product Master
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>

                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.master.colors.index') }}"
                                            class="{{ str_contains($page_url, 'admin/master/colors') ? 'nav-link active' : 'nav-link' }}">
                                            <i class="fas fa-circle"></i>
                                            <p>Color</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('admin.master.product-types.index') }}"
                                            class="{{ str_contains($page_url, 'admin/master/product-types') ? 'nav-link active' : 'nav-link' }}">
                                            <i class="fas fa-circle"></i>
                                            <p>Types</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('admin.master.size-measurement.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.master.size-measurement.*') ? 'active' : '' }}">
                                            <i class="fas fa-circle"></i>
                                            <p>Size Group</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('admin.master.size.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.master.size.*') ? 'active' : '' }}">
                                            <i class="fas fa-circle"></i>
                                            <p>Standard Size</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('admin.master.product_stage.index') }}"
                                            class="{{ (str_contains($page_url, 'admin/master/product-stage') || str_contains($page_url, 'admin/master/product-sub-stage')) ? 'nav-link active' : 'nav-link' }}">
                                            <i class="fas fa-circle"></i>
                                            <p>Stages</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('admin.master.pattern.index') }}"
                                            class="{{ str_contains($page_url, 'admin/master/pattern') ? 'nav-link active' : 'nav-link' }}">
                                            <i class="fas fa-circle"></i>
                                            <p>Pattern</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            {{-- ===== OTHER MASTERS (KEEP AS THEY ARE) ===== --}}
                            <li class="nav-item">
                                <a href="{{ route('admin.master.item.index') }}"
                                    class="{{ str_contains($page_url, 'admin/master/item') ? 'nav-link active' : 'nav-link' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Items</p>
                                </a>
                            </li>

                            

                            <li class="nav-item">
                                <a href="{{ route('admin.master.production-goods.index') }}"
                                    class="{{ (str_contains($page_url, 'admin/master/product/') || str_contains($page_url, 'admin/master/production-goods-item/')) ? 'nav-link active' : 'nav-link' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Products</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.master.vendor.index') }}"
                                    class="{{ str_contains($page_url, 'admin/master/vendors') ? 'nav-link active' : 'nav-link' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Vendors</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.master.customer.index') }}"
                                    class="{{ str_contains($page_url, 'admin/master/customers') ? 'nav-link active' : 'nav-link' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Customers</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.master.fabric_warehouse.index') }}"
                                    class="{{ request()->path() === 'admin/master/fabric-warehouse/index' ? 'nav-link active' : 'nav-link' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Fabric Warehouse</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.master.stage_unit.index') }}"
                                    class="{{ request()->path() === 'admin/master/stage-unit/index' ? 'nav-link active' : 'nav-link' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Warehouse Units</p>
                                </a>
                            </li>
                            <!-- <li class="nav-item">
                                <a href="{{ route('admin.master.warehouse.index') }}"
                                    class="{{ request()->path() === 'admin/master/warehouse/index' ? 'nav-link active' : 'nav-link' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Warehouse</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.master.warehouse-blocks.index') }}"
                                    class="{{ request()->path() === 'admin/master/warehouse-blocks/index' ? 'nav-link active' : 'nav-link' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Warehouse Racks</p>
                                </a>
                            </li> -->
                        </ul>
                    </li>

                   

                    <li class="{{ str_contains($page_url, 'admin/reports') ? 'nav-item menu-open' : 'nav-item' }}" style="display:none;">
                        <a href="#"
                            class="{{ str_contains($page_url, 'admin/reports') ? 'nav-link active' : 'nav-link' }}">
                            <i class="nav-icon fa fa-print" aria-hidden="true"></i>
                            <p>
                                Reports
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.purchaseOrder') }}"
                                    class="{{ str_contains(strtolower($page_url), 'admin/reports/purchase-order')  ? 'nav-link active' : 'nav-link' }}"
                                    >
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Fabric Purchase Order</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.itemPurchaseOrder') }}"
                                    class="{{ str_contains(strtolower($page_url), 'admin/reports/item-purchase-order')  ? 'nav-link active' : 'nav-link' }}"
                                    >
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Item Purchase Order</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.itemStockSku') }}"
                                    class="{{ (str_contains(strtolower($page_url), 'admin/reports/item-stock-sku') || str_contains(strtolower($page_url), 'admin/reports/item-stock-details')) ? 'nav-link active' : 'nav-link' }}"
                                    >
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Item Stock</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.itemReceipt') }}"
                                    class="{{ str_contains(strtolower($page_url), 'admin/reports/item-receipt') ? 'nav-link active' : 'nav-link' }}"
                                    >
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Item Receipt</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.fabricReceipt') }}"
                                    class="{{ str_contains(strtolower($page_url), 'admin/reports/fabric-receipt') ? 'nav-link active' : 'nav-link' }}"
                                    >
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Fabric Receipt</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.fabricStockSku') }}"
                                    class="{{ (str_contains(strtolower($page_url), 'admin/reports/fabric-stock-sku') || str_contains(strtolower($page_url), 'admin/reports/fabric-stock-details')) ? 'nav-link active' : 'nav-link' }}"
                                    >
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Fabric Stock</p>
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.production') }}"
                                    class="{{ str_contains(strtolower($page_url), 'admin/reports/production') ? 'nav-link active' : 'nav-link' }}"
                                    >
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Production</p>
                                </a>
                            </li>
                            
                            @foreach($stage_data as $stage)
                                
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.stages',['stage_id' => $stage->id]) }}"
                                    class="{{ str_contains(strtolower($page_url), 'admin/reports') &&  (request('stage_id') == $stage->id)  ? 'nav-link active' : 'nav-link' }}"
                                    >
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{$stage->name}}</p>
                                </a>
                            </li>

                            @endforeach
                        </ul>
                    </li>
                    <!-- Logout -->
                    <li class="nav-item">
                        <a href="{{ route('admin.logout') }}" class="nav-link" style="position:static;">
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
