@extends('layouts.app')

@section('title', 'System Monitoring')

@section('content')
<div class="system-container">

    {{-- Header --}}
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1">System Monitoring</h2>
            <p class="text-muted mb-0">Monitor performa CPU, Memory, dan Disk VM VirtualBox secara real-time</p>
        </div>
        <a href="{{ route('system.create') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil-square me-1"></i>Input Manual
        </a>
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
            <div class="realtime-header d-flex align-items-center justify-content-between px-4 py-3 rounded-top flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="vm-status-dot" id="vm-status-dot"></div>
                    <div>
                        <h6 class="fw-bold mb-0 text-white">
                            <i class="bi bi-router me-2"></i>Mikrotik RouterOS — System Monitor
                        </h6>
                        <small class="text-white-50">
                            SNMP: <strong class="text-white">{{ config('snmp.host') }}</strong>
                        </small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="text-center d-none" id="countdown-wrap">
                        <small class="text-white-50 d-block" style="font-size:10px;">Refresh berikutnya</small>
                        <span class="badge bg-white text-dark fw-bold" id="countdown-badge">{{ (int) ($appSettings['polling_interval'] ?? 30) }}s</span>
                    </div>
                    <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" id="auto-refresh-toggle" style="width:2.5em;height:1.3em;cursor:pointer;">
                        <label class="form-check-label text-white small fw-semibold" for="auto-refresh-toggle">Auto ({{ (int) ($appSettings['polling_interval'] ?? 30) }}s)</label>
                    </div>
                    <button class="btn btn-light btn-sm fw-semibold px-3" id="btn-fetch-vm" onclick="fetchAndSaveFromVM()">
                        <i class="bi bi-lightning-charge-fill me-1 text-warning"></i>
                        <span id="fetch-btn-text">Fetch dari VM</span>
                        <span class="spinner-border spinner-border-sm d-none ms-1" id="fetch-spinner"></span>
                    </button>
                </div>
            </div>

            {{-- Alert bar --}}
            <div id="fetch-alert" class="d-none px-4 py-2" style="font-size:13px;"></div>

            {{-- CPU / Memory / Disk Gauge Cards --}}
            <div class="row g-0 border-top">
                {{-- CPU --}}
                <div class="col-12 col-md-4 gauge-cell border-end">
                    <div class="px-4 py-4 text-center">
                        <div class="gauge-wrap mb-3">
                            <canvas id="cpuGauge" width="140" height="140"></canvas>
                            <div class="gauge-label">
                                <div class="fw-bold fs-4" id="cpu-pct">
                                    {{ $latestMetric ? number_format($latestMetric->cpu_load, 1) : '—' }}<small class="fs-6">%</small>
                                </div>
                                <small class="text-muted">CPU Load</small>
                            </div>
                        </div>
                        <div class="progress mb-1" style="height:6px;">
                            <div class="progress-bar" id="cpu-bar"
                                 style="width:{{ $latestMetric ? $latestMetric->cpu_load : 0 }}%;
                                        background: {{ $latestMetric && $latestMetric->cpu_load > 80 ? '#ef4444' : ($latestMetric && $latestMetric->cpu_load > 50 ? '#f59e0b' : '#22c55e') }}">
                            </div>
                        </div>
                        <small class="text-muted d-block">
                            <i class="bi bi-cpu me-1"></i>
                            <span id="cpu-proc-name">{{ $latestMetric->processor_name ?? '—' }}</span>
                        </small>
                        <small class="text-muted">
                            <span id="cpu-cores">{{ $latestMetric->processor_cores ?? '—' }}</span> cores ·
                            <span id="cpu-freq">{{ $latestMetric ? $latestMetric->processor_frequency : '—' }}</span> GHz
                        </small>
                    </div>
                </div>

                {{-- Memory --}}
                <div class="col-12 col-md-4 gauge-cell border-end">
                    <div class="px-4 py-4 text-center">
                        <div class="gauge-wrap mb-3">
                            <canvas id="memGauge" width="140" height="140"></canvas>
                            <div class="gauge-label">
                                <div class="fw-bold fs-4" id="mem-pct">
                                    @if($latestMetric && $latestMetric->memory_total > 0)
                                        {{ number_format($latestMetric->memory_used / $latestMetric->memory_total * 100, 1) }}<small class="fs-6">%</small>
                                    @else
                                        —
                                    @endif
                                </div>
                                <small class="text-muted">Memory</small>
                            </div>
                        </div>
                        <div class="progress mb-1" style="height:6px;">
                            @php
                                $memPct = ($latestMetric && $latestMetric->memory_total > 0)
                                    ? round($latestMetric->memory_used / $latestMetric->memory_total * 100, 1) : 0;
                            @endphp
                            <div class="progress-bar bg-warning" id="mem-bar" style="width:{{ $memPct }}%"></div>
                        </div>
                        <small class="text-muted d-block">
                            <i class="bi bi-memory me-1"></i>
                            Digunakan: <strong id="mem-used">{{ $latestMetric ? $latestMetric->memory_used : '—' }}</strong> GB
                            / <span id="mem-total">{{ $latestMetric ? $latestMetric->memory_total : '—' }}</span> GB
                        </small>
                    </div>
                </div>

                {{-- Disk --}}
                <div class="col-12 col-md-4 gauge-cell">
                    <div class="px-4 py-4 text-center">
                        <div class="gauge-wrap mb-3">
                            <canvas id="diskGauge" width="140" height="140"></canvas>
                            <div class="gauge-label">
                                <div class="fw-bold fs-4" id="disk-pct">
                                    @if($latestMetric && $latestMetric->disk_total > 0)
                                        {{ number_format($latestMetric->disk_used / $latestMetric->disk_total * 100, 1) }}<small class="fs-6">%</small>
                                    @else
                                        —
                                    @endif
                                </div>
                                <small class="text-muted">Disk</small>
                            </div>
                        </div>
                        <div class="progress mb-1" style="height:6px;">
                            @php
                                $diskPct = ($latestMetric && $latestMetric->disk_total > 0)
                                    ? round($latestMetric->disk_used / $latestMetric->disk_total * 100, 1) : 0;
                            @endphp
                            <div class="progress-bar bg-info" id="disk-bar" style="width:{{ $diskPct }}%"></div>
                        </div>
                        <small class="text-muted d-block">
                            <i class="bi bi-hdd me-1"></i>
                            Digunakan: <strong id="disk-used">{{ $latestMetric ? $latestMetric->disk_used : '—' }}</strong> GB
                            / <span id="disk-total">{{ $latestMetric ? $latestMetric->disk_total : '—' }}</span> GB
                        </small>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-4 py-2 border-top d-flex align-items-center justify-content-between" style="background:#f8f9fa;border-radius:0 0 8px 8px;">
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    Terakhir diperbarui: <span id="rt-updated">{{ $latestMetric ? $latestMetric->recorded_at->format('d M Y H:i:s') : 'Belum ada data' }}</span>
                </small>
                <small class="text-muted">Data dari <strong>Mikrotik via SNMP</strong></small>
            </div>
        </div>
    </div>

    {{-- History Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-secondary"></i>Riwayat Data Sistem</h6>
            <span class="badge bg-secondary">{{ $metrics->total() }} record</span>
        </div>
        <div class="card-body p-0">
            @if($metrics->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Waktu</th>
                                <th>CPU Load</th>
                                <th>Memory</th>
                                <th>Disk</th>
                                <th>Processor</th>
                                <th class="pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($metrics as $metric)
                                <tr>
                                    <td class="ps-3">
                                        <small class="text-muted">{{ $metric->recorded_at->format('d M Y') }}</small><br>
                                        <strong>{{ $metric->recorded_at->format('H:i:s') }}</strong>
                                    </td>
                                    <td>
                                        @php $cpuColor = $metric->cpu_load > 80 ? 'bg-danger' : ($metric->cpu_load > 50 ? 'bg-warning' : 'bg-success'); @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:8px;min-width:60px;">
                                                <div class="progress-bar {{ $cpuColor }}" style="width:{{ $metric->cpu_load }}%"></div>
                                            </div>
                                            <span class="fw-semibold">{{ number_format($metric->cpu_load, 1) }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ number_format($metric->memory_used, 1) }} / {{ number_format($metric->memory_total, 1) }} GB</small><br>
                                        <span class="badge bg-warning text-dark">{{ number_format($metric->memory_percentage, 1) }}%</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ number_format($metric->disk_used, 1) }} / {{ number_format($metric->disk_total, 1) }} GB</small><br>
                                        <span class="badge bg-info text-white">{{ number_format($metric->disk_percentage, 1) }}%</span>
                                    </td>
                                    <td>
                                        <small>{{ $metric->processor_name }}<br>
                                        <span class="text-muted">{{ $metric->processor_cores }} cores · {{ $metric->processor_frequency }} GHz</span></small>
                                    </td>
                                    <td class="pe-3">
                                        <a href="{{ route('system.edit', $metric) }}" class="btn btn-sm btn-outline-warning py-0 px-2">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('system.destroy', $metric) }}" method="POST" style="display:inline;">
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
                    {{ $metrics->links() }}
                </div>
            @else
                <div class="p-4">
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>Belum ada data sistem.
                        Klik <strong>"Fetch dari VM"</strong> di panel atas untuk mulai mengambil data dari VirtualBox.
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

<style>
.system-container { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn {
    from { opacity:0; transform:translateY(8px); }
    to   { opacity:1; transform:translateY(0); }
}

.realtime-panel { border-radius:10px; overflow:hidden; }
.realtime-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #7c3aed 100%);
}

.vm-status-dot {
    width:12px; height:12px; border-radius:50%;
    background:#6b7280;
    box-shadow:0 0 0 3px rgba(107,114,128,.3);
    flex-shrink:0; transition:background .3s,box-shadow .3s;
}
.vm-status-dot.online {
    background:#22c55e; box-shadow:0 0 0 4px rgba(34,197,94,.3);
    animation:pulse-dot 2s infinite;
}
.vm-status-dot.error {
    background:#ef4444; box-shadow:0 0 0 4px rgba(239,68,68,.3);
}
@keyframes pulse-dot {
    0%,100%{ box-shadow:0 0 0 4px rgba(34,197,94,.3); }
    50%    { box-shadow:0 0 0 8px rgba(34,197,94,.1); }
}

/* Gauge */
.gauge-cell { transition:background .2s; }
.gauge-cell:hover { background:#f8f9ff; }
.gauge-wrap { position:relative; display:inline-block; }
.gauge-label {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
    text-align:center; line-height:1.2;
}

.progress { border-radius:10px; background:#e9ecef; }
.progress-bar { border-radius:10px; transition:width .6s ease, background .3s ease; }

/* Flash animation saat data diperbarui */
@keyframes flash-update {
    0%   { background: rgba(34,197,94,0.18); }
    100% { background: transparent; }
}
.gauge-cell.stat-updated {
    animation: flash-update 1.5s ease-out;
}
</style>

{{-- Gauge Script (Canvas Arc) --}}
<script>
function drawGauge(canvasId, pct, color) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const cx = canvas.width / 2, cy = canvas.height / 2, r = 55;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Background arc
    ctx.beginPath();
    ctx.arc(cx, cy, r, 0.75 * Math.PI, 2.25 * Math.PI);
    ctx.strokeStyle = '#e5e7eb';
    ctx.lineWidth = 13;
    ctx.lineCap = 'round';
    ctx.stroke();

    // Value arc
    if (pct > 0) {
        const end = 0.75 * Math.PI + (pct / 100) * 1.5 * Math.PI;
        ctx.beginPath();
        ctx.arc(cx, cy, r, 0.75 * Math.PI, end);
        ctx.strokeStyle = color;
        ctx.lineWidth = 13;
        ctx.lineCap = 'round';
        ctx.stroke();
    }
}

function gaugeColor(pct) {
    if (pct > 80) return '#ef4444';
    if (pct > 50) return '#f59e0b';
    return '#22c55e';
}

// Initial draw
@php
    $initCpu  = $latestMetric ? round($latestMetric->cpu_load, 1) : 0;
    $initMem  = ($latestMetric && $latestMetric->memory_total > 0) ? round($latestMetric->memory_used / $latestMetric->memory_total * 100, 1) : 0;
    $initDisk = ($latestMetric && $latestMetric->disk_total > 0) ? round($latestMetric->disk_used / $latestMetric->disk_total * 100, 1) : 0;
@endphp
document.addEventListener('DOMContentLoaded', () => {
    drawGauge('cpuGauge',  {{ $initCpu }},  gaugeColor({{ $initCpu }}));
    drawGauge('memGauge',  {{ $initMem }},  '#f59e0b');
    drawGauge('diskGauge', {{ $initDisk }}, '#3b82f6');
});
</script>

{{-- Realtime Script --}}
<script>
const CSRF_TOKEN      = '{{ csrf_token() }}';
const AUTO_FETCH_URL  = '{{ route("system.auto-fetch") }}';
const LATEST_JSON_URL = '{{ route("system.latest-json") }}';
const POLL_INTERVAL   = {{ (int) ($appSettings['polling_interval'] ?? 30) }};

let isFetching = false, countdownTimer = null, countdownSec = POLL_INTERVAL;

// ── Update UI ──────────────────────────────────────────────────
function updateUI(d) {
    const cpuPct  = parseFloat(d.cpu_load).toFixed(1);
    const memPct  = parseFloat(d.memory_pct).toFixed(1);
    const diskPct = parseFloat(d.disk_pct).toFixed(1);

    // Text values
    document.getElementById('cpu-pct').innerHTML  = cpuPct  + '<small class="fs-6">%</small>';
    document.getElementById('mem-pct').innerHTML  = memPct  + '<small class="fs-6">%</small>';
    document.getElementById('disk-pct').innerHTML = diskPct + '<small class="fs-6">%</small>';

    document.getElementById('mem-used').textContent  = parseFloat(d.memory_used).toFixed(2);
    document.getElementById('mem-total').textContent = parseFloat(d.memory_total).toFixed(2);
    document.getElementById('disk-used').textContent  = parseFloat(d.disk_used).toFixed(1);
    document.getElementById('disk-total').textContent = parseFloat(d.disk_total).toFixed(1);

    document.getElementById('cpu-proc-name').textContent = d.processor_name ?? '—';
    document.getElementById('cpu-cores').textContent     = d.processor_cores ?? '—';
    document.getElementById('cpu-freq').textContent      = d.processor_frequency ?? '—';
    document.getElementById('rt-updated').textContent    = d.recorded_at ?? '—';

    // Progress bars
    const cpuColor = cpuPct > 80 ? '#ef4444' : (cpuPct > 50 ? '#f59e0b' : '#22c55e');
    document.getElementById('cpu-bar').style.cssText  = `width:${cpuPct}%;background:${cpuColor}`;
    document.getElementById('mem-bar').style.width    = memPct  + '%';
    document.getElementById('disk-bar').style.width   = diskPct + '%';

    // Gauge redraw
    drawGauge('cpuGauge',  cpuPct,  cpuColor);
    drawGauge('memGauge',  memPct,  '#f59e0b');
    drawGauge('diskGauge', diskPct, '#3b82f6');

    // Flash animation pada setiap gauge-cell
    document.querySelectorAll('.gauge-cell').forEach(cell => {
        cell.classList.remove('stat-updated');
        void cell.offsetWidth; // trigger reflow
        cell.classList.add('stat-updated');
    });
}

// ── Status dot ────────────────────────────────────────────────
function setStatus(s) {
    document.getElementById('vm-status-dot').className = 'vm-status-dot' + (s !== 'idle' ? ' ' + s : '');
}

// ── Alert bar ─────────────────────────────────────────────────
function showAlert(type, msg) {
    const el = document.getElementById('fetch-alert');
    const colors = { success:'#d1fae5', danger:'#fee2e2', info:'#dbeafe' };
    const icons  = { success:'bi-check-circle-fill text-success', danger:'bi-exclamation-triangle-fill text-danger', info:'bi-info-circle-fill text-info' };
    el.style.background = colors[type] ?? '#f3f4f6';
    el.innerHTML = `<i class="bi ${icons[type] ?? ''} me-2"></i>${msg}`;
    el.classList.remove('d-none');
    clearTimeout(el._t);
    if (type !== 'danger') el._t = setTimeout(() => el.classList.add('d-none'), 5000);
}

// ── Fetch from VM + Save ──────────────────────────────────────
async function fetchAndSaveFromVM(silent = false) {
    if (isFetching) return;
    isFetching = true;
    const btn = document.getElementById('btn-fetch-vm');
    const txt = document.getElementById('fetch-btn-text');
    const spn = document.getElementById('fetch-spinner');
    btn.disabled = true; txt.textContent = 'Mengambil...'; spn.classList.remove('d-none');
    if (!silent) document.getElementById('fetch-alert').classList.add('d-none');

    try {
        const res  = await fetch(AUTO_FETCH_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json', 'Content-Type': 'application/json' }
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
    } catch (e) {
        setStatus('error');
        showAlert('danger', 'Gagal terhubung ke server. Pastikan VM menyala dan konfigurasi SSH di .env benar.');
    } finally {
        isFetching = false;
        btn.disabled = false; txt.textContent = 'Fetch dari VM'; spn.classList.add('d-none');
    }
}

// ── Poll DB terbaru (tanpa SSH) ───────────────────────────────
async function pollLatest() {
    try {
        const res  = await fetch(LATEST_JSON_URL, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (json.success) updateUI(json.data);
    } catch (_) {}
}

// ── Countdown ─────────────────────────────────────────────────
function startCountdown() {
    countdownSec = POLL_INTERVAL;
    const badge = document.getElementById('countdown-badge');
    badge.textContent = countdownSec + 's';
    clearInterval(countdownTimer);
    countdownTimer = setInterval(() => {
        countdownSec--;
        badge.textContent = countdownSec > 0 ? countdownSec + 's' : '...';
        if (countdownSec <= 0) {
            clearInterval(countdownTimer);
            fetchAndSaveFromVM(true).then(() => {
                if (document.getElementById('auto-refresh-toggle').checked) startCountdown();
            });
        }
    }, 1000);
}

// ── Toggle Listener ───────────────────────────────────────────
document.getElementById('auto-refresh-toggle').addEventListener('change', function () {
    const wrap = document.getElementById('countdown-wrap');
    if (this.checked) {
        wrap.classList.remove('d-none');
        startCountdown();
    } else {
        wrap.classList.add('d-none');
        clearInterval(countdownTimer);
    }
});

// ── Init: poll data terbaru dari DB saat halaman dimuat ───────
document.addEventListener('DOMContentLoaded', () => {
    pollLatest();
    // Auto-polling setiap 30 detik dari DB (tanpa SSH) agar data selalu fresh
    setInterval(pollLatest, POLL_INTERVAL * 1000);
});
</script>

@endsection