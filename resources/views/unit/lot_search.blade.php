@extends('layouts.unit')

@section('title', 'Search Lot')

@section('content')
<div class="container-fluid" style="padding-top: 20px;">
    
    @if(session('error'))
        <div class="alert alert-danger" style="border-radius: 12px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card" style="border-radius: 16px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <div class="card-body" style="padding: 30px 20px; text-align: center;">
            
            <div style="width: 60px; height: 60px; background: #e0e7ff; color: #4f46e5; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 20px;">
                <i class="fas fa-search"></i>
            </div>
            
            <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 10px;">Track Lot</h4>
            <p style="color: #64748b; font-size: 14px; margin-bottom: 24px;">Enter a lot number below to view full details including cutting info, fabric used, slips uploaded, and progress.</p>
            
            <form action="{{ route('unit.lot.details') }}" method="GET">
                <div class="form-group" style="margin-bottom: 20px;">
                    <input type="text" name="lot_no" class="form-control form-control-lg" placeholder="e.g. LOT-12345" required style="border-radius: 12px; border: 2px solid #e2e8f0; font-size: 16px; padding: 12px 20px; text-align: center;">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg" style="border-radius: 12px; font-weight: 600; padding: 12px;">
                    Search Lot Details
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
