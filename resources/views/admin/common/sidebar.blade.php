<?php
$page_url = $_SERVER['REQUEST_URI'];
$general_setting = App\Models\GeneralSettings::where('status', 1)->first();
$stage_data = App\Models\MasterProductStage::orderBy('status', 'desc')->get();
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4 km-sidebar">
    <!-- Brand Logo -->
    <a href="{{ route('admin.user.profileEdit') }}" class="brand-link">
        <img src="{{ $general_setting->logo }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">SNAPKID</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel pb-3 mb-3 d-flex">
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
                    @can('manage-purchase-order')
                        <li class="nav-item">
                            <a href="{{ route('admin.purchase_order.index') }}"
                                class="{{ str_contains($page_url, 'admin/purchase-order') ? 'nav-link active' : 'nav-link' }} border_class">
                                <i class="nav-icon fas fa-cube"></i>
                                <p>
                                    FABRIC POs
                                </p>
                            </a>
                        </li>
                    @endcan

                    @can('manage-shipment')
                        <li class="nav-item">
                            <a href="{{ route('admin.fabric_receipt.index') }}"
                                class="{{ str_contains($page_url, 'admin/fabric-receipt') ? 'nav-link active' : 'nav-link' }} border_class"
                                style="position:static;">
                                <i class="nav-icon fas fa-receipt"></i>
                                <p>FABRIC SHIPMENT</p>
                            </a>
                        </li>
                    @endcan

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
                    <!-- <li class="nav-item">
                        <a href="{{ route('admin.sales_order.create') }}"
                            class="{{ str_contains($page_url, 'admin/sales-order') ? 'nav-link active' : 'nav-link' }} border_class"
                            style="position:static;">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Sales Order</p>
                        </a>
                    </li> -->
                    @can('manage-sales-order')
                        <li class="nav-item">
                            <a href="{{ route('admin.product_order.indexOrder') }}"
                                class="{{ str_contains($page_url, 'admin/production-order') && !str_contains($page_url, 'po-list') ? 'nav-link active' : 'nav-link' }} border_class"
                                style="position:static;">
                                <i class="nav-icon fas fa-industry"></i>
                                <p>CORPORATE ORDER</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.product_order.poList') }}"
                                class="{{ str_contains($page_url, 'admin/production-order/po-list') ? 'nav-link active' : 'nav-link' }} border_class"
                                style="position:static;">
                                <i class="nav-icon fas fa-list-alt"></i>
                                <p>PRODUCTION PO LIST</p>
                            </a>
                        </li>
                    @endcan
                    <!-- <li class="nav-item">
                        <a href="{{ route('admin.order_digitalization.cutting-master') }}"
                            class="{{ str_contains($page_url, 'admin/order_digitalization/cutting-master') ? 'nav-link active' : 'nav-link' }} border_class"
                            style="position:static;">
                            <i class="fas fa-file-signature"></i>
                            
                            <p>Cutting Master Slip</p>
                        </a>
                    </li> -->
                    @can('manage-uploaded-slips')
                        <li class="nav-item">
                            <a href="{{ route('admin.uploaded-slips.index') }}"
                                class="{{ str_contains($page_url, 'admin/uploaded-slips') || str_contains($page_url, 'admin/packing/process') || str_contains($page_url, 'admin/order_digitalization/create-slips-production') || str_contains($page_url, 'admin/order_digitalization/cutting-master') ? 'nav-link active' : 'nav-link' }} border_class"
                                style="position:static;">
                                <i class="fas fa-file-upload"></i>
                                <p>UPLOADED SLIPS</p>
                            </a>
                        </li>
                    @endcan
                    @can('manage-time-allocation')
                        <li class="nav-item">
                            <a href="{{ route('admin.time_allocation.index') }}"
                                class="{{ str_contains($page_url, 'admin/time-allocation') ? 'nav-link active' : 'nav-link' }} border_class"
                                style="position:static;">
                                <i class="fas fa-clock"></i>
                                <p>TIME ALLOCATION</p>
                            </a>
                        </li>
                    @endcan
                    <!-- <li class="nav-item">
                        <a href="{{ route('admin.order_digitalization.create-slips-production') }}"
                            class="{{ str_contains($page_url, 'admin/order_digitalization/create-slips-production') ? 'nav-link active' : 'nav-link' }} border_class"
                            style="position:static;">
                            <i class="fas fa-file-signature"></i>
                            
                            <p>HAND SLIP DIGITALIZATION</p>
                        </a>
                    </li> -->

                    <!-- <li class="nav-item">
                        <a href="{{ route('admin.packing-carton.index') }}"
                            class="{{ str_contains($page_url, 'admin/packing-carton') ? 'nav-link active' : 'nav-link' }} border_class"
                            style="position:static;">
                            <i class="fas fa-dolly"></i>
                            <p>PACKING IN CARTON</p>
                        </a>
                    </li> -->
                    @can('manage-packing-module')
                        <li class="nav-item">
                            <a href="{{ route('admin.packing.index') }}"
                                class="{{ str_contains($page_url, 'admin/packing') ? 'nav-link active' : 'nav-link' }} border_class"
                                style="position:static;">
                                <i class="nav-icon fas fa-box-open"></i>
                                <p>PACKING MODULE</p>
                            </a>
                        </li>
                    @endcan

                    @can('manage-inventory')
                        <li
                            class="nav-item {{ (request()->is('admin/inventory*') || request()->is('admin/agent-orders*') || request()->is('admin/direct-sales*')) ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ (request()->is('admin/inventory*') || request()->is('admin/agent-orders*') || request()->is('admin/direct-sales*')) ? 'active' : '' }} border_class">
                                <i class="nav-icon fas fa-warehouse"></i>
                                <p>
                                    INVENTORY
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.index') }}"
                                        class="{{ str_contains($page_url, 'admin/inventory/index') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Browse Inventory</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.sample-product.index') }}"
                                        class="{{ str_contains($page_url, 'admin/inventory/sample-product') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Sample Product</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.fair-product.index') }}"
                                        class="{{ str_contains($page_url, 'admin/inventory/fair-product') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Sample Set</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.agent-orders.index') }}"
                                        class="{{ str_contains($page_url, 'admin/agent-orders') && !str_contains($page_url, 'admin/agent-orders/dispatches') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Sales Orders</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.agent-orders.create') }}"
                                        class="{{ str_contains($page_url, 'admin/agent-orders/create') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Create Order</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.agent-orders.dispatches.index') }}"
                                        class="{{ str_contains($page_url, 'admin/agent-orders/dispatches') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Sales (Dispatches)</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.agent-orders.returns.index') }}"
                                        class="{{ str_contains($page_url, 'admin/agent-orders/returns') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Sales Returns</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.warehouse_stock') }}"
                                        class="{{ str_contains($page_url, 'admin/inventory/warehouse-stock') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Warehouse Stock</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.create') }}"
                                        class="{{ str_contains($page_url, 'admin/inventory/create') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Stock Consume</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.inbound_history.index') }}"
                                        class="{{ str_contains($page_url, 'admin/inventory/inbound-history') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon text-success"></i>
                                        <p>Inbound History</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.purchase_history.index') }}"
                                        class="{{ str_contains($page_url, 'admin/inventory/purchase-history') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Purchase</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.fabric_transfer.index') }}"
                                        class="{{ str_contains($page_url, 'admin/inventory/fabric-transfer') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon text-warning"></i>
                                        <p>Fabric Transfer</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.stock_transfer.index') }}"
                                        class="{{ str_contains($page_url, 'admin/inventory/stock-transfer') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Stock Transfer</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.stock_disposal.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.inventory.stock_disposal.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-danger"></i>
                                        <p>Stock Disposal</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.barcode-generator.index') }}"
                                        class="{{ str_contains($page_url, 'admin/inventory/barcode-generator') ? 'nav-link active' : 'nav-link' }}"
                                        style="position:static;">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Barcode Print</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.outflow.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.inventory.outflow.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Unit Movement & Loss</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.inventory.attribute-history.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.inventory.attribute-history.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>History</p>
                                    </a>
                                </li>

                            </ul>
                        </li>
                    @endcan

                    @can('manage-order-dispatch')
                        <li class="nav-item">
                            <a href="{{ route('admin.order-dispatch.index') }}"
                                class="{{ str_contains($page_url, 'admin/order-dispatch') ? 'nav-link active' : 'nav-link' }} border_class"
                                style="position:static;">
                                <i class="fas fa-truck"></i>
                                <p>CORPORATE ORDER DISPATCH</p>
                            </a>
                        </li>
                    @endcan

                    {{-- <li class="nav-item">
                        <a href="{{ route('admin.order_digitalization.create-slips-production') }}"
                            class="{{ str_contains($page_url, 'admin.order_digitalization.create-slips-production') ? 'nav-link active' : 'nav-link' }} border_class"
                            style="position:static;">
                            <i class="nav-icon fas fa-industry"></i>
                            <p>Hand Slip Digitalization</p>
                        </a>
                    </li> --}}
                    @can('manage-payment')
                        <li class="{{ (str_contains($page_url, 'admin/payment')) ? 'nav-item menu-open' : 'nav-item' }}">
                            <a href="#"
                                class="{{ (str_contains($page_url, 'admin/payment')) ? 'nav-link active' : 'nav-link' }} border_class">
                                <i class="nav-icon fas fa-money-bill"></i>
                                <p>
                                    PAYMENT
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <!-- <li class="nav-item">
                                                                                                                <a href="{{ route('admin.payment.dashboard.index') }}"
                                                                                                                    class="{{ str_contains($page_url, 'admin/payment/dashboard') ? 'nav-link active' : 'nav-link' }}">
                                                                                                                    <i class="fas fa-chart-pie nav-icon"></i>
                                                                                                                    <p>Analytics Dashboard</p>
                                                                                                                </a>
                                                                                                            </li> -->
                                <li class="nav-item {{ (str_contains($page_url, 'payment/master')) ? 'menu-open' : '' }}">
                                    <a href="#"
                                        class="nav-link {{ (str_contains($page_url, 'payment/master')) ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            Master
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.cash_payment.index') }}"
                                                class="{{ str_contains($page_url, 'payment/master/cash-payment') ? 'active' : '' }} nav-link">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Cash Payment Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.bank_account.index') }}"
                                                class="{{ str_contains($page_url, 'payment/master/bank-account') ? 'active' : '' }} nav-link">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Bank Account Master</p>
                                            </a>
                                        </li>
                                        <!-- <li class="nav-item">
                                                                                                                                <a href="{{ route('admin.payment.master.payment_type.index') }}"
                                                                                                                                    class="nav-link {{ str_contains($page_url, 'payment/master/payment-type') ? 'active' : '' }}">
                                                                                                                                    <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                                                                                                    <p>Payment Type Master</p>
                                                                                                                                </a>
                                                                                                                            </li> -->
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.committee.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/committee') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Committee Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.tax.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/tax') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Tax Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.interest.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/interest') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Interest Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.tour_expense.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/tour_expense') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Tour Expense Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.fare_expense.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/fare_expense') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Fare Expense Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.sk_expense.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/sk_expense') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>SK Expense Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.agent_payment.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/agent_payment') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Agent Payment Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.washing_master.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/washing_master') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Washing Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.cutting_payment.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/cutting_payment') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Cutting Payment Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.contractor.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/contractor') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Contractor Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.consumable_good.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/consumable_good') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Consumable Good Master</p>
                                            </a>
                                        </li>
                                        <!-- <li class="nav-item">
                                                                                                    <a href="{{ route('admin.payment.master.company_capital.index') }}"
                                                                                                        class="{{ str_contains($page_url, 'payment/master/company_capital') ? 'active' : '' }} nav-link">
                                                                                                        <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                                                                        <p>Company Capital</p>
                                                                                                    </a>
                                                                                                </li> -->
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.general_expense.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/general_expense') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>General Expense Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.electricity_expense.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/electricity_expense') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Electricity Expense Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.rent.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/rent') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Rent Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.telephone_expense.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/telephone_expense') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Telephone Expense Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.commission.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/commission') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Commission Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.hulayati.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/hulayati') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Hulayati Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.machinery.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/machinery') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Machinery Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.loan.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/loan') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Loan Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.factory_head.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/factory_head') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Factory Head Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.discount.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/discount') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Discount Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.salary.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/salary') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Salary Master</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.master.capital.index') }}"
                                                class="nav-link {{ str_contains($page_url, 'payment/master/capital') ? 'active' : '' }}">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Capital Master</p>
                                            </a>
                                        </li>
                                        <!-- <li class="nav-item">
                                                                                                                            <a href="{{ route('admin.payment.master.adjustment_master.index') }}"
                                                                                                                                class="nav-link {{ str_contains($page_url, 'payment/master/adjustment_master') ? 'active' : '' }}">
                                                                                                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                                                                                                <p>Adjustment Master</p>
                                                                                                                            </a>
                                                                                                                        </li> -->
                                    </ul>
                                </li>
                                <li class="nav-item {{ (str_contains($page_url, 'payment/voucher')) ? 'menu-open' : '' }}">
                                    <a href="#"
                                        class="nav-link {{ (str_contains($page_url, 'payment/voucher')) ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-info"></i>
                                        <p>
                                            Voucher
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.voucher.consumable.index') }}"
                                                class="{{ str_contains($page_url, 'payment/voucher/consumable') ? 'active' : '' }} nav-link">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Consumable Voucher</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.voucher.contractor.index') }}"
                                                class="{{ str_contains($page_url, 'payment/voucher/contractor') ? 'active' : '' }} nav-link">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Contractor Voucher</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.payment.voucher.washing.index') }}"
                                                class="{{ str_contains($page_url, 'payment/voucher/washing') ? 'active' : '' }} nav-link">
                                                <i class="fas fa-circle nav-icon" style="font-size: 12px;"></i>
                                                <p>Washing Voucher</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.payment.adjustment.index') }}"
                                        class="nav-link {{ str_contains($page_url, 'payment/adjustment') ? 'active' : '' }}">
                                        <i class="fas fa-balance-scale nav-icon"></i>
                                        <p>Payment Adjust</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.payment.journal-voucher.index') }}"
                                        class="nav-link {{ str_contains($page_url, 'payment/journal-voucher') ? 'active' : '' }}">
                                        <i class="fas fa-file-invoice-dollar nav-icon"></i>
                                        <p>Journal Voucher</p>
                                    </a>
                                </li>
                                <!-- <li class="nav-item">
                                            <a href="{{ route('admin.payment.pending.index') }}"
                                                class="{{ str_contains($page_url, 'admin/payment/pending') ? 'nav-link active' : 'nav-link' }}">
                                                <i class="fas fa-clock nav-icon"></i>
                                                <p>Pending Payments</p>
                                            </a>
                                        </li> -->
                                <!-- <li class="nav-item">
                                                                                                                                <a href="{{ route('admin.payment.fabric-shipment.create') }}"
                                                                                                                                    class="{{ str_contains($page_url, 'admin/payment/fabric-shipment') ? 'nav-link active' : 'nav-link' }}">
                                                                                                                                    <i class="far fa-circle nav-icon"></i>
                                                                                                                                    <p>Fabric Shipment</p>
                                                                                                                                </a>
                                                                                                                            </li>
                                                                                                                            <li class="nav-item">
                                                                                                                                <a href="{{ route('admin.payment.corporate-order.index') }}"
                                                                                                                                    class="{{ str_contains($page_url, 'admin/payment/corporate-order') ? 'nav-link active' : 'nav-link' }}">
                                                                                                                                    <i class="far fa-circle nav-icon"></i>
                                                                                                                                    <p>Corporate Order</p>
                                                                                                                                </a>
                                                                                                                            </li>
                                                                                                                            <li class="nav-item">
                                                                                                                                <a href="{{ route('admin.payment.agent-order.create') }}"
                                                                                                                                    class="{{ str_contains($page_url, 'admin/payment/agent-order') ? 'nav-link active' : 'nav-link' }}">
                                                                                                                                    <i class="far fa-circle nav-icon"></i>
                                                                                                                                    <p>Agent Order</p>
                                                                                                                                </a>
                                                                                                                            </li>
                                                                                                                            <li class="nav-item">
                                                                                                                                <a href="{{ route('admin.payment.salary.create') }}"
                                                                                                                                    class="{{ str_contains($page_url, 'admin/payment/salary') ? 'nav-link active' : 'nav-link' }}">
                                                                                                                                    <i class="far fa-circle nav-icon"></i>
                                                                                                                                    <p>Salary Payment</p>
                                                                                                                                </a>
                                                                                                                            </li>
                                                                                                                            <li class="nav-item">
                                                                                                                                <a href="{{ route('admin.payment.other.create') }}"
                                                                                                                                    class="{{ str_contains($page_url, 'admin/payment/other') ? 'nav-link active' : 'nav-link' }}">
                                                                                                                                    <i class="far fa-circle nav-icon"></i>
                                                                                                                                    <p>Other Payment</p>
                                                                                                                                </a>
                                                                                                                            </li>
                                                                                                                        <li class="nav-item">
                                                                                                                            <a href="{{ route('admin.payment.history.index') }}"
                                                                                                                                class="{{ str_contains($page_url, 'admin/payment/history') ? 'nav-link active' : 'nav-link' }}">
                                                                                                                                <i class="far fa-circle nav-icon"></i>
                                                                                                                                <p>Payment History</p>
                                                                                                                            </a>
                                                                                                                        </li> -->
                            </ul>
                        </li>
                    @endcan

                    @can('manage-reports')
                        <li class="{{ (str_contains($page_url, 'admin/report')) ? 'nav-item menu-open' : 'nav-item' }}">
                            <a href="#"
                                class="{{ (str_contains($page_url, 'admin/report')) ? 'nav-link active' : 'nav-link' }} border_class">
                                <i class="nav-icon fa fa-print" aria-hidden="true"></i>
                                <p>
                                    REPORTS
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">

                                <li class="nav-item">
                                    <a href="{{ route('admin.report.purchase_order') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/report/purchase-order') && !str_contains(strtolower($page_url), 'fabric-wise') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Purchase Order</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.report.purchase_order_fabric_wise') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/report/purchase-order-fabric-wise') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Purchase Order (Fabric Wise)</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.report.stock') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/report/stock') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Fabric Stock</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.report.stock.rolls') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/report/stock-rolls') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Stock Report (Rolls)</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.report.fabric_return') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/report/fabric-return') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Fabric Return</p>
                                    </a>
                                </li>

                                <!-- <li class="nav-item">
                                                                                                                                <a href="{{ route('admin.report.sales-order') }}"
                                                                                                                                    class="{{ str_contains(strtolower($page_url), 'admin/report/sales-order')  ? 'nav-link active' : 'nav-link' }}"
                                                                                                                                    >
                                                                                                                                    <i class="far fa-circle nav-icon"></i>
                                                                                                                                    <p>Sales Order</p>
                                                                                                                                </a>
                                                                                                                            </li>
                                                                                                                            <li class="nav-item">
                                                                                                                                <a href="{{ route('admin.report.orderTrackingSystem') }}"
                                                                                                                                    class="{{ str_contains(strtolower($page_url), 'admin/report/order-tracking-system')  ? 'nav-link active' : 'nav-link' }}"
                                                                                                                                    >
                                                                                                                                    <i class="far fa-circle nav-icon"></i>
                                                                                                                                    <p>Order Tracking</p>
                                                                                                                                </a>
                                                                                                                            </li>
                                                                                                                            <li class="nav-item">
                                                                                                                                <a href="{{ route('admin.report.dispatch-order') }}"
                                                                                                                                    class="{{ str_contains(strtolower($page_url), 'admin/report/dispatch-order')  ? 'nav-link active' : 'nav-link' }}"
                                                                                                                                    >
                                                                                                                                    <i class="far fa-circle nav-icon"></i>
                                                                                                                                    <p>Dispatch Order</p>
                                                                                                                                </a>
                                                                                                                            </li> -->

                                <li class="nav-item">
                                    <a href="{{ route('admin.report.order-summary.index') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/report/order-summary') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Order Summary Reports</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.report.lots') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/report/lots') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Lots Reports</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.reports.unit-assignments') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/reports/unit-assignments') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Unit Assignments</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.reports.stock-pending') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/reports/stock-pending') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Stage Wise Pending Stock</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.reports.outflows') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/reports/outflows') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Outflows & Adjustments</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.reports.design-wip') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/reports/design-wip') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Design WIP</p>
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a href="{{ route('admin.report.wip-complete') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/report/wip-complete') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>WIP Complete Report</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.report.product-customer-count') }}"
                                        class="{{ str_contains(strtolower($page_url), 'product-customer-count') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Product Customer Count</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.report.agent-ledger.index') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/report/agent-ledger') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Agent Ledger Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.reports.salesManReport') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/reports/sales-man-report') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon text-info"></i>
                                        <p>Sales Man Report</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan

                    @can('manage-ledger')
                        <li class="{{ (str_contains($page_url, 'admin/ledger')) ? 'nav-item menu-open' : 'nav-item' }}">
                            <a href="#"
                                class="{{ (str_contains($page_url, 'admin/ledger')) ? 'nav-link active' : 'nav-link' }} border_class">
                                <i class="nav-icon fas fa-book"></i>
                                <p>
                                    LEDGER
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.ledger.fabric.index') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/ledger/fabric') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Fabric Ledger</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.ledger.production-goods.index') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/ledger/production-goods') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Production Goods Ledger</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.ledger.lot.index') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/ledger/lot') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Lot Ledger</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.ledger.party.index') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/ledger/party') && !str_contains(strtolower($page_url), 'type_id=sales_agent') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Party Ledger</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.ledger.bank-cash-ledger.index') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/ledger/bank-cash-ledger') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Bank & Cash Ledger</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.ledger.party.index', ['type_id' => 'sales_agent']) }}"
                                        class="{{ str_contains(strtolower($page_url), 'type_id=sales_agent') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon text-purple"></i>
                                        <p>Sales Agent Ledger</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.ledger.sales.index') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/ledger/sales') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Sales Ledger</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.ledger.purchase.index') }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/ledger/purchase') ? 'nav-link active' : 'nav-link' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Purchase Ledger</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                    {{-- <li
                        class="{{ (str_contains($page_url, 'admin/order_digitalization') || str_contains($page_url, 'admin/order_digitalization/create-slips-production')) ? 'nav-item menu-open' : 'nav-item' }} ">
                        <a href="#"
                            class="{{ str_contains($page_url, 'admin/order_digitalization') || str_contains($page_url, 'admin/order_digitalization/create-slips-production') ? 'nav-link active' : 'nav-link' }} border_class">
                            <i class="nav-icon fas fa-cube"></i>
                            <p>
                                ORDER DIGITALIZATION
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.order_digitalization.create-slips-production') }}"
                                    class="{{ (str_contains($page_url, 'admin/order_digitalization')) ? 'nav-link active' : 'nav-link' }}"
                                    style="position:static;">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Production Slips Digitalization</p>
                                </a>
                            </li>
                        </ul>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.order_digitalization.create-rolls-assign') }}"
                                    class="{{ (str_contains($page_url, 'admin/order_digitalization') ) ? 'nav-link active' : 'nav-link' }}"
                                    style="position:static;">
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



                    <li class="{{ str_contains($page_url, 'admin/order-stages') ? 'nav-item menu-open' : 'nav-item' }}"
                        style="display:none;">
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
                                    <a href="{{ route('admin.order-stages.index', ['stage_id' => $stage->id]) }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/order-stages') && (request('stage_id') == $stage->id) ? 'nav-link active' : 'nav-link' }}">
                                        <!-- <i class="nav-icon fas fa-store"></i> -->
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{$stage->name}}</p>
                                    </a>
                                </li>

                            @endforeach
                        </ul>
                    </li>

                    <li class="{{ (str_contains($page_url, 'admin/warehouse') || str_contains($page_url, 'admin/warehouse')) ? 'nav-item menu-open' : 'nav-item' }}"
                        style="display:none;">
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

                    @can('manage-masters')
                                        <!-- Master Settings (Dropdown) -->
                                        <li class="{{ str_contains($page_url, 'admin/master') ? 'nav-item menu-open' : 'nav-item' }}">
                                            <a href="#"
                                                class="{{ str_contains($page_url, 'admin/master') ? 'nav-link active' : 'nav-link' }} border_class">
                                                <i class="nav-icon fas fa-cogs"></i>
                                                <p>
                                                    MASTERS
                                                    <i class="right fas fa-angle-left"></i>
                                                </p>
                                            </a>

                                            <ul class="nav nav-treeview">
                                                {{-- ================= FABRIC MASTER ================= --}}
                                                <li class="{{ (
                            str_contains($page_url, 'admin/master/fabric_dye') ||
                            str_contains($page_url, 'admin/master/fabric-composition') ||
                            str_contains($page_url, 'admin/master/fabric_gsm') ||
                            str_contains($page_url, 'admin/master/fabric_weave') ||
                            str_contains($page_url, 'admin/master/fabric_width') ||
                            str_contains($page_url, 'admin/master/fabric_unit') ||
                            ($page_url === '/admin/master/fabric' || str_starts_with($page_url, '/admin/master/fabric/'))
                        ) ? 'nav-item menu-open' : 'nav-item' }}">
                                                    <a href="#" class="{{ (
                            str_contains($page_url, 'admin/master/fabric_dye') ||
                            str_contains($page_url, 'admin/master/fabric-composition') ||
                            str_contains($page_url, 'admin/master/fabric_gsm') ||
                            str_contains($page_url, 'admin/master/fabric_weave') ||
                            str_contains($page_url, 'admin/master/fabric_width') ||
                            str_contains($page_url, 'admin/master/fabric_unit') ||
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
                                                            <a href="{{ route('admin.master.fabric_composition.index') }}"
                                                                class="{{ str_contains($page_url, 'admin/master/fabric-composition') ? 'nav-link active' : 'nav-link' }}">
                                                                <i class="fas fa-circle"></i>
                                                                <p>Composition</p>
                                                            </a>
                                                        </li>

                                                        <li class="nav-item">
                                                            <a href="{{ route('admin.master.fabric.index') }}"
                                                                class="{{ $page_url === '/admin/master/fabric' || str_starts_with($page_url, '/admin/master/fabric/') ? 'nav-link active' : 'nav-link' }}">
                                                                <i class="fas fa-circle"></i>
                                                                <p>Fabric</p>
                                                            </a>
                                                        </li>

                                                        <li class="nav-item">
                                                            <a href="{{ route('admin.master.fabric_unit.index') }}"
                                                                class="{{ str_contains($page_url, 'admin/master/fabric_unit') ? 'nav-link active' : 'nav-link' }}">
                                                                <i class="fas fa-circle"></i>
                                                                <p>Fabric Unit</p>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>

                                                {{-- ================= PRODUCT MASTER ================= --}}
                                                <li class="{{ (
                            str_contains($page_url, 'admin/master/colors') ||
                            str_contains($page_url, 'admin/master/fitting') ||
                            str_contains($page_url, 'admin/master/product-types') ||
                            str_contains($page_url, 'admin/master/size-measurement') ||
                            str_contains($page_url, 'admin/master/size') ||
                            str_contains($page_url, 'admin/master/product-stage') ||
                            str_contains($page_url, 'admin/master/product-sub-stage') ||
                            str_contains($page_url, 'admin/master/design-pattern') ||
                            str_contains($page_url, 'admin/master/product-nature') ||
                            str_contains($page_url, 'admin/master/fabric-type')
                        ) ? 'nav-item menu-open' : 'nav-item' }}">
                                                    <a href="#" class="{{ (
                            str_contains($page_url, 'admin/master/colors') ||
                            str_contains($page_url, 'admin/master/fitting') ||
                            str_contains($page_url, 'admin/master/product-types') ||
                            str_contains($page_url, 'admin/master/size-measurement') ||
                            str_contains($page_url, 'admin/master/size') ||
                            str_contains($page_url, 'admin/master/product-stage') ||
                            str_contains($page_url, 'admin/master/product-sub-stage') ||
                            str_contains($page_url, 'admin/master/design-pattern') ||
                            str_contains($page_url, 'admin/master/product-nature') ||
                            str_contains($page_url, 'admin/master/fabric-type')
                        ) ? 'nav-link active' : 'nav-link' }}">
                                                        <i class="far fa-circle nav-icon"></i>
                                                        <p>
                                                            Product Master
                                                            <i class="right fas fa-angle-left"></i>
                                                        </p>
                                                    </a>

                                                    <ul class="nav nav-treeview">
                                                        <li class="nav-item">
                                                            <a href="{{ route('admin.master.order-remarks.index') }}"
                                                                class="{{ str_contains($page_url, 'admin/master/order-remarks') ? 'nav-link active' : 'nav-link' }}">
                                                                <i class="fas fa-circle"></i>
                                                                <p>Order Remarks</p>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="{{ route('admin.master.colors.index') }}"
                                                                class="{{ str_contains($page_url, 'admin/master/colors') ? 'nav-link active' : 'nav-link' }}">
                                                                <i class="fas fa-circle"></i>
                                                                <p>Color</p>
                                                            </a>
                                                        </li>

                                                        <li class="nav-item">
                                                            <a href="{{ route('admin.master.fitting.index') }}"
                                                                class="{{ str_contains($page_url, 'admin/master/fitting') ? 'nav-link active' : 'nav-link' }}">
                                                                <i class="fas fa-circle"></i>
                                                                <p>Fitting</p>
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
                                                                class="{{ ((str_contains($page_url, 'admin/master/product-stage') && !str_contains($page_url, 'lot-time')) || str_contains($page_url, 'admin/master/product-sub-stage')) ? 'nav-link active' : 'nav-link' }}">
                                                                <i class="fas fa-circle"></i>
                                                                <p>Stages</p>
                                                            </a>
                                                        </li>

                                                        <li class="nav-item">
                                                            <a href="{{ route('admin.master.design-pattern.index') }}"
                                                                class="{{ str_contains($page_url, 'admin/master/design-pattern') ? 'nav-link active' : 'nav-link' }}">
                                                                <i class="fas fa-circle"></i>
                                                                <p>Product Style</p>
                                                            </a>
                                                        </li>

                                                        <li class="nav-item">
                                                            <a href="{{ route('admin.master.product-nature.index') }}"
                                                                class="{{ str_contains($page_url, 'admin/master/product-nature') ? 'nav-link active' : 'nav-link' }}">
                                                                <i class="fas fa-circle"></i>
                                                                <p>Product Nature</p>
                                                            </a>
                                                        </li>

                                                        <li class="nav-item">
                                                            <a href="{{ route('admin.master.fabric-type.index') }}"
                                                                class="{{ str_contains($page_url, 'admin/master/fabric-type') ? 'nav-link active' : 'nav-link' }}">
                                                                <i class="fas fa-circle"></i>
                                                                <p>Fabric Type</p>
                                                            </a>
                                                        </li>

                                                        <li class="nav-item">
                                                            <a href="{{ route('admin.master.series.index') }}"
                                                                class="{{ str_contains($page_url, 'admin/master/series') ? 'nav-link active' : 'nav-link' }}">
                                                                <i class="fas fa-circle"></i>
                                                                <p>Series</p>
                                                            </a>
                                                        </li>

                                                        <li class="nav-item">
                                                            <a href="{{ route('admin.master.brand.index') }}"
                                                                class="{{ str_contains($page_url, 'admin/master/brand') ? 'nav-link active' : 'nav-link' }}">
                                                                <i class="fas fa-circle"></i>
                                                                <p>Brand</p>
                                                            </a>
                                                        </li>
                                                    </ul>
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
                                                    <a href="{{ route('admin.master.sales-agent.index') }}"
                                                        class="{{ str_contains($page_url, 'admin/master/sales-agent') ? 'nav-link active' : 'nav-link' }}">
                                                        <i class="far fa-circle nav-icon"></i>
                                                        <p>Sales Agents</p>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ route('admin.master.sales-man.index') }}"
                                                        class="{{ str_contains($page_url, 'admin/master/sales-man') ? 'nav-link active' : 'nav-link' }}">
                                                        <i class="far fa-circle nav-icon"></i>
                                                        <p>Sales Men</p>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ route('admin.master.purchase-agent.index') }}"
                                                        class="{{ str_contains($page_url, 'admin/master/purchase-agent') ? 'nav-link active' : 'nav-link' }}">
                                                        <i class="far fa-circle nav-icon"></i>
                                                        <p>Purchase Agents</p>
                                                    </a>
                                                </li>
                                                <!-- <li class="nav-item">
                                                                                                                                    <a href="{{ route('admin.master.employees.index') }}"
                                                                                                                                        class="{{ str_contains($page_url, 'admin/master/employees') ? 'nav-link active' : 'nav-link' }}">
                                                                                                                                        <i class="far fa-circle nav-icon"></i>
                                                                                                                                        <p>Employees</p>
                                                                                                                                    </a>
                                                                                                                                </li> -->
                                                <li class="nav-item">
                                                    <a href="{{ route('admin.master.company.index') }}"
                                                        class="{{ str_contains($page_url, 'admin/master/company') ? 'nav-link active' : 'nav-link' }}">
                                                        <i class="far fa-circle nav-icon"></i>
                                                        <p>Company Master</p>
                                                    </a>
                                                </li>


                                                <li class="nav-item">
                                                    <a href="{{ route('admin.master.storeroom.index') }}"
                                                        class="nav-link {{ str_contains($page_url, 'master/storerooms') ? 'active' : '' }}">
                                                        <i class="far fa-circle nav-icon"></i>
                                                        <p>Storeroom Master</p>
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
                                                <li class="nav-item">
                                                    <a href="{{ route('admin.settings.edit') }}"
                                                        class="{{ request()->path() === 'admin/master/settings/edit' ? 'nav-link active' : 'nav-link' }}">
                                                        <i class="far fa-circle nav-icon"></i>
                                                        <p>General Settings</p>
                                                    </a>
                                                </li>

                                                @can('manage-users')
                                                    <li
                                                        class="{{ (str_contains($page_url, 'admin/roles') || str_contains($page_url, 'admin/users')) ? 'nav-item menu-open' : 'nav-item' }}">
                                                        <a href="#"
                                                            class="{{ (str_contains($page_url, 'admin/roles') || str_contains($page_url, 'admin/users')) ? 'nav-link active' : 'nav-link' }}">
                                                            <i class="far fa-circle nav-icon"></i>
                                                            <p>
                                                                User Management
                                                                <i class="right fas fa-angle-left"></i>
                                                            </p>
                                                        </a>
                                                        <ul class="nav nav-treeview">
                                                            <li class="nav-item">
                                                                <a href="{{ route('admin.users.index') }}"
                                                                    class="{{ str_contains($page_url, 'admin/users') ? 'nav-link active' : 'nav-link' }}">
                                                                    <i class="fas fa-circle-notch nav-icon"></i>
                                                                    <p>Users</p>
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ route('admin.roles.index') }}"
                                                                    class="{{ str_contains($page_url, 'admin/roles') ? 'nav-link active' : 'nav-link' }}">
                                                                    <i class="fas fa-circle-notch nav-icon"></i>
                                                                    <p>Roles & Permissions</p>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </li>
                    @endcan



                    <li class="{{ str_contains($page_url, 'admin/reports') ? 'nav-item menu-open' : 'nav-item' }}"
                        style="display:none;">
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
                                    class="{{ str_contains(strtolower($page_url), 'admin/reports/purchase-order') ? 'nav-link active' : 'nav-link' }}">
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Fabric Purchase Order</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.itemPurchaseOrder') }}"
                                    class="{{ str_contains(strtolower($page_url), 'admin/reports/item-purchase-order') ? 'nav-link active' : 'nav-link' }}">
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Item Purchase Order</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.itemStockSku') }}"
                                    class="{{ (str_contains(strtolower($page_url), 'admin/reports/item-stock-sku') || str_contains(strtolower($page_url), 'admin/reports/item-stock-details')) ? 'nav-link active' : 'nav-link' }}">
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Item Stock</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.itemReceipt') }}"
                                    class="{{ str_contains(strtolower($page_url), 'admin/reports/item-receipt') ? 'nav-link active' : 'nav-link' }}">
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Item Receipt</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.fabricReceipt') }}"
                                    class="{{ str_contains(strtolower($page_url), 'admin/reports/fabric-receipt') ? 'nav-link active' : 'nav-link' }}">
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Fabric Receipt</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.fabricStockSku') }}"
                                    class="{{ (str_contains(strtolower($page_url), 'admin/reports/fabric-stock-sku') || str_contains(strtolower($page_url), 'admin/reports/fabric-stock-details')) ? 'nav-link active' : 'nav-link' }}">
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Fabric Stock</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.reports.production') }}"
                                    class="{{ str_contains(strtolower($page_url), 'admin/reports/production') ? 'nav-link active' : 'nav-link' }}">
                                    <!-- <i class="nav-icon fas fa-store"></i> -->
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Production</p>
                                </a>
                            </li>

                            @foreach($stage_data as $stage)

                                <li class="nav-item">
                                    <a href="{{ route('admin.reports.stages', ['stage_id' => $stage->id]) }}"
                                        class="{{ str_contains(strtolower($page_url), 'admin/reports') && (request('stage_id') == $stage->id) ? 'nav-link active' : 'nav-link' }}">
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
                        <a href="{{ route('admin.logout') }}" class="nav-link border_class" style="position:static;">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>LOGOUT</p>
                        </a>
                    </li>

                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </div>
</aside>