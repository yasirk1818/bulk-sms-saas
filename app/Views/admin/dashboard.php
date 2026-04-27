<?php
/** @var array $stats */
/** @var array $recentUsers */
/** @var array $gateways */
/** @var array $chartData */
/** @var array $revenueData */
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">System overview and analytics</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/users" class="btn btn-secondary"><i class="bi bi-people"></i> Users</a>
        <a href="/admin/gateways" class="btn btn-primary"><i class="bi bi-hdd-network"></i> Gateways</a>
    </div>
</div>

<!-- Key Metrics -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i class="bi bi-people-fill"></i></div>
            <div class="stat-card-value"><?= number_format($stats['total_users']) ?></div>
            <div class="stat-card-label">Total Users</div>
            <span class="stat-card-change up"><i class="bi bi-arrow-up"></i> <?= $stats['active_users'] ?> active</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-icon success"><i class="bi bi-send-check-fill"></i></div>
            <div class="stat-card-value"><?= number_format($stats['total_sms_sent']) ?></div>
            <div class="stat-card-label">Total SMS Sent</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-icon warning"><i class="bi bi-collection-fill"></i></div>
            <div class="stat-card-value"><?= number_format($stats['total_queued']) ?></div>
            <div class="stat-card-label">SMS in Queue</div>
            <span class="stat-card-change"><?= $stats['total_processing'] ?> processing</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-icon info"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-card-value">$<?= number_format((float)$stats['total_revenue'], 2) ?></div>
            <div class="stat-card-label">Total Revenue</div>
        </div>
    </div>
</div>

<!-- Secondary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-sm-4 col-6">
        <div class="stat-card">
            <div class="stat-card-icon success"><i class="bi bi-hdd-network"></i></div>
            <div class="stat-card-value"><?= $stats['active_gateways'] ?>/<?= $stats['total_gateways'] ?></div>
            <div class="stat-card-label">Active Gateways</div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i class="bi bi-megaphone"></i></div>
            <div class="stat-card-value"><?= $stats['active_campaigns'] ?></div>
            <div class="stat-card-label">Active Campaigns</div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
        <div class="stat-card">
            <div class="stat-card-icon danger"><i class="bi bi-x-circle"></i></div>
            <div class="stat-card-value"><?= number_format($stats['total_failed']) ?></div>
            <div class="stat-card-label">Failed SMS</div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
        <div class="stat-card">
            <div class="stat-card-icon warning"><i class="bi bi-ticket"></i></div>
            <div class="stat-card-value"><?= $stats['pending_tickets'] ?></div>
            <div class="stat-card-label">Open Tickets</div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
        <div class="stat-card">
            <div class="stat-card-icon info"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-card-value"><?= $stats['pending_templates'] ?></div>
            <div class="stat-card-label">Pending Templates</div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i class="bi bi-person-badge"></i></div>
            <div class="stat-card-value"><?= $stats['pending_sender_ids'] ?></div>
            <div class="stat-card-label">Pending Sender IDs</div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><h5><i class="bi bi-graph-up me-2"></i>SMS Traffic (7 Days)</h5></div>
            <div class="card-body"><canvas id="adminSmsChart" height="300"></canvas></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h5><i class="bi bi-wallet2 me-2"></i>Revenue (7 Days)</h5></div>
            <div class="card-body"><canvas id="revenueChart" height="300"></canvas></div>
        </div>
    </div>
</div>

<!-- Gateway Status & Recent Users -->
<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-hdd-network me-2"></i>Active Gateways</h5>
                <a href="/admin/gateways" class="btn btn-sm btn-secondary">Manage</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Gateway</th><th>Status</th><th>Sent</th><th>Success Rate</th></tr></thead>
                    <tbody>
                        <?php if (empty($gateways)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No active gateways</td></tr>
                        <?php else: ?>
                            <?php foreach ($gateways as $gw): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($gw['name']) ?></strong></td>
                                    <td><span class="badge badge-status badge-active">Active</span></td>
                                    <td><?= number_format($gw['total_sent']) ?></td>
                                    <td><?= $gw['total_sent'] > 0 ? round(($gw['total_delivered'] / $gw['total_sent']) * 100, 1) : 0 ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-people me-2"></i>Recent Users</h5>
                <a href="/admin/users" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($u['name']) ?></strong>
                                    <div class="fs-xs text-muted"><?= htmlspecialchars($u['email']) ?></div>
                                </td>
                                <td><span class="tag-chip"><?= str_replace('_', ' ', $u['role']) ?></span></td>
                                <td><span class="badge badge-status badge-<?= $u['status'] === 'active' ? 'active' : 'inactive' ?>"><?= ucfirst($u['status']) ?></span></td>
                                <td class="fs-sm text-muted"><?= date('M d', strtotime($u['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = <?= json_encode($chartData) ?>;
    const revenueData = <?= json_encode($revenueData) ?>;

    new Chart(document.getElementById('adminSmsChart'), {
        type: 'bar',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [
                { label: 'Sent', data: chartData.map(d => d.sent), backgroundColor: 'rgba(99,102,241,0.7)', borderRadius: 6 },
                { label: 'Delivered', data: chartData.map(d => d.delivered), backgroundColor: 'rgba(34,197,94,0.7)', borderRadius: 6 },
                { label: 'Failed', data: chartData.map(d => d.failed), backgroundColor: 'rgba(239,68,68,0.7)', borderRadius: 6 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { color: 'rgb(142,142,160)' } } },
            scales: {
                x: { ticks: { color: 'rgb(142,142,160)' }, grid: { color: 'rgba(255,255,255,0.04)' } },
                y: { ticks: { color: 'rgb(142,142,160)' }, grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: revenueData.map(d => d.date),
            datasets: [{ label: 'Revenue ($)', data: revenueData.map(d => d.revenue), borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.1)', tension: 0.4, fill: true }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { color: 'rgb(142,142,160)' } } },
            scales: {
                x: { ticks: { color: 'rgb(142,142,160)' }, grid: { color: 'rgba(255,255,255,0.04)' } },
                y: { ticks: { color: 'rgb(142,142,160)', callback: v => '$' + v }, grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true }
            }
        }
    });
});
</script>
