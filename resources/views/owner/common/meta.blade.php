{{-- Owner panel <head> contents: only what the owner pages use (Bootstrap via AdminLTE, Font Awesome, select2, admin style.css). --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
@if(!empty($general_setting->fav_icon))
    <link rel="icon" type="image/png" href="{{ $general_setting->fav_icon }}">
@endif
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('admin_assets/plugins/fontawesome-free/css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin_assets/dist/css/adminlte.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin_assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin_assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin_assets/style.css') }}?v={{ @filemtime(public_path('admin_assets/style.css')) }}">
<script src="{{ asset('admin_assets/plugins/jquery/jquery.min.js') }}"></script>
