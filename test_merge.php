<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$request = Illuminate\Http\Request::create('/test', 'GET', ['a' => '1']);
$request->merge(['is_pagination' => true]);
print_r($request->query());
