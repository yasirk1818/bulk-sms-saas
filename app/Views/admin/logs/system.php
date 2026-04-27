<?php /** @var array $logs @var array $pagination */ ?>
<div class="page-header"><div><h1 class="page-title">System Logs</h1></div>
<div class="d-flex gap-2"><a href="/admin/logs/audit" class="btn btn-secondary btn-sm">Audit</a><a href="/admin/logs/system" class="btn btn-primary btn-sm">System</a><a href="/admin/logs/api" class="btn btn-secondary btn-sm">API</a></div></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>Level</th><th>Channel</th><th>Message</th><th>Date</th></tr></thead><tbody>
<?php foreach ($logs as $log): ?><tr>
<td><span class="badge bg-<?= $log['level'] === 'error' ? 'danger' : ($log['level'] === 'warning' ? 'warning' : 'info') ?>"><?= $log['level'] ?></span></td>
<td><?= htmlspecialchars($log['channel'] ?? '') ?></td><td class="truncate" style="max-width:400px"><?= htmlspecialchars($log['message']) ?></td>
<td class="fs-xs"><?= date('M d H:i:s', strtotime($log['created_at'])) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
