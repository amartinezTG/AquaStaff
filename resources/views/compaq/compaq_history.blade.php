@extends('layout.shared')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-4">Historial de Archivos COMPAQ</h3>
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre del Archivo</th>
                        <th>Fecha de Generación</th>
                        <th>Descargar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($compaqFiles as $index => $file)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $file->name }}</td>
                            <td>{{ $file->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>
                                @if($file->file_path)
                                    <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="btn btn-primary btn-sm">Descargar</a>
                                @else
                                    <span class="text-muted">No disponible</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay archivos generados aún.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
