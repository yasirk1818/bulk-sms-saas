<?php use App\Core\Csrf; use App\Core\Session; /** @var array $keys */ ?>
<div class="page-header"><div><h1 class="page-title">API Keys</h1></div>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateModal"><i class="bi bi-plus-lg"></i> Generate Key</button></div>

<?php $newKey = Session::getFlash('new_api_key'); $newSecret = Session::getFlash('new_api_secret'); ?>
<?php if ($newKey): ?>
<div class="alert alert-success"><strong>New API Key Generated!</strong> Save these credentials - the secret won't be shown again.<br>
<strong>API Key:</strong> <code><?= $newKey ?></code><br>
<strong>API Secret:</strong> <code><?= $newSecret ?></code></div>
<?php endif; ?>

<div class="card mb-4"><div class="card-header"><h5>Your API Keys</h5></div><div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>API Key</th><th>Status</th><th>Last Used</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($keys as $k): ?><tr>
<td><?= htmlspecialchars($k['name']) ?></td><td><code><?= htmlspecialchars($k['api_key']) ?></code></td>
<td><span class="badge badge-status badge-<?= $k['is_active'] ? 'active' : 'inactive' ?>"><?= $k['is_active'] ? 'Active' : 'Revoked' ?></span></td>
<td class="fs-sm"><?= $k['last_used_at'] ? date('M d H:i', strtotime($k['last_used_at'])) : 'Never' ?></td>
<td><?php if ($k['is_active']): ?><form method="POST" action="/api-keys/<?= $k['id'] ?>/revoke"><?= Csrf::field() ?><button class="btn btn-sm btn-danger">Revoke</button></form><?php endif; ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>

<div class="card"><div class="card-header"><h5><i class="bi bi-code-square me-2"></i>API Documentation</h5></div><div class="card-body">
<h6>Send SMS</h6><pre class="p-3" style="background:var(--bg-secondary);border-radius:8px;color:var(--text-primary)">POST /api/v1/sms/send
Headers: X-API-KEY: your_api_key
Body: {"to": "+1234567890", "message": "Hello!", "sender_id": "MyApp"}</pre>
<h6>Check Balance</h6><pre class="p-3" style="background:var(--bg-secondary);border-radius:8px;color:var(--text-primary)">GET /api/v1/balance
Headers: X-API-KEY: your_api_key</pre>
<h6>Message Status</h6><pre class="p-3" style="background:var(--bg-secondary);border-radius:8px;color:var(--text-primary)">GET /api/v1/sms/{id}/status
Headers: X-API-KEY: your_api_key</pre>
</div></div>

<div class="modal fade" id="generateModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Generate API Key</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="/api-keys/generate"><div class="modal-body"><?= Csrf::field() ?>
<div class="mb-3"><label class="form-label">Key Name</label><input type="text" class="form-control" name="name" value="Default" required></div>
<div class="mb-3"><label class="form-label">IP Whitelist (optional)</label><input type="text" class="form-control" name="ip_whitelist" placeholder="1.2.3.4,5.6.7.8"></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Generate</button></div></form></div></div></div>
