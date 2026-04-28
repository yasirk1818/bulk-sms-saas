<?php /** @var array $logs @var array $pagination */ ?>
<div class="page-header"><div><h1 class="page-title">Audit Logs</h1></div>
<div class="d-flex gap-2"><a href="/admin/logs/audit" class="btn btn-primary btn-sm">Audit</a><a href="/admin/logs/system" class="btn btn-secondary btn-sm">System</a><a href="/admin/logs/api" class="btn btn-secondary btn-sm">API</a></div></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>User</th><th>Action</th><th>IP</th><th>Details</th><th>Date</th></tr></thead><tbody>
<?php foreach ($logs as $log): ?><tr>
<td><?= htmlspecialchars($log['user_name'] ?? 'System') ?></td><td><code><?= htmlspecialchars($log['action']) ?></code></td>
<td class="fs-xs"><code><?= $log['ip_address'] ?? '-' ?></code></td><td class="fs-xs truncate" style="max-width:200px"><?= htmlspecialchars($log['details'] ?? '') ?></td>
<td class="fs-xs"><?= date('M d H:i:s', strtotime($log['created_at'])) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php if ($pagination['last_page'] > 1): ?><div class="card-footer"><nav><ul class="pagination mb-0"><?php for($i=1;$i<=$pagination['last_page'];$i++): ?><li class="page-item <?= $i===$pagination['current_page']?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav></div><?php endif; ?></div>
