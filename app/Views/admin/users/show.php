<?php use App\Core\Csrf; /** @var array $user @var array $loginLogs @var array $smsStats */ ?>
<div class="page-header"><div><h1 class="page-title"><?= htmlspecialchars($user['name']) ?></h1><p class="page-subtitle"><?= htmlspecialchars($user['email']) ?></p></div>
<div class="d-flex gap-2"><a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit</a></div></div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card"><div class="stat-card-icon primary"><i class="bi bi-wallet2"></i></div><div class="stat-card-value"><?= number_format((float)$user['sms_balance'], 0) ?></div><div class="stat-card-label">Balance</div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="stat-card-icon success"><i class="bi bi-send"></i></div><div class="stat-card-value"><?= number_format($smsStats['total']) ?></div><div class="stat-card-label">Total Sent</div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="stat-card-icon info"><i class="bi bi-check"></i></div><div class="stat-card-value"><?= number_format($smsStats['delivered']) ?></div><div class="stat-card-label">Delivered</div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="stat-card-icon danger"><i class="bi bi-x"></i></div><div class="stat-card-value"><?= number_format($smsStats['failed']) ?></div><div class="stat-card-label">Failed</div></div></div>
</div>

<div class="row g-3">
    <div class="col-md-6"><div class="card"><div class="card-header"><h5>User Details</h5></div><div class="card-body">
        <table class="table table-sm"><tr><td class="text-muted">Role</td><td><span class="tag-chip"><?= str_replace('_',' ',$user['role']) ?></span></td></tr>
        <tr><td class="text-muted">Status</td><td><span class="badge badge-status badge-<?= $user['status'] === 'active' ? 'active' : 'inactive' ?>"><?= ucfirst($user['status']) ?></span></td></tr>
        <tr><td class="text-muted">Phone</td><td><?= htmlspecialchars($user['phone'] ?? '-') ?></td></tr>
        <tr><td class="text-muted">Last Login</td><td><?= $user['last_login_at'] ? date('M d, Y H:i', strtotime($user['last_login_at'])) : 'Never' ?></td></tr>
        <tr><td class="text-muted">Last Login IP</td><td><code><?= $user['last_login_ip'] ?? '-' ?></code></td></tr>
        <tr><td class="text-muted">Joined</td><td><?= date('M d, Y', strtotime($user['created_at'])) ?></td></tr></table>
    </div></div>

    <div class="card mt-3"><div class="card-header"><h5>Add Credits</h5></div><div class="card-body">
        <form method="POST" action="/admin/users/<?= $user['id'] ?>/add-credits" class="d-flex gap-2">
            <?= Csrf::field() ?>
            <input type="number" class="form-control" name="amount" placeholder="Amount" min="1" step="0.01" required>
            <button class="btn btn-primary"><i class="bi bi-plus"></i> Add</button>
        </form>
    </div></div></div>

    <div class="col-md-6"><div class="card"><div class="card-header"><h5>Login History</h5></div>
    <div class="table-responsive"><table class="table table-sm"><thead><tr><th>IP</th><th>Device</th><th>Status</th><th>Date</th></tr></thead><tbody>
    <?php foreach ($loginLogs as $log): ?><tr><td class="fs-xs"><code><?= $log['ip_address'] ?></code></td><td class="fs-xs"><?= $log['browser'] ?>/<?= $log['os'] ?></td><td><span class="badge badge-status badge-<?= $log['status'] === 'success' ? 'delivered' : 'failed' ?>"><?= $log['status'] ?></span></td><td class="fs-xs"><?= date('M d H:i', strtotime($log['created_at'])) ?></td></tr><?php endforeach; ?>
    </tbody></table></div></div></div>
</div>
