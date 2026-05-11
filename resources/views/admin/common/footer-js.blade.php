<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{asset('admin_assets/plugins/jquery/jquery.min.js')}}"></script>
<!-- Bootstrap -->
<script src="{{asset('admin_assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- AdminLTE -->
<script src="{{asset('admin_assets/dist/js/adminlte.js')}}"></script>

<!-- OPTIONAL SCRIPTS -->
<script src="{{asset('admin_assets/plugins/chart.js/Chart.min.js')}}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{asset('admin_assets/dist/js/demo.js')}}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="{{asset('admin_assets/dist/js/pages/dashboard3.js')}}"></script>

<script src="{{asset('admin_assets/plugins/select2/js/select2.full.min.js')}}"></script>
<!-- Bootstrap4 Duallistbox -->
<script src="{{asset('admin_assets/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js')}}"></script>
<!-- InputMask -->
<script src="{{asset('admin_assets/plugins/moment/moment.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/inputmask/jquery.inputmask.min.js')}}"></script>
<!-- date-range-picker -->
<script src="{{asset('admin_assets/plugins/daterangepicker/daterangepicker.js')}}"></script>
<!-- bootstrap color picker -->
<script src="{{asset('admin_assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')}}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{asset('admin_assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js')}}"></script>
<!-- Bootstrap Switch -->
<script src="{{asset('admin_assets/plugins/bootstrap-switch/js/bootstrap-switch.min.js')}}"></script>
<!-- BS-Stepper -->
<script src="{{asset('admin_assets/plugins/bs-stepper/js/bs-stepper.min.js')}}"></script>
<!-- dropzonejs -->
<script src="{{asset('admin_assets/plugins/dropzone/min/dropzone.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>

<script src="{{asset('admin_assets/plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/jszip/jszip.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/pdfmake/pdfmake.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/pdfmake/vfs_fonts.js')}}"></script>
<script src="{{asset('admin_assets/plugins/datatables-buttons/js/buttons.html5.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/datatables-buttons/js/buttons.print.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/datatables-buttons/js/buttons.colVis.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/sweetalert2/sweetalert2.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/toastr/toastr.min.js')}}"></script>
<script src="{{asset('admin_assets/plugins/summernote/summernote-bs4.min.js')}}"></script>

<script>
    $(document).ready(function(){

      
        Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
        
        var success_message = "{{( Session::has( 'success' )) ? Session::get( 'success' ) : 0}}";
        var error_message = "{{( Session::has( 'error' )) ? Session::get( 'error' ) : 0}}";
        if(success_message != 0){
          Toast.fire({
          icon: 'success',
          title: success_message
          });
        }
        if(error_message != 0){
          Toast.fire({
          icon: 'error',
          title: error_message
          });
        }
        
      
    });
    $(function () {
      
      //Initialize Select2 Elements
      function initSelect2($el, extraOptions) {
        // If inside a modal, render dropdown inside modal (fix z-index/position)
        var $dropdownParent = $el.closest('.modal');
        if ($dropdownParent.length === 0) {
          // Otherwise keep dropdown within the immediate form-group/column to avoid body positioning issues
          $dropdownParent = $el.parent();
        }

        var baseOptions = {
          width: '100%',
          dropdownAutoWidth: true,
          dropdownParent: $dropdownParent
        };

        $el.select2($.extend(baseOptions, extraOptions || {}));
      }

      $('.select2').each(function () {
        initSelect2($(this));
      });
      
      //Initialize Select2 Elements
      $('.select2bs4').each(function () {
        initSelect2($(this), { theme: 'bootstrap4' });
      });
      
      // Prevent double form submission across admin/owner panels
      $(document).on('submit', 'form', function (e) {
        var $form = $(this);

        // Allow opt-out with class if needed
        if ($form.hasClass('allow-multiple-submit')) {
          return;
        }

        if ($form.data('submitted') === true) {
          e.preventDefault();
          return false;
        }

        $form.data('submitted', true);
        $form.find('button[type="submit"], input[type="submit"]').prop('disabled', true);
      });
    })
    
  </script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    flatpickr(".datetime-picker", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        time_24hr: true,
        defaultDate: new Date(),
        // minTime: "09:00",
        // maxTime: "17:00"
    });
});
</script>