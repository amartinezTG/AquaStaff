{{-- ══ MODAL DETALLE DÍA ══ --}}
<div class="modal fade" id="modalCorte" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
 
      <div class="modal-header bg-dark text-white py-2">
        <h5 class="modal-title" id="modalCorteTitle">Corte del día</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-0" id="modalCorteBody">
        {{-- Spinner inicial --}}
        <div class="text-center py-5" id="modalSpinner">
          <div class="spinner-border text-primary" role="status"></div>
          <div class="mt-2 text-muted small">Cargando...</div>
        </div>
      </div>

      <div class="modal-footer py-2">
        <a id="modalLinkCapturar" href="#" class="btn btn-sm btn-outline-warning">
          <i class="bi bi-pencil me-1"></i> Ir a capturar
        </a>
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

<footer id="footer" class="footer">
  <div class="copyright">&copy; Copyright <strong><span></span></strong>. All Rights Reserved</div>
</footer>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center">
  <i class="bi bi-arrow-up-short"></i>
</a>
@include('layout.footer')

<script>
(function () {
  var BASE_MODAL = "{{ url('cortes') }}";
  var BASE_CAP   = "{{ route('cortes.create') }}";

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-ver-corte');
    if (!btn) return;

    var fecha  = btn.dataset.fecha;
    var modal  = new bootstrap.Modal(document.getElementById('modalCorte'));
    var body   = document.getElementById('modalCorteBody');
    var title  = document.getElementById('modalCorteTitle');
    var link   = document.getElementById('modalLinkCapturar');

    // Resetear contenido
    body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted small">Cargando...</div></div>';
    title.textContent = 'Corte del ' + fecha.split('-').reverse().join('/');
    link.href = BASE_CAP + '?fecha=' + fecha;

    modal.show();

    $.get(BASE_MODAL + '/' + fecha + '/modal')
      .done(function (html) {
        body.innerHTML = html;
      })
      .fail(function () {
        body.innerHTML = '<div class="text-center py-4 text-danger p-3">Error al cargar los datos.</div>';
      });
  });
})();
</script>
