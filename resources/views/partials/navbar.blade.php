<nav class="navbar">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <button id="toggleSidebar" class="btn btn-light" type="button" title="Toggle Sidebar"
                aria-label="Toggle sidebar" aria-controls="sidebar" aria-expanded="false">
            <i class="bi bi-list"></i>
        </button>

        <div class="d-flex gap-2 align-items-center ms-auto">
            <button class="btn btn-light" type="button" title="Search" data-bs-toggle="modal" data-bs-target="#searchModal">
                <i class="bi bi-search"></i>
            </button>

            <button id="toggleDark" class="btn btn-light" type="button" title="Dark Mode">
                <i class="bi bi-moon"></i>
            </button>

            <button class="btn btn-light position-relative" type="button" title="Notifications" data-bs-toggle="modal" data-bs-target="#notificationsModal">
                <i class="bi bi-bell"></i>
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" id="notificationCount">3</span>
            </button>

            @auth
            <button class="btn btn-light" type="button" title="Profile" onclick="showProfileModal(event)">
                <i class="bi bi-person-circle"></i>
            </button>
            @endauth
        </div>
    </div>
</nav>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-search me-2"></i>Search</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control form-control-lg mb-3" id="searchInput" placeholder="Cari...">
                <div id="searchResults">
                    <p class="text-muted text-center">Mulai ketik untuk mencari...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-circle me-2"></i>Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle text-success" style="font-size: 80px;"></i>
                    <h4 class="mt-3">{{ Auth::user()->name }}</h4>
                    <p class="text-muted">{{ Auth::user()->email }}</p>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-info-circle me-2"></i>Account Info</h6>
                                <small class="text-muted d-block">Email Verified:</small>
                                <span class="badge bg-success">{{ Auth::user()->email_verified_at ? 'Yes' : 'No' }}</span>
                                <div class="mt-3">
                                    <small class="text-muted d-block">Member Since:</small>
                                    <span>{{ Auth::user()->created_at->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-shield-check me-2"></i>Status</h6>
                                <small class="text-muted d-block">Account Status:</small>
                                <span class="badge bg-info">Active</span>
                                <div class="mt-3">
                                    <small class="text-muted d-block">Last Updated:</small>
                                    <span>{{ Auth::user()->updated_at->format('d M Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary w-100">
                        <i class="bi bi-pencil me-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notifications Modal -->
<div class="modal fade" id="notificationsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bell me-2"></i>Notifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                <div class="list-group list-group-flush" id="notificationsList">
                    <div class="list-group-item list-group-item-action py-3" onclick="goToPage('/system')">
                        <div class="d-flex gap-2">
                            <i class="bi bi-cpu text-info mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong>System Alert</strong>
                                    <span class="badge bg-danger">New</span>
                                </div>
                                <p class="mb-1 small">CPU Load mencapai 85%. Segera lakukan pengecekan sistem.</p>
                                <small class="text-muted">5 menit yang lalu</small>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item list-group-item-action py-3" onclick="goToPage('/network')">
                        <div class="d-flex gap-2">
                            <i class="bi bi-wifi text-success mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong>Network Update</strong>
                                    <span class="badge bg-danger">New</span>
                                </div>
                                <p class="mb-1 small">Network traffic melebihi threshold. Bandwidth usage: 78%</p>
                                <small class="text-muted">15 menit yang lalu</small>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item list-group-item-action py-3" onclick="goToPage('/system')">
                        <div class="d-flex gap-2">
                            <i class="bi bi-database text-warning mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong>Disk Space Warning</strong>
                                    <span class="badge bg-danger">New</span>
                                </div>
                                <p class="mb-1 small">Disk space usage mencapai 90%. Silakan bersihkan storage.</p>
                                <small class="text-muted">1 jam yang lalu</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm btn-primary" id="clearNotifications">
                    <i class="bi bi-trash me-1"></i>Clear All
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger bg-opacity-10">
                <h5 class="modal-title text-danger"><i class="bi bi-exclamation-circle me-2"></i>Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin keluar dari aplikasi ini?</p>
                <p class="text-muted mb-0"><small>Anda dapat login kembali kapan saja menggunakan akun Anda.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">Yes, Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Modal functions
function showProfileModal(event) {
    event.preventDefault();
    new bootstrap.Modal(document.getElementById('profileModal')).show();
}

function showLogoutModal(event) {
    event.preventDefault();
    new bootstrap.Modal(document.getElementById('logoutModal')).show();
}

function goToPage(url) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('notificationsModal'));
    if (modal) modal.hide();
    setTimeout(() => window.location.href = url, 300);
}

function goToSearchPage(url) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('searchModal'));
    if (modal) modal.hide();
    setTimeout(() => window.location.href = url, 300);
}

document.addEventListener('DOMContentLoaded', function() {
    // Search
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const query = this.value.toLowerCase().trim();
            const resultsDiv = document.getElementById('searchResults');

            if (query.length === 0) {
                resultsDiv.innerHTML = '<p class="text-muted text-center">Mulai ketik untuk mencari...</p>';
                return;
            }

            const items = [
                { type: 'System', name: 'CPU Monitoring', icon: 'cpu', url: '/system' },
                { type: 'System', name: 'Memory Usage', icon: 'memory', url: '/system' },
                { type: 'Network', name: 'Network Traffic', icon: 'wifi', url: '/network' },
                { type: 'Network', name: 'Bandwidth Monitor', icon: 'graph-up', url: '/network' },
                { type: 'Settings', name: 'System Settings', icon: 'gear', url: '/settings' }
            ];

            const filtered = items.filter(item => 
                item.name.toLowerCase().includes(query) || 
                item.type.toLowerCase().includes(query)
            );

            if (filtered.length === 0) {
                resultsDiv.innerHTML = '<p class="text-muted text-center">Tidak ada hasil ditemukan.</p>';
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            filtered.forEach(item => {
                html += '<div class="list-group-item list-group-item-action d-flex gap-2" onclick="goToSearchPage(\'' + item.url + '\')">';
                html += '<i class="bi bi-' + item.icon + '"></i>';
                html += '<div><strong>' + item.name + '</strong><small class="text-muted d-block">' + item.type + '</small></div>';
                html += '</div>';
            });
            html += '</div>';
            resultsDiv.innerHTML = html;
        });
    }

    // Clear notifications
    const clearBtn = document.getElementById('clearNotifications');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            document.getElementById('notificationsList').innerHTML = '<p class="text-muted text-center py-3">No notifications</p>';
            document.getElementById('notificationCount').textContent = '0';
        });
    }

    // Dark mode & sidebar toggle dikelola di layouts/app.blade.php (satu pemilik handler)
});
</script>
