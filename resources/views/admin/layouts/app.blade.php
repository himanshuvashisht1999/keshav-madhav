<!DOCTYPE html>
<html lang="en">


<!-- Mirrored from appstack.bootlab.io/dashboard-saas.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 15 Dec 2021 04:27:46 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->
<head>
    @include('admin.common.meta')
</head>
<body class="hold-transition sidebar-mini">
	<div class="wrapper">
		@include('admin.common.header')
		@include('admin.common.sidebar')
		
		@yield('content')
		@include('admin.common.footer')

	</div>
	@include('admin.common.footer-js')
</body>


</html>

