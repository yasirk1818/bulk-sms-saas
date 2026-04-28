<?php /** @var array $stats @var array $topUsers @var array $topGateways */ ?>
<div class="page-header"><div><h1 class="page-title">Reports</h1></div>
<a href="/admin/reports/export?type=sms&from=<?= $dateFrom ?>&to=<?= $dateTo ?>" class="btn btn-secondary"><i class="bi bi-download"></i> Export CSV</a></div>
<div class="card mb-4"><div class="card-body"><form method="GET" class="row g-3 align-items-end">
<div class="col-md-3"><label class="form-label">From</label><input type="date" class="form-control" name="from" value="<?= $dateFrom ?>"></div>
<div class="col-md-3"><label class="form-label">To</label><input type="date" class="form-control" name="to" value="<?= $dateTo ?>"></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Apply</button></div></form></div></div>
<div class="row g-3 mb-4">
<div class="col-md-3"><div class="stat-card"><div class="stat-card-value"><?= number_format($stats['total_sent']) ?></div><div class="stat-card-label">Total Sent</div></div></div>
<div class="col-md-3"><div class="stat-card"><div class="stat-card-value"><?= number_format($stats['total_delivered']) ?></div><div class="stat-card-label">Delivered</div></div></div>
<div class="col-md-3"><div class="stat-card"><div class="stat-card-value"><?= number_format($stats['total_failed']) ?></div><div class="stat-card-label">Failed</div></div></div>
<div class="col-md-3"><div class="stat-card"><div class="stat-card-value">$<?= number_format((float)$stats['total_revenue'], 2) ?></div><div class="stat-card-label">Revenue</div></div></div>
</div>
<div class="row g-3"><div class="col-md-6"><div class="card"><div class="card-header"><h5>Top Users</h5></div><div class="table-responsive"><table class="table"><thead><tr><th>User</th><th>SMS</th><th>Cost</th></tr></thead><tbody>
<?php foreach ($topUsers as $u): ?><tr><td><?= htmlspecialchars($u['name']) ?><div class="fs-xs text-muted"><?= $u['email'] ?></div></td><td><?= number_format($u['sms_count']) ?></td><td>$<?= number_format((float)$u['total_cost'], 2) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div></div>
<div class="col-md-6"><div class="card"><div class="card-header"><h5>Gateway Performance</h5></div><div class="table-responsive"><table class="table"><thead><tr><th>Gateway</th><th>Sent</th><th>Delivered</th><th>Rate</th></tr></thead><tbody>
<?php foreach ($topGateways as $g): ?><tr><td><?= htmlspecialchars($g['name']) ?></td><td><?= number_format($g['sms_count']) ?></td><td><?= number_format($g['delivered']) ?></td><td><?= $g['sms_count'] > 0 ? round(($g['delivered']/$g['sms_count'])*100,1) : 0 ?>%</td></tr><?php endforeach; ?>
</tbody></table></div></div></div></div>
