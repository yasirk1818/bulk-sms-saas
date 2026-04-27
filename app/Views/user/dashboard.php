<?php
/** @var array $stats */
/** @var array $recentSms */
/** @var array $chartData */
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, <?= htmlspecialchars(\App\Core\Session::getUser()['name'] ?? 'User') ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="/sms/send" class="btn btn-primary"><i class="bi bi-send-fill"></i> Send SMS</a>
        <a href="/campaigns/create" class="btn btn-secondary"><i class="bi bi-megaphone"></i> New Campaign</a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i class="bi bi-wallet2"></i></div>
            <div class="stat-card-value"><?= number_format((float)$stats['balance'], 0) ?></div>
            <div class="stat-card-label">SMS Balance</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-icon success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-card-value"><?= number_format($stats['total_delivered']) ?></div>
            <div class="stat-card-label">Delivered</div>
            <span class="stat-card-change up"><i class="bi bi-arrow-up"></i> Lifetime</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-icon warning"><i class="bi bi-clock"></i></div>
            <div class="stat-card-value"><?= number_format($stats['total_queued']) ?></div>
            <div class="stat-card-label">In Queue</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-icon danger"><i class="bi bi-x-circle"></i></div>
            <div class="stat-card-value"><?= number_format($stats['total_failed']) ?></div>
            <div class="stat-card-label">Failed</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-icon info"><i class="bi bi-send"></i></div>
            <div class="stat-card-value"><?= number_format($stats['total_sent']) ?></div>
            <div class="stat-card-label">Total Sent</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i class="bi bi-people"></i></div>
            <div class="stat-card-value"><?= number_format($stats['total_contacts']) ?></div>
            <div class="stat-card-label">Contacts</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-icon success"><i class="bi bi-megaphone"></i></div>
            <div class="stat-card-value"><?= number_format($stats['total_campaigns']) ?></div>
            <div class="stat-card-label">Campaigns</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-icon warning"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-card-value"><?= $stats['total_sent'] > 0 ? round(($stats['total_delivered'] / $stats['total_sent']) * 100, 1) : 0 ?>%</div>
            <div class="stat-card-label">Delivery Rate</div>
        </div>
    </div>
</div>

<!-- Charts & Recent Activity -->
<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-graph-up me-2"></i>SMS Activity (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="smsChart" height="280"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart me-2"></i>Delivery Summary</h5>
            </div>
            <div class="card-body">
                <canvas id="deliveryChart" height="280"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent SMS -->
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-clock-history me-2"></i>Recent SMS</h5>
        <a href="/sms/history" class="btn btn-sm btn-secondary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Cost</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentSms)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No SMS sent yet. <a href="/sms/send">Send your first SMS</a></td></tr>
                <?php else: ?>
                    <?php foreach ($recentSms as $sms): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($sms['recipient']) ?></code></td>
                            <td class="truncate" style="max-width:250px"><?= htmlspecialchars(substr($sms['message'], 0, 50)) ?>...</td>
                            <td>
                                <span class="badge badge-status badge-<?= match($sms['status']) { 'delivered' => 'delivered', 'sent' => 'active', 'failed' => 'failed', default => 'pending' } ?>">
                                    <?= ucfirst($sms['status']) ?>
                                </span>
                            </td>
                            <td><?= number_format((float)$sms['cost'], 4) ?></td>
                            <td class="fs-sm text-muted"><?= date('M d, H:i', strtotime($sms['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = <?= json_encode($chartData) ?>;
    const labels = chartData.map(d => d.date);
    const sentData = chartData.map(d => d.sent);
    const deliveredData = chartData.map(d => d.delivered);

    // Line Chart
    const ctx = document.getElementById('smsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Sent', data: sentData, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', tension: 0.4, fill: true },
                    { label: 'Delivered', data: deliveredData, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.1)', tension: 0.4, fill: true }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: 'rgb(142,142,160)' } } },
                scales: {
                    x: { ticks: { color: 'rgb(142,142,160)' }, grid: { color: 'rgba(255,255,255,0.04)' } },
                    y: { ticks: { color: 'rgb(142,142,160)' }, grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true }
                }
            }
        });
    }

    // Doughnut Chart
    const ctx2 = document.getElementById('deliveryChart');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Delivered', 'Failed', 'Queued'],
                datasets: [{
                    data: [<?= $stats['total_delivered'] ?>, <?= $stats['total_failed'] ?>, <?= $stats['total_queued'] ?>],
                    backgroundColor: ['#22c55e', '#ef4444', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { position: 'bottom', labels: { color: 'rgb(142,142,160)', padding: 16 } } }
            }
        });
    }
});
</script>
