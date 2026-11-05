@extends('layouts.main')

@section('title', 'Escanear Sócio')
@section('breadcrumb-item', 'Dashboard')
@section('breadcrumb-item-active', 'Escanear Sócio')

@section('css')
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="ti ti-qrcode me-1"></i>Escanear Sócio</h5>
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Voltar ao Painel
                    </a>
                </div>
                <div class="card-body text-center py-4">
                    <div id="qr-reader" style="width: 100%; max-width: 420px; margin: 0 auto;"></div>

                    <div class="mt-4">
                        <p class="text-muted text-sm">
                            <i class="ti ti-info me-1"></i>
                            Aponte a câmera para o QR Code colado na raquete do sócio
                        </p>
                    </div>

                    <div class="mt-4">
                        <p class="text-muted text-sm mb-2">Ou digite o número do sócio:</p>
                        <input type="text" id="qrManualInput" class="form-control form-control-sm w-75 mx-auto" placeholder="VBT-2026-0003" autocomplete="off">
                        <button class="btn btn-sm btn-primary mt-2" onclick="checkManual()">
                            <i class="ti ti-search me-1"></i> Verificar
                        </button>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.membres.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-user me-1"></i> Ver Lista de Sócios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrcodeScanner = null;

        const qrReader = document.getElementById('qr-reader');

        html5QrcodeScanner = new Html5QrcodeScanner('qr-reader', {
            fps: 10,
            qrbox: 250
        }, false);

        html5QrcodeScanner.render(
            (decodedText) => {
                // O QR Code contém a URL completa: https://vbt.rcmasters.com.br/check/VBT-2026-0003
                // Extraímos o memberNumber do final da URL
                const match = decodedText.match(/\/check\/([A-Z0-9-]+)$/) || decodedText.match(/([A-Z0-9]{3}-\d{4}-\d{4})$/);
                if (match) {
                    const memberNumber = match[1];
                    html5QrcodeScanner.clear();
                    window.location.href = `/check/${memberNumber}`;
                } else {
                    alert('QR Code não reconhecido. Tente novamente.');
                }
            },
            (errorMessage) => {
                // ignore scan errors
            }
        );

        function checkManual() {
            const input = document.getElementById('qrManualInput');
            const val = input.value.trim();
            if (val) {
                window.location.href = `/check/${val}`;
            }
        }

        document.getElementById('qrManualInput')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') checkManual();
        });
    </script>

@endsection
