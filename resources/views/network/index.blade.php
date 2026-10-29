@extends('layouts.app')

@section('title', 'Network Traffic')

@section('content')
<div class="network-container">

    {{-- Header --}}
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1">Network Traffic</h2>
            <p class="text-muted mb-0">Monitor lalu lintas jaringan dan performa koneksi</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('network.create') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil-square me-1"></i>Input Manual
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ===== REALTIME PANEL ===== --}}
    <div class="card mb-4 border-0 shadow-sm realtime-panel">
        <div class="card-body p-0">
            {{-- Panel Header --}}
            <div class="realtime-header d-flex flex-wrap align-items-center justify-content-between gap-3 px-3 px-sm-4 py-3 rounded-top">
                <div class="d-flex align-items-center gap-3">
                    <div class="vm-status-dot" id="vm-status-dot"></div>
                    <div>
                        <h6 class="fw-bold mb-0 text-white">
                            <i class="bi bi-router me-2"></i>Mikrotik RouterOS — Network Monitor
                        </h6>
                        <small class="text-white-50">
                            Interface: <strong class="text-white">{{ config('snmp.interface_name') }}</strong>
                            &nbsp;·&nbsp;
                            SNMP: <strong class="text-white">{{ config('snmp.host') }}</strong>
                        </small>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 gap-sm-3">
                    {{-- Countdown --}}
                    <div class="text-center d-none" id="countdown-wrap">
                        <small class="text-white-50 d-block" style="font-size:10px;">Refresh berikutnya</small>
                        <span class="badge bg-white text-dark fw-bold" id="countdown-badge">{{ (int) ($appSettings['polling_interval'] ?? 30) }}s</span>
                    </div>
                    {{-- Auto Refresh Toggle --}}
                    <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" id="auto-refresh-toggle" style="width:2.5em;height:1.3em;cursor:pointer;">
                        <label class="form-check-label text-white small fw-semibold" for="auto-refresh-toggle">Auto ({{ (int) ($appSettings['polling_interval'] ?? 30) }}s)</label>
                    </div>
                    {{-- Fetch Button --}}
                    <button class="btn btn-light btn-sm fw-semibold px-3 text-nowrap" id="btn-fetch-vm" onclick="fetchAndSaveFromVM()">
                        <i class="bi bi-lightning-charge-fill me-1 text-warning"></i>
                        <span id="fetch-btn-text">Fetch dari VM</span>
                        <span class="spinner-border spinner-border-sm d-none ms-1" id="fetch-spinner"></span>
                    </button>
                </div>
            </div>

            {{-- Alert bar --}}
            <div id="fetch-alert" class="d-none px-4 py-2" style="font-size:13px;"></div>

            {{-- Live Stats Grid --}}
            <div class="row g-0 border-top" id="realtime-stats">
                <div class="col-6 col-md-3 stat-cell border-end border-bottom">
                    <div class="px-4 py-3">
                        <small class="text-muted d-block mb-1"><i class="bi bi-arrow-down-circle text-primary me-1"></i>Download Speed</small>
                        <div class="fw-bold fs-5" id="rt-download">
                            {{ $latestTraffic ? number_format($latestTraffic->download_speed, 2) : '—' }}
                            <small class="text-muted fw-normal fs-6">Mbps</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 stat-cell border-end border-bottom">
                    <div class="px-4 py-3">
                        <small class="text-muted d-block mb-1"><i class="bi bi-arrow-up-circle text-success me-1"></i>Upload Speed</small>
                        <div class="fw-bold fs-5" id="rt-upload">
                            {{ $latestTraffic ? number_format($latestTraffic->upload_speed, 2) : '—' }}
                            <small class="text-muted fw-normal fs-6">Mbps</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 stat-cell border-end border-bottom">
                    <div class="px-4 py-3">
                        <small class="text-muted d-block mb-1"><i class="bi bi-diagram-3 text-info me-1"></i>Active Connections</small>
                        <div class="fw-bold fs-5" id="rt-active">
                            {{ $latestTraffic ? $latestTraffic->active_connections : '—' }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 stat-cell border-bottom">
                    <div class="px-4 py-3">
                        <small class="text-muted d-block mb-1"><i class="bi bi-check2-circle text-success me-1"></i>Established</small>
                        <div class="fw-bold fs-5" id="rt-estab">
                            {{ $latestTraffic ? $latestTraffic->established_connections : '—' }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 stat-cell border-end">
                    <div class="px-4 py-3">
                        <small class="text-muted d-block mb-1"><i class="bi bi-send text-warning me-1"></i>Packets Sent</small>
                        <div class="fw-bold" id="rt-pkt-sent">
                            {{ $latestTraffic ? number_format($latestTraffic->packets_sent) : '—' }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 stat-cell border-end">
                    <div class="px-4 py-3">
                        <small class="text-muted d-block mb-1"><i class="bi bi-inbox text-info me-1"></i>Packets Received</small>
                        <div class="fw-bold" id="rt-pkt-recv">
                            {{ $latestTraffic ? number_format($latestTraffic->packets_received) : '—' }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 stat-cell border-end">
                    <div class="px-4 py-3">
                        <small class="text-muted d-block mb-1"><i class="bi bi-cloud-upload text-primary me-1"></i>Bytes Sent</small>
                        <div class="fw-bold" id="rt-bytes-sent">
                            {{ $latestTraffic ? number_format($latestTraffic->bytes_sent) : '—' }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 stat-cell">
                    <div class="px-4 py-3">
                        <small class="text-muted d-block mb-1"><i class="bi bi-cloud-download text-success me-1"></i>Bytes Received</small>
                        <div class="fw-bold" id="rt-bytes-recv">
                            {{ $latestTraffic ? number_format($latestTraffic->bytes_received) : '—' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer: last updated --}}
            <div class="px-4 py-2 border-top d-flex align-items-center justify-content-between" style="background:#f8f9fa;border-radius:0 0 8px 8px;">
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    Terakhir diperbarui: <span id="rt-updated">{{ $latestTraffic ? $latestTraffic->recorded_at->format('d M Y H:i:s') : 'Belum ada data' }}</span>
                </small>
                <small class="text-muted">
                    Interface: <strong id="rt-interface">{{ $latestTraffic ? $latestTraffic->interface_name : '—' }}</strong>
                </small>
            </div>
        </div>
    </div>

    {{-- Traffic Chart --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up me-2 text-primary"></i>Traffic Analysis (Last 24 Hours)</h6>
        </div>
        <div class="card-body">
            @if(count($chartLabels) > 0)
                <div style="position: relative; height: clamp(220px, 45vw, 300px);">
                    <canvas id="trafficChart"></canvas>
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>Belum ada data traffic dalam 24 jam terakhir.
                    Klik <strong>Fetch dari VM</strong> di panel atas untuk mulai mengumpulkan data.
                </div>
            @endif
        </div>
    </div>

    {{-- History Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-secondary"></i>Riwayat Data Network Traffic</h6>
            <span class="badge bg-secondary">{{ $traffics->total() }} record</span>
        </div>
        <div class="card-body p-0">
            @if($traffics->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Waktu</th>
                                <th>Interface</th>
                                <th>Download</th>
                                <th>Upload</th>
                                <th>Packets</th>
                                <th>Bytes</th>
                                <th>Connections</th>
                                <th class="pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($traffics as $traffic)
                                <tr>
                                    <td class="ps-3">
                                        <small class="text-muted">{{ $traffic->recorded_at->format('d M Y') }}</small><br>
                                        <strong>{{ $traffic->recorded_at->format('H:i:s') }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">{{ $traffic->interface_name }}</span>
                                    </td>
                                    <td>
                                        <i class="bi bi-arrow-down text-primary"></i>
                                        {{ number_format($traffic->download_speed, 2) }} Mbps
                                    </td>
                                    <td>
                                        <i class="bi bi-arrow-up text-success"></i>
                                        {{ number_format($traffic->upload_speed, 2) }} Mbps
                                    </td>
                                    <td>
                                        <small class="text-muted">↑</small> {{ number_format($traffic->packets_sent) }}<br>
                                        <small class="text-muted">↓</small> {{ number_format($traffic->packets_received) }}
                                    </td>
                                    <td>
                                        <small class="text-muted">↑</small> {{ number_format($traffic->bytes_sent) }}<br>
                                        <small class="text-muted">↓</small> {{ number_format($traffic->bytes_received) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">{{ $traffic->active_connections }} aktif</span><br>
                                        <span class="badge bg-success bg-opacity-10 text-success">{{ $traffic->established_connections }} estab</span>
                                    </td>
                                    <td class="pe-3">
                                        <a href="{{ route('network.edit', $traffic) }}" class="btn btn-sm btn-outline-warning py-0 px-2">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('network.destroy', $traffic) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Yakin hapus data ini?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-3 border-top">
                    {{ $traffics->links() }}
                </div>
            @else
                <div class="p-4">
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>Belum ada data.
                        Klik <strong>"Fetch dari VM"</strong> di panel atas atau <a href="{{ route('network.create') }}">tambah manual</a>.
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

<style>
.network-container {
    animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Realtime Panel */
.realtime-panel { border-radius: 10px; overflow: hidden; }

.realtime-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
}

.vm-status-dot {
    width: 12px; height: 12px;
    border-radius: 50%;
    background: #6b7280;
    box-shadow: 0 0 0 3px rgba(107,114,128,0.3);
    flex-shrink: 0;
    transition: background 0.3s, box-shadow 0.3s;
}
.vm-status-dot.online {
    background: #22c55e;
    box-shadow: 0 0 0 4px rgba(34,197,94,0.3);
    animation: pulse-dot 2s infinite;
}
.vm-status-dot.error {
    background: #ef4444;
    box-shadow: 0 0 0 4px rgba(239,68,68,0.3);
}
@keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 0 4px rgba(34,197,94,0.3); }
    50%       { box-shadow: 0 0 0 8px rgba(34,197,94,0.1); }
}

.stat-cell { transition: background 0.2s; }
.stat-cell:hover { background: #f8f9ff; }
.stat-cell .fw-bold { transition: color 0.3s; }

/* Flash animation saat data update */
@keyframes flash-update {
    0%   { background: rgba(34,197,94,0.15); }
    100% { background: transparent; }
}
.stat-updated {
    animation: flash-update 1.5s ease-out;
}
</style>

{{-- Chart Script --}}
@if(count($chartLabels) > 0)
<script>
const trafficChart = new Chart(document.getElementById('trafficChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [
            {
                label: 'Download (Mbps)',
                data: {!! json_encode($chartDownload) !!},
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.08)',
                tension: 0.4, fill: true,
                pointRadius: 3, pointBackgroundColor: '#2563eb'
            },
            {
                label: 'Upload (Mbps)',
                data: {!! json_encode($chartUpload) !!},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.08)',
                tension: 0.4, fill: true,
                pointRadius: 3, pointBackgroundColor: '#10b981'
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'top' } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: v => v + ' Mbps' }
            }
        }
    }
});
</script>
@endif

{{-- Realtime Fetch & Auto-Poll Script --}}
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
const AUTO_FETCH_URL  = '{{ route("network.auto-fetch") }}';
const LATEST_JSON_URL = '{{ route("network.latest-json") }}';
const POLL_INTERVAL   = {{ (int) ($appSettings['polling_interval'] ?? 30) }}; // detik

let autoTimer   = null;
let countdownTimer = null;
let countdownSec = POLL_INTERVAL;
let isFetching  = false;

// ─── Helper: format angka ──────────────────────────────────────
function fmt(n)  { return parseFloat(n).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function fmtInt(n) { return parseInt(n).toLocaleString('id-ID'); }

// ─── Update UI dari data JSON ──────────────────────────────────
function updateUI(data) {
    const map = {
        'rt-download'  : fmt(data.download_speed) + ' <small class="text-muted fw-normal fs-6">Mbps</small>',
        'rt-upload'    : fmt(data.upload_speed)   + ' <small class="text-muted fw-normal fs-6">Mbps</small>',
        'rt-active'    : data.active_connections,
        'rt-estab'     : data.established_connections,
        'rt-pkt-sent'  : fmtInt(data.packets_sent),
        'rt-pkt-recv'  : fmtInt(data.packets_received),
        'rt-bytes-sent': fmtInt(data.bytes_sent),
        'rt-bytes-recv': fmtInt(data.bytes_received),
    };
    Object.entries(map).forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.innerHTML = val;
        // Flash animasi
        el.closest('.stat-cell')?.classList.remove('stat-updated');
        void el.closest('.stat-cell')?.offsetWidth; // reflow
        el.closest('.stat-cell')?.classList.add('stat-updated');
    });
    document.getElementById('rt-updated').textContent  = data.recorded_at ?? '—';
    document.getElementById('rt-interface').textContent = data.interface_name ?? '—';
}

// ─── Set status dot ───────────────────────────────────────────
function setStatus(state) { // 'idle' | 'online' | 'error'
    const dot = document.getElementById('vm-status-dot');
    dot.className = 'vm-status-dot' + (state !== 'idle' ? ' ' + state : '');
}

// ─── Show alert bar ───────────────────────────────────────────
function showAlert(type, msg) {
    const el = document.getElementById('fetch-alert');
    const colors = { success: '#d1fae5', danger: '#fee2e2', info: '#dbeafe' };
    const icons  = { success: 'bi-check-circle-fill text-success', danger: 'bi-exclamation-triangle-fill text-danger', info: 'bi-info-circle-fill text-info' };
    el.className = '';
    el.style.background = colors[type] ?? '#f3f4f6';
    el.innerHTML = `<i class="bi ${icons[type] ?? ''} me-2"></i>${msg}`;
    el.classList.remove('d-none');
    clearTimeout(el._timer);
    if (type !== 'danger') {
        el._timer = setTimeout(() => el.classList.add('d-none'), 5000);
    }
}

// ─── Fetch dari VM + Simpan ───────────────────────────────────
async function fetchAndSaveFromVM(silent = false) {
    if (isFetching) return;
    isFetching = true;

    const btn     = document.getElementById('btn-fetch-vm');
    const btnText = document.getElementById('fetch-btn-text');
    const spinner = document.getElementById('fetch-spinner');

    btn.disabled = true;
    btnText.textContent = 'Mengambil...';
    spinner.classList.remove('d-none');
    if (!silent) document.getElementById('fetch-alert').classList.add('d-none');

    try {
        const res  = await fetch(AUTO_FETCH_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        });
        const json = await res.json();

        if (json.success) {
            updateUI(json.data);
            setStatus('online');
            if (!silent) showAlert('success', json.message);
        } else {
            setStatus('error');
            showAlert('danger', json.message);
        }
    } catch (err) {
        setStatus('error');
        showAlert('danger', 'Gagal terhubung ke server. Pastikan VM menyala dan konfigurasi SSH di .env benar.');
    } finally {
        isFetching = false;
        btn.disabled = false;
        btnText.textContent = 'Fetch dari VM';
        spinner.classList.add('d-none');
    }
}

// ─── Poll data terbaru (tanpa SSH, hanya dari DB) ─────────────
async function pollLatest() {
    try {
        const res  = await fetch(LATEST_JSON_URL, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (json.success) updateUI(json.data);
    } catch (_) { /* silent */ }
}

// ─── Countdown Timer ──────────────────────────────────────────
function startCountdown() {
    countdownSec = POLL_INTERVAL;
    const badge = document.getElementById('countdown-badge');
    if (badge) badge.textContent = countdownSec + 's';

    clearInterval(countdownTimer);
    countdownTimer = setInterval(() => {
        countdownSec--;
        if (badge) badge.textContent = countdownSec > 0 ? countdownSec + 's' : '...';
        if (countdownSec <= 0) {
            clearInterval(countdownTimer);
            fetchAndSaveFromVM(true).then(() => {
                if (document.getElementById('auto-refresh-toggle').checked) startCountdown();
            });
        }
    }, 1000);
}

// ─── Auto Refresh Toggle ──────────────────────────────────────
document.getElementById('auto-refresh-toggle').addEventListener('change', function () {
    const wrap = document.getElementById('countdown-wrap');
    if (this.checked) {
        wrap.classList.remove('d-none');
        startCountdown();
    } else {
        wrap.classList.add('d-none');
        clearInterval(autoTimer);
        clearInterval(countdownTimer);
    }
});

// ─── Init: poll data terbaru dari DB saat halaman dimuat ──────
document.addEventListener('DOMContentLoaded', () => {
    pollLatest();
});
</script>

@endsection