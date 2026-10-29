@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard-container">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h6 class="text-muted mb-0">Selamat Datang {{ Auth::user()->name }} 👋</h6>
            <h2 class="fw-bold mb-0">Dashboard</h2>
        </div>
        <div class="d-flex flex-wrap align-items-center column-gap-3 row-gap-1">
            <div class="d-flex align-items-center gap-2">
                <div class="live-dot" id="live-dot"></div>
                <small class="text-muted fw-semibold" id="last-updated-text">
                    {{ $latestSystem ? $latestSystem->recorded_at->format('d M Y H:i:s') : 'Belum ada data' }}
                </small>
            </div>
            <p class="text-muted mb-0">Monitoring Jaringan BMKG</p>
        </div>
    </div>

    {{-- CPU & RAM Donut Charts --}}
    <div class="row g-3 mb-4">
        {{-- CPU Load --}}
        <div class="col-12 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted fw-semibold mb-3">
                        <i class="bi bi-cpu me-1 text-danger"></i>CPU Load
                    </h6>
                    <div class="chart-donut-wrap mx-auto mb-3">
                        <canvas id="cpuChart"></canvas>
                        <div class="chart-center-label">
                            <div class="fw-bold fs-4 text-danger" id="cpu-pct-label">
                                {{ $latestSystem ? number_format($latestSystem->cpu_load, 1) : '—' }}<small class="fs-6">%</small>
                            </div>
                            <small class="text-muted">Used</small>
                        </div>
                    </div>
                    <small class="text-muted" id="cpu-info">
                        {{ $latestSystem ? ($latestSystem->processor_name . ' · ' . $latestSystem->processor_cores . ' cores') : '—' }}
                    </small>
                </div>
            </div>
        </div>

        {{-- RAM Load --}}
        <div class="col-12 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted fw-semibold mb-3">
                        <i class="bi bi-memory me-1 text-success"></i>RAM Load
                    </h6>
                    <div class="chart-donut-wrap mx-auto mb-3">
                        <canvas id="ramChart"></canvas>
                        <div class="chart-center-label">
                            <div class="fw-bold fs-4 text-success" id="ram-pct-label">
                                @if($latestSystem && $latestSystem->memory_total > 0)
                                    {{ number_format($latestSystem->memory_used / $latestSystem->memory_total * 100, 1) }}<small class="fs-6">%</small>
                                @else
                                    —
                                @endif
                            </div>
                            <small class="text-muted">Used</small>
                        </div>
                    </div>
                    <small class="text-muted" id="ram-info">
                        @if($latestSystem)
                            {{ $latestSystem->memory_used }} / {{ $latestSystem->memory_total }} GB
                        @else
                            —
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm text-center stat-card-anim" id="card-download">
                <div class="card-body py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-arrow-down-circle text-primary me-1"></i>Download Speed</h6>
                    <h3 class="fw-bold text-primary mb-0" id="stat-download">
                        {{ $latestNetwork ? number_format($latestNetwork->download_speed, 2) : '—' }}
                        <small class="fs-6 text-muted fw-normal">Mbps</small>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm text-center stat-card-anim" id="card-upload">
                <div class="card-body py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-arrow-up-circle text-success me-1"></i>Upload Speed</h6>
                    <h3 class="fw-bold text-success mb-0" id="stat-upload">
                        {{ $latestNetwork ? number_format($latestNetwork->upload_speed, 2) : '—' }}
                        <small class="fs-6 text-muted fw-normal">Mbps</small>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm text-center stat-card-anim" id="card-memory">
                <div class="card-body py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-hdd text-warning me-1"></i>AVG Memory</h6>
                    <h3 class="fw-bold mb-0" id="stat-memory">
                        @if($latestSystem && $latestSystem->memory_total > 0)
                            {{ number_format($latestSystem->memory_used / $latestSystem->memory_total * 100, 1) }}
                            <small class="fs-6 text-muted fw-normal">%</small>
                        @else
                            —
                        @endif
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Network Traffic Chart --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3 d-flex flex-wrap align-items-center justify-content-between gap-1">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-graph-up me-2 text-primary"></i>Network Traffic
            </h6>
            <small class="text-muted" id="chart-updated">
                {{ count($chartLabels) > 0 ? '20 data terakhir' : 'Belum ada data' }}
            </small>
        </div>
        <div class="card-body">
            <div class="chart-traffic-wrap">
                <canvas id="trafficChart"></canvas>
            </div>
        </div>
    </div>

</div>

<style>
.dashboard-container {
    animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Live dot indicator */
.live-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: #6b7280;
    flex-shrink: 0;
    transition: background 0.3s, box-shadow 0.3s;
}
.live-dot.active {
    background: #22c55e;
    box-shadow: 0 0 0 3px rgba(34,197,94,0.3);
    animation: pulse-live 2s infinite;
}
@keyframes pulse-live {
    0%, 100% { box-shadow: 0 0 0 3px rgba(34,197,94,0.3); }
    50%       { box-shadow: 0 0 0 7px rgba(34,197,94,0.1); }
}

/* Donut chart container */
.chart-donut-wrap {
    position: relative;
    width: 160px;
    height: 160px;
}
@media (max-width: 575.98px) {
    .chart-donut-wrap { width: 140px; height: 140px; }
}

/* Grafik trafik: tinggi mengikuti lebar layar agar tetap terbaca di HP */
.chart-traffic-wrap {
    position: relative;
    height: clamp(220px, 45vw, 300px);
}
.chart-center-label {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    line-height: 1.2;
    pointer-events: none;
}

/* Flash animation saat card diupdate */
@keyframes flash-card {
    0%   { box-shadow: 0 0 0 3px rgba(34,197,94,0.4); }
    100% { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
}
.stat-card-anim.updated {
    animation: flash-card 1.2s ease-out;
}
</style>

@php
    $initCpu = $latestSystem ? round($latestSystem->cpu_load, 1) : 0;
    $initRam = ($latestSystem && $latestSystem->memory_total > 0)
        ? round($latestSystem->memory_used / $latestSystem->memory_total * 100, 1) : 0;
@endphp

<script>
// ── Chart instances ──────────────────────────────────────────────
let cpuChart, ramChart, trafficChart;

function buildCpuChart(pct) {
    const ctx = document.getElementById('cpuChart');
    if (!ctx) return;
    cpuChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Used', 'Free'],
            datasets: [{ data: [pct, Math.max(0, 100 - pct)], backgroundColor: ['#dc2626','#e5e7eb'], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: true, cutout: '72%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
    });
}

function buildRamChart(pct) {
    const ctx = document.getElementById('ramChart');
    if (!ctx) return;
    ramChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Used', 'Free'],
            datasets: [{ data: [pct, Math.max(0, 100 - pct)], backgroundColor: ['#16a34a','#e5e7eb'], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: true, cutout: '72%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
    });
}

function buildTrafficChart(labels, download, upload) {
    const ctx = document.getElementById('trafficChart');
    if (!ctx) return;
    trafficChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { label: 'Download (Mbps)', data: download, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.08)', tension: 0.4, fill: true, pointRadius: 3 },
                { label: 'Upload (Mbps)',   data: upload,   borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', tension: 0.4, fill: true, pointRadius: 3 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: true, position: 'top' } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' Mbps' } } }
        }
    });
}

// ── Update donut chart data ──────────────────────────────────────
function updateDonut(chart, pct) {
    if (!chart) return;
    chart.data.datasets[0].data = [pct, Math.max(0, 100 - pct)];
    chart.update('none');
}

// ── Append data point to traffic chart ──────────────────────────
function appendTrafficPoint(label, dl, ul) {
    if (!trafficChart) return;
    trafficChart.data.labels.push(label);
    trafficChart.data.datasets[0].data.push(dl);
    trafficChart.data.datasets[1].data.push(ul);
    // Batasi 30 titik
    if (trafficChart.data.labels.length > 30) {
        trafficChart.data.labels.shift();
        trafficChart.data.datasets[0].data.shift();
        trafficChart.data.datasets[1].data.shift();
    }
    trafficChart.update();
}

// ── Flash card animation ─────────────────────────────────────────
function flashCard(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('updated');
    void el.offsetWidth;
    el.classList.add('updated');
}

// ── Polling: System (CPU + RAM) ──────────────────────────────────
const SYS_URL = '{{ route("system.latest-json") }}';
let lastSysTime = null;

async function pollSystem() {
    try {
        const res  = await fetch(SYS_URL, { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success) return;
        const d = json.data;

        const cpuPct = parseFloat(d.cpu_load).toFixed(1);
        const ramPct = parseFloat(d.memory_pct).toFixed(1);

        // Update labels
        document.getElementById('cpu-pct-label').innerHTML = cpuPct + '<small class="fs-6">%</small>';
        document.getElementById('ram-pct-label').innerHTML = ramPct + '<small class="fs-6">%</small>';
        document.getElementById('cpu-info').textContent    = (d.processor_name ?? '—') + ' · ' + (d.processor_cores ?? '—') + ' cores';
        document.getElementById('ram-info').textContent    = parseFloat(d.memory_used).toFixed(2) + ' / ' + parseFloat(d.memory_total).toFixed(2) + ' GB';
        document.getElementById('stat-memory').innerHTML  = parseFloat(d.memory_pct).toFixed(1) + ' <small class="fs-6 text-muted fw-normal">%</small>';

        // Update charts
        updateDonut(cpuChart, cpuPct);
        updateDonut(ramChart, ramPct);

        // Update timestamp + live dot only if data changed
        if (d.recorded_at !== lastSysTime) {
            lastSysTime = d.recorded_at;
            document.getElementById('last-updated-text').textContent = d.recorded_at;
            document.getElementById('live-dot').classList.add('active');
            flashCard('card-memory');
        }
    } catch (_) {}
}

// ── Polling: Network Traffic ─────────────────────────────────────
const NET_URL = '{{ route("network.latest-json") }}';
let lastNetTime = null;

async function pollNetwork() {
    try {
        const res  = await fetch(NET_URL, { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success) return;
        const d = json.data;

        const dl = parseFloat(d.download_speed).toFixed(2);
        const ul = parseFloat(d.upload_speed).toFixed(2);

        document.getElementById('stat-download').innerHTML = dl + ' <small class="fs-6 text-muted fw-normal">Mbps</small>';
        document.getElementById('stat-upload').innerHTML   = ul + ' <small class="fs-6 text-muted fw-normal">Mbps</small>';

        if (d.recorded_at !== lastNetTime) {
            lastNetTime = d.recorded_at;
            // Append baru ke chart
            const timeLabel = d.recorded_at ? d.recorded_at.substring(11, 16) : '—';
            appendTrafficPoint(timeLabel, parseFloat(d.download_speed), parseFloat(d.upload_speed));
            document.getElementById('chart-updated').textContent = 'Diperbarui: ' + d.recorded_at;
            flashCard('card-download');
            flashCard('card-upload');
        }
    } catch (_) {}
}

// ── Init ─────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Build charts dengan data awal dari PHP
    buildCpuChart({{ $initCpu }});
    buildRamChart({{ $initRam }});
    buildTrafficChart(
        {!! json_encode($chartLabels) !!},
        {!! json_encode($chartDownload) !!},
        {!! json_encode($chartUpload) !!}
    );

    // Poll langsung
    pollSystem();
    pollNetwork();

    // Auto-poll setiap 30 detik mengikuti System & Network Traffic
    setInterval(pollSystem,  30000);
    setInterval(pollNetwork, 30000);
});
</script>

@endsection
