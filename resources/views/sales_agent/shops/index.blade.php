@extends('sales_agent.layouts.app', ['title' => 'My Shops'])

@section('content')
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="font-weight-bold h4 mb-0">My Shops</h2>
            <a href="{{ route('agent.shops.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fas fa-plus mr-1"></i> Add New
            </a>
        </div>

        <div class="mb-4">
            <form action="{{ route('agent.shops.index') }}" method="GET">
                <div class="input-group bg-white rounded-pill shadow-sm overflow-hidden p-1">
                    <input type="text" name="search" class="form-control border-0 px-3"
                        placeholder="Search shop name or phone..." value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary rounded-pill px-4" type="submit">
                            <i class="fas fa-search mr-1"></i> Search
                        </button>
                    </div>
                </div>
                @if(request('search'))
                    <div class="mt-2 text-center">
                        <a href="{{ route('agent.shops.index') }}" class="text-muted small">
                            <i class="fas fa-times-circle mr-1"></i> Clear search results
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <div class="row">
            @forelse($shops as $shop)
                <div class="col-12 mb-3">
                    <div class="app-card">
                        <div class="d-flex align-items-start">
                            <div class="bg-light p-3 rounded-circle mr-3">
                                <i class="fas fa-store text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="font-weight-bold mb-1">{{ $shop->name }}</h6>
                                <p class="text-muted small mb-2"><i class="fas fa-phone-alt mr-1"></i> {{ $shop->phone }}</p>
                                <p class="text-secondary small mb-2"><i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ Str::limit($shop->address, 50) }}</p>

                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <a href="{{ route('agent.orders.create', ['shop_id' => $shop->id]) }}"
                                class="btn btn-primary btn-sm rounded-lg px-3 mb-2 mb-sm-0">
                                <i class="fas fa-shopping-cart mr-1"></i> Place Order
                            </a>

                            <div class="d-flex">
                                <a href="{{ route('agent.shops.edit', $shop->id) }}"
                                    class="btn btn-outline-secondary btn-sm mr-2">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('agent.shops.toggle-status', $shop->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-sm {{ $shop->status ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                        onclick="return confirm('Are you sure you want to {{ $shop->status ? 'deactivate' : 'activate' }} this shop?')">
                                        <i class="fas {{ $shop->status ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-store-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">You haven't added any shops yet.</p>
                    <a href="{{ route('agent.shops.create') }}" class="btn btn-primary rounded-pill">Add Your First Shop</a>
                </div>
            @endforelse
        </div>
    </div>
@endsection