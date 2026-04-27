<?php use App\Core\Csrf; /** @var array $items @var array $summary */ ?>
<div class="page-header"><div><h1 class="page-title">SMS Queue</h1><p class="page-subtitle">Monitor and manage the SMS queue</p></div>
<div class="d-flex gap-2">
    <form method="POST" action="/admin/queue/retry-all" style="display:inline"><?= Csrf::field() ?><button class="btn btn-warning"><i class="bi bi-arrow-clockwise"></i> Retry Failed</button></form>
    <form method="POST" action="/admin/queue/pause" style="display:inline"><?= Csrf::field() ?><button class="btn btn-secondary"><i class="bi bi-pause"></i> Pause</button></form>
    <form method="POST" action="/admin/queue/resume" style="display:inline"><?= Csrf::field() ?><button class="btn btn-success"><i class="bi bi-play"></i> Resume</button></form>
</div></div>

<div class="row g-3 mb-4">
    <div class="col-md-2"><div class="stat-card"><div class="stat-card-value"><?= $summary['queued'] ?? 0 ?></div><div class="stat-card-label">Queued</div></div></div>
    <div class="col-md-2"><div class="stat-card"><div class="stat-card-value"><?= $summary['processing'] ?? 0 ?></div><div class="stat-card-label">Processing</div></div></div>
    <div class="col-md-2"><div class="stat-card"><div class="stat-card-value"><?= $summary['sent'] ?? 0 ?></div><div class="stat-card-label">Sent</div></div></div>
    <div class="col-md-2"><div class="stat-card"><div class="stat-card-value"><?= $summary['delivered'] ?? 0 ?></div><div class="stat-card-label">Delivered</div></div></div>
    <div class="col-md-2"><div class="stat-card"><div class="stat-card-value"><?= $summary['failed'] ?? 0 ?></div><div class="stat-card-label">Failed</div></div></div>
    <div class="col-md-2"><div class="stat-card"><div class="stat-card-value"><?= array_sum($summary) ?></div><div class="stat-card-label">Total</div></div></div>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="d-flex gap-3 align-items-end">
        <select class="form-select" name="status" style="width:200px"><option value="">All Status</option><?php foreach(['queued','processing','sent','delivered','failed'] as $s): ?><option value="<?= $s ?>" <?= ($status ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <button class="btn btn-primary"><i class="bi bi-filter"></i> Filter</button>
    </form>
</div></div>

<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>Recipient</th><th>User</th><th>Gateway</th><th>Priority</th><th>Status</th><th>Retry</th><th>Date</th><th>Actions</th></tr></thead><tbody>
<?php if (empty($items)): ?><tr><td colspan="8" class="text-center text-muted py-4">Queue is empty</td></tr>
<?php else: foreach ($items as $item): ?>
<tr>
    <td><code><?= htmlspecialchars($item['recipient']) ?></code></td>
    <td class="fs-sm"><?= htmlspecialchars($item['user_name'] ?? '-') ?></td>
    <td class="fs-sm"><?= htmlspecialchars($item['gateway_name'] ?? '-') ?></td>
    <td><?= $item['priority'] ?></td>
    <td><span class="badge badge-status badge-<?= match($item['status']) { 'delivered' => 'delivered', 'sent' => 'active', 'failed' => 'failed', 'processing' => 'pending', default => 'queued' } ?>"><?= ucfirst($item['status']) ?></span></td>
    <td><?= $item['retry_count'] ?? 0 ?></td>
    <td class="fs-xs"><?= date('M d H:i', strtotime($item['created_at'])) ?></td>
    <td><?php if ($item['status'] === 'failed'): ?><form method="POST" action="/admin/queue/<?= $item['id'] ?>/retry" style="display:inline"><?= Csrf::field() ?><button class="btn btn-sm btn-warning"><i class="bi bi-arrow-clockwise"></i></button></form><?php endif; ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody></table></div>
<?php if (($pagination['last_page'] ?? 1) > 1): ?>
<div class="card-footer d-flex justify-content-between"><span class="text-muted fs-sm"><?= $pagination['from'] ?>-<?= $pagination['to'] ?> of <?= $pagination['total'] ?></span>
<nav><ul class="pagination mb-0"><?php for($i=1;$i<=$pagination['last_page'];$i++): ?><li class="page-item <?= $i===$pagination['current_page']?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&status=<?= $status ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav></div>
<?php endif; ?></div>
