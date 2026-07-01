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
        // If inside a modal, render dropdown inside modal-content (fix z-index/position & CSS transform bugs)
        var $dropdownParent = $el.closest('.modal-content');
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
      
      // Prevent double form submission and double clicks across admin/owner panels
      $(document).on('click', 'form button:not(.allow-multiple-submit), form input[type="submit"]:not(.allow-multiple-submit), form input[type="button"]:not(.allow-multiple-submit)', function (e) {
        var $btn = $(this);
        var $form = $btn.closest('form');

        // Allow opt-out with class if needed
        if ($form.hasClass('allow-multiple-submit')) {
          return;
        }

        // HTML5 validation check: if not valid, let browser show errors and don't submit/disable
        if ($form.length && $form[0].checkValidity && !$form[0].checkValidity()) {
            return;
        }

        if ($btn.data('clicked') === true) {
          e.preventDefault();
          e.stopImmediatePropagation();
          return false;
        }

        $btn.data('clicked', true);
        var originalPointerEvents = $btn.css('pointer-events');
        $btn.css('pointer-events', 'none');

        // Defer disabling the button so that standard form post still registers the clicked button name/value
        setTimeout(function() {
          $btn.prop('disabled', true);
        }, 10);

        // Fallback to restore in case form submission is aborted or for AJAX/single-page forms
        setTimeout(function() {
          $btn.data('clicked', false);
          $btn.prop('disabled', false);
          $btn.css('pointer-events', originalPointerEvents || 'auto');
        }, 5000);
      });

      $(document).on('submit', 'form', function (e) {
        var $form = $(this);

        // Allow opt-out with class if needed
        if ($form.hasClass('allow-multiple-submit')) {
          return;
        }

        // HTML5 validation check
        if (this.checkValidity && !this.checkValidity()) {
            return;
        }

        if ($form.data('submitted') === true) {
          e.preventDefault();
          return false;
        }

        $form.data('submitted', true);
        
        var $buttons = $form.find('button, input[type="submit"], input[type="button"]');
        $buttons.css('pointer-events', 'none');

        // Defer disabling the button so we can check if another script prevented submission
        setTimeout(function() {
            if (e.isDefaultPrevented()) {
                $form.data('submitted', false);
                $buttons.css('pointer-events', 'auto');
            } else {
                $buttons.prop('disabled', true);
                
                // Auto-enable after 5 seconds as a fallback
                setTimeout(function() {
                    $form.data('submitted', false);
                    $buttons.prop('disabled', false);
                    $buttons.css('pointer-events', 'auto');
                }, 5000);
            }
        }, 10);
      });

      // Global fix to auto-focus Select2 search field when opened
      $(document).on('select2:open', function(e) {
          let searchField = document.querySelector('.select2-search__field');
          if (searchField) {
              searchField.focus();
          }
      });
    })
    
  </script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.datetime-picker').forEach(function(el) {
        flatpickr(el, {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            defaultDate: el.value ? el.value : new Date()
        });
    });
});
</script>