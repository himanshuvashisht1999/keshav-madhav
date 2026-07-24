<?php
require 'vendor/autoload.php';
\ = require_once 'bootstrap/app.php';
\->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\ = app(App\Services\Admin\ReportService::class);
echo json_encode(\->lotDetails('21385') != null);

