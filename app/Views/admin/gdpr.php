<?php use App\Core\Csrf; /** @var array $requests @var array $consentLogs */ ?>
<div class="page-header"><div><h1 class="page-title">GDPR Management</h1></div></div>
<div class="card mb-4"><div class="card-header"><h5>Data Requests</h5></div><div class="table-responsive"><table class="table"><thead><tr><th>User</th><th>Type</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>
<?php if (empty($requests)): ?><tr><td colspan="5" class="text-center text-muted py-3">No pending requests</td></tr>
<?php else: foreach ($requests as $r): ?><tr>
<td><?= htmlspecialchars($r['user_name']) ?> <div class="fs-xs text-muted"><?= $r['user_email'] ?></div></td>
<td><span class="tag-chip"><?= ucfirst($r['request_type']) ?></span></td>
<td><span class="badge badge-status badge-<?= $r['status'] === 'completed' ? 'delivered' : 'pending' ?>"><?= ucfirst($r['status']) ?></span></td>
<td class="fs-sm"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
<td><?php if ($r['status'] === 'pending'): ?><form method="POST" action="/admin/gdpr/<?= $r['id'] ?>/process"><?= Csrf::field() ?><button class="btn btn-sm btn-primary">Process</button></form><?php endif; ?></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>
<div class="card"><div class="card-header"><h5>Recent Consent Logs</h5></div><div class="table-responsive"><table class="table"><thead><tr><th>User</th><th>Action</th><th>IP</th><th>Date</th></tr></thead><tbody>
<?php foreach ($consentLogs as $c): ?><tr><td><?= htmlspecialchars($c['user_name']) ?></td><td><?= $c['consent_type'] ?? $c['action'] ?? '-' ?></td><td class="fs-xs"><code><?= $c['ip_address'] ?? '-' ?></code></td><td class="fs-xs"><?= date('M d H:i', strtotime($c['created_at'])) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
