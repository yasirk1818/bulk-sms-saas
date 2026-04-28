<?php /** @var array $logs @var array $pagination */ ?>
<div class="page-header"><div><h1 class="page-title">API Logs</h1></div>
<div class="d-flex gap-2"><a href="/admin/logs/audit" class="btn btn-secondary btn-sm">Audit</a><a href="/admin/logs/system" class="btn btn-secondary btn-sm">System</a><a href="/admin/logs/api" class="btn btn-primary btn-sm">API</a></div></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>Method</th><th>Endpoint</th><th>Status</th><th>IP</th><th>Response Time</th><th>Date</th></tr></thead><tbody>
<?php foreach ($logs as $log): ?><tr>
<td><span class="tag-chip"><?= $log['method'] ?? 'GET' ?></span></td><td class="fs-sm"><code><?= htmlspecialchars($log['endpoint'] ?? '') ?></code></td>
<td><span class="badge bg-<?= ($log['response_code'] ?? 200) < 400 ? 'success' : 'danger' ?>"><?= $log['response_code'] ?? '-' ?></span></td>
<td class="fs-xs"><code><?= $log['ip_address'] ?? '-' ?></code></td>
<td class="fs-xs"><?= $log['response_time'] ?? '-' ?>ms</td>
<td class="fs-xs"><?= date('M d H:i:s', strtotime($log['created_at'])) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
