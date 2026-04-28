<?php /** @var array $campaign @var array $logs */ ?>
<div class="page-header"><div><h1 class="page-title"><?= htmlspecialchars($campaign['name']) ?></h1>
<span class="badge badge-status badge-<?= match($campaign['status']){ 'running'=>'active','completed'=>'delivered','failed'=>'failed',default=>'pending'} ?>"><?= ucfirst($campaign['status']) ?></span></div></div>
<div class="row g-3 mb-4">
<div class="col-md-3"><div class="stat-card"><div class="stat-card-value"><?= number_format($campaign['total_recipients'] ?? 0) ?></div><div class="stat-card-label">Recipients</div></div></div>
<div class="col-md-3"><div class="stat-card"><div class="stat-card-value text-success"><?= number_format($campaign['total_delivered'] ?? 0) ?></div><div class="stat-card-label">Delivered</div></div></div>
<div class="col-md-3"><div class="stat-card"><div class="stat-card-value text-danger"><?= number_format($campaign['total_failed'] ?? 0) ?></div><div class="stat-card-label">Failed</div></div></div>
<div class="col-md-3"><div class="stat-card"><div class="stat-card-value">$<?= number_format((float)($campaign['total_cost'] ?? 0), 2) ?></div><div class="stat-card-label">Total Cost</div></div></div>
</div>
<div class="card mb-4"><div class="card-header"><h5>Message</h5></div><div class="card-body"><p><?= nl2br(htmlspecialchars($campaign['message'])) ?></p></div></div>
<div class="card"><div class="card-header"><h5>Delivery Log</h5></div><div class="table-responsive"><table class="table"><thead><tr><th>Recipient</th><th>Status</th><th>Date</th></tr></thead><tbody>
<?php foreach ($logs as $log): ?><tr><td><code><?= htmlspecialchars($log['recipient']) ?></code></td>
<td><span class="badge badge-status badge-<?= match($log['status']){ 'delivered'=>'delivered','failed'=>'failed',default=>'pending'} ?>"><?= ucfirst($log['status']) ?></span></td>
<td class="fs-xs"><?= date('M d H:i', strtotime($log['created_at'])) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
