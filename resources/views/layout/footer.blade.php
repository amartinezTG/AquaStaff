
  <!-- Vendor JS Files -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>

  <script src="{{ asset('/assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
  <script src="{{ asset('/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('/assets/vendor/chart.js/chart.umd.js') }}"></script>
  <script src="{{ asset('/assets/vendor/echarts/echarts.min.js') }}"></script>
  <script src="{{ asset('/assets/vendor/quill/quill.js') }}"></script>
  <!--<script src="{{ asset('/assets/vendor/simple-datatables/simple-datatables.js') }}"></script>-->
  <script src="{{ asset('/assets/vendor/tinymce/tinymce.min.js') }}"></script>
  <script src="{{ asset('/assets/vendor/php-email-form/validate.js') }}"></script>

  <!-- Template Main JS File -->
  <script src="{{ asset('/assets/js/main.js') }}"></script>

  <script src="https://www.google.com/recaptcha/api.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.dataTables.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.print.min.js"></script>


    <script>
        $(document).ready(function() {
            new DataTable('#datatable', {
                ordering: false,
                pageLength: 100,
                lengthMenu: [
                    ['All']
                ],
                layout: {
                    topStart: {
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    }
                }
            });

            new DataTable('#datatable2', {
                ordering: false,
                layout: {
                    topStart: {
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    }
                }
            });

            new DataTable('#datatableRows', {
                ordering: false,
                
            });

            new DataTable('#transacciones', {
                pageLength: 80,
                ordering: false,
                
            });

            $('#select-all').on('click', function() {
                $('input[type="checkbox"]').prop('checked', $(this).is(':checked'));
                toggleDiv();
            });

            $('input[type="checkbox"]').on('change', function() {
                toggleDiv();
            });

            function toggleDiv() {
                $('#your_div_id').toggle($('input[type="checkbox"]:checked').length > 0);
            }
            
        });
    </script>
    

</body>

</html>