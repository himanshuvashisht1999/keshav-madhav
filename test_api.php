<?php
$service = app(\App\Services\Admin\OrderDigitalizationService::class);
$details = $service->getLotDetailsForHandSlip('671', 1, 1);
echo json_encode($details);
