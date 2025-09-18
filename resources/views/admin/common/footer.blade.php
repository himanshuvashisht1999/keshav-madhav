<?php
	$general_setting = App\Models\GeneralSettings::where('status',1)->first();
?>
<footer class="main-footer">
	<strong>{{$general_setting->copy_right}}</strong>
	All rights reserved.
	<div class="float-right d-none d-sm-inline-block">
	<b>Version</b> 1.0.0
	</div>
</footer>