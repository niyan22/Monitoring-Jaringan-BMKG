@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="settings-container">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Settings</h2>
        <p class="text-muted">Kelola pengaturan aplikasi monitoring jaringan Anda</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="list-group sticky-top">
                <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="list" role="tab">
                    <i class="bi bi-gear me-2"></i>General Settings
                </a>
                <a href="#network" class="list-group-item list-group-item-action" data-bs-toggle="list" role="tab">
                    <i class="bi bi-wifi me-2"></i>Network Configuration
                </a>
                <a href="#monitoring" class="list-group-item list-group-item-action" data-bs-toggle="list" role="tab">
                    <i class="bi bi-graph-up me-2"></i>Monitoring Preferences
                </a>
                <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="list" role="tab">
                    <i class="bi bi-bell me-2"></i>Notifications
                </a>
                <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list" role="tab">
                    <i class="bi bi-shield-lock me-2"></i>Security
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="col-lg-9">
            <form action="{{ route('settings.saveAll') }}" method="POST">
                @csrf
                <div class="tab-content">
                    <!-- General Settings -->
                    <div class="tab-pane fade show active" id="general">
                        <div class="card">
                            <div class="card-header bg-light border-0">
                                <h6 class="mb-0 fw-bold">General Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Application Name</label>
                                    <input type="text" class="form-control" name="app_name" value="{{ \App\Models\Setting::getSetting('app_name', 'BMKG Network Monitoring') }}" placeholder="Enter application name">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Location</label>
                                    <input type="text" class="form-control" name="location" value="{{ \App\Models\Setting::getSetting('location', 'BMKG Riau') }}" placeholder="Enter location">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Timezone</label>
                                    <select class="form-select" name="timezone">
                                        <option value="Asia/Jakarta" {{ \App\Models\Setting::getSetting('timezone', 'Asia/Jakarta') === 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (UTC+7)</option>
                                        <option value="Asia/Bangkok" {{ \App\Models\Setting::getSetting('timezone') === 'Asia/Bangkok' ? 'selected' : '' }}>Asia/Bangkok (UTC+7)</option>
                                        <option value="Asia/Singapore" {{ \App\Models\Setting::getSetting('timezone') === 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (UTC+8)</option>
                                        <option value="Asia/Tokyo" {{ \App\Models\Setting::getSetting('timezone') === 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (UTC+9)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Language</label>
                                    <select class="form-select" name="language">
                                        <option value="id" {{ \App\Models\Setting::getSetting('language', 'id') === 'id' ? 'selected' : '' }}>Bahasa Indonesia</option>
                                        <option value="en" {{ \App\Models\Setting::getSetting('language') === 'en' ? 'selected' : '' }}>English</option>
                                        <option value="zh" {{ \App\Models\Setting::getSetting('language') === 'zh' ? 'selected' : '' }}>中文</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Theme</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="theme" id="light" value="light" {{ \App\Models\Setting::getSetting('theme', 'light') === 'light' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-secondary" for="light">
                                            <i class="bi bi-sun me-2"></i>Light
                                        </label>

                                        <input type="radio" class="btn-check" name="theme" id="dark" value="dark" {{ \App\Models\Setting::getSetting('theme') === 'dark' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-secondary" for="dark">
                                            <i class="bi bi-moon me-2"></i>Dark
                                        </label>

                                        <input type="radio" class="btn-check" name="theme" id="auto" value="auto" {{ \App\Models\Setting::getSetting('theme') === 'auto' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-secondary" for="auto">
                                            <i class="bi bi-circle-half me-2"></i>Auto
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Network Configuration -->
                    <div class="tab-pane fade" id="network">
                        <div class="card">
                            <div class="card-header bg-light border-0">
                                <h6 class="mb-0 fw-bold">Network Configuration</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="fw-bold mb-3">Monitoring Methods</h6>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="ping" name="ping_enabled" {{ \App\Models\Setting::getSetting('ping_enabled', 'true') === 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ping">
                                            <strong>Ping (ICMP)</strong> - Monitor device availability
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="snmp" name="snmp_enabled" {{ \App\Models\Setting::getSetting('snmp_enabled', 'true') === 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="snmp">
                                            <strong>SNMP</strong> - Get detailed network statistics
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="http" name="http_enabled" {{ \App\Models\Setting::getSetting('http_enabled') === 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="http">
                                            <strong>HTTP/HTTPS</strong> - Monitor web services
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Polling Interval (seconds)</label>
                                    <input type="number" class="form-control" name="polling_interval" value="{{ \App\Models\Setting::getSetting('polling_interval', '30') }}" min="10" max="3600">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Timeout (seconds)</label>
                                    <input type="number" class="form-control" name="timeout" value="{{ \App\Models\Setting::getSetting('timeout', '10') }}" min="1" max="60">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Retries</label>
                                    <input type="number" class="form-control" name="retries" value="{{ \App\Models\Setting::getSetting('retries', '3') }}" min="1" max="10">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monitoring Preferences -->
                    <div class="tab-pane fade" id="monitoring">
                        <div class="card">
                            <div class="card-header bg-light border-0">
                                <h6 class="mb-0 fw-bold">Monitoring Preferences</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="fw-bold mb-3">Data Retention</h6>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="data_retention" id="retention7" value="7" {{ \App\Models\Setting::getSetting('data_retention', '7') === '7' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="retention7">
                                            7 days
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="data_retention" id="retention30" value="30" {{ \App\Models\Setting::getSetting('data_retention') === '30' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="retention30">
                                            30 days
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="data_retention" id="retention90" value="90" {{ \App\Models\Setting::getSetting('data_retention') === '90' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="retention90">
                                            90 days
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <h6 class="fw-bold mb-3">Graph Display</h6>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="realtime" name="realtime_graphs" {{ \App\Models\Setting::getSetting('realtime_graphs', 'true') === 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="realtime">
                                            Real-time graphs
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="trends" name="show_trends" {{ \App\Models\Setting::getSetting('show_trends', 'true') === 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="trends">
                                            Show trends
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="tab-pane fade" id="notifications">
                        <div class="card">
                            <div class="card-header bg-light border-0">
                                <h6 class="mb-0 fw-bold">Notification Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="fw-bold mb-3">Alert Methods</h6>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="email" name="email_notifications" {{ \App\Models\Setting::getSetting('email_notifications', 'true') === 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email">
                                            <i class="bi bi-envelope me-2"></i>Email Notifications
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="sms" name="sms_alerts" {{ \App\Models\Setting::getSetting('sms_alerts', 'true') === 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sms">
                                            <i class="bi bi-chat-dots me-2"></i>SMS Alerts
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="telegram" name="telegram_alerts" {{ \App\Models\Setting::getSetting('telegram_alerts') === 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="telegram">
                                            <i class="bi bi-send me-2"></i>Telegram Alerts
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Alert Email</label>
                                    <input type="email" class="form-control" name="alert_email" value="{{ \App\Models\Setting::getSetting('alert_email', 'admin@bmkg.local') }}" placeholder="Enter alert email">
                                </div>

                                <!-- Telegram Configuration -->
                                <div class="mb-3 border-top pt-3">
                                    <h6 class="fw-bold mb-3">Telegram Configuration</h6>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Bot Token</label>
                                        <input type="password" class="form-control" name="telegram_token" value="{{ \App\Models\Setting::getSetting('telegram_token', '') }}" placeholder="Enter Telegram Bot Token">
                                        <small class="text-muted">Dapatkan token dari BotFather di Telegram</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Chat ID</label>
                                        <input type="text" class="form-control" name="telegram_chat_id" value="{{ \App\Models\Setting::getSetting('telegram_chat_id', '') }}" placeholder="Enter Telegram Chat ID">
                                        <small class="text-muted">Chat ID tujuan untuk menerima notifikasi</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="tab-pane fade" id="security">
                        <div class="card">
                            <div class="card-header bg-light border-0">
                                <h6 class="mb-0 fw-bold">Security Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="fw-bold mb-3">Two-Factor Authentication</h6>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="twofa" name="two_factor_auth" {{ \App\Models\Setting::getSetting('two_factor_auth') === 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="twofa">
                                            Enable 2FA
                                        </label>
                                    </div>
                                </div>

                                <div class="alert alert-info" role="alert">
                                    <i class="bi bi-info-circle me-2"></i>Last login: {{ auth()->user()->updated_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Save All Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .settings-container {
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .list-group-item {
        border: none;
        border-left: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
        border-left-color: #16a34a;
        transform: translateX(5px);
    }

    .list-group-item.active {
        background-color: #e7f3ed;
        color: #16a34a;
        border-left-color: #16a34a;
    }

    body.dark-mode .list-group-item {
        background-color: #2a2a2a;
        color: #e0e0e0;
    }

    body.dark-mode .list-group-item:hover {
        background-color: #374151;
    }

    body.dark-mode .list-group-item.active {
        background-color: #1f4d3a;
        color: #4ade80;
    }

    .card-header {
        background-color: rgba(0, 0, 0, 0.02) !important;
    }
</style>

<script>
// Ingat tab yang sedang dibuka agar setelah "Save" halaman tidak kembali ke General Settings.
document.addEventListener('DOMContentLoaded', () => {
    const KEY = 'settingsActiveTab';
    const tabs = document.querySelectorAll('.list-group-item[data-bs-toggle="list"]');

    tabs.forEach(tab => tab.addEventListener('shown.bs.tab', () => {
        try { sessionStorage.setItem(KEY, tab.getAttribute('href')); } catch (e) {}
    }));

    let saved = null;
    try { saved = sessionStorage.getItem(KEY); } catch (e) {}
    const target = saved ? document.querySelector('.list-group-item[href="' + saved + '"]') : null;
    if (target) bootstrap.Tab.getOrCreateInstance(target).show();
});
</script>
@endsection