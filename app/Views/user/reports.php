<?php /** @var array $stats @var float $totalCost */ ?>
<div class="page-header"><div><h1 class="page-title">Reports</h1></div>
<a href="/reports/export" class="btn btn-secondary"><i class="bi bi-download"></i> Export CSV</a></div>
<div class="card mb-4"><div class="card-body"><form method="GET" class="row g-3 align-items-end">
<div class="col-md-3"><label class="form-label">From</label><input type="date" class="form-control" name="from" value="<?= $dateFrom ?>"></div>
<div class="col-md-3"><label class="form-label">To</label><input type="date" class="form-control" name="to" value="<?= $dateTo ?>"></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Apply</button></div></form></div></div>
<div class="row g-3 mb-4">
<div class="col-md-3"><div class="stat-card"><div class="stat-card-value"><?= number_format($stats['sent']) ?></div><div class="stat-card-label">SMS Sent</div></div></div>
<div class="col-md-3"><div class="stat-card"><div class="stat-card-value text-success"><?= number_format($stats['delivered']) ?></div><div class="stat-card-label">Delivered</div></div></div>
<div class="col-md-3"><div class="stat-card"><div class="stat-card-value text-danger"><?= number_format($stats['failed']) ?></div><div class="stat-card-label">Failed</div></div></div>
<div class="col-md-3"><div class="stat-card"><div class="stat-card-value"><?= $stats['delivery_rate'] ?>%</div><div class="stat-card-label">Delivery Rate</div></div></div>
</div>
<div class="card"><div class="card-body text-center"><p>Total Cost: <strong>$<?= number_format((float)$totalCost, 2) ?></strong></p></div></div>
