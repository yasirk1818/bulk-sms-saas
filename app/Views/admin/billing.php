<?php use App\Core\Csrf; /** @var array $transactions @var array $pagination @var float $totalRevenue */ ?>
<div class="page-header"><div><h1 class="page-title">Billing & Transactions</h1><p class="page-subtitle">Total Revenue: $<?= number_format((float)$totalRevenue, 2) ?></p></div></div>
<div class="card mb-4"><div class="card-header"><h5>Manual Payment</h5></div><div class="card-body">
<form method="POST" action="/admin/billing/manual-payment" class="row g-3 align-items-end">
<?= Csrf::field() ?>
<div class="col-md-3"><label class="form-label">User ID</label><input type="number" class="form-control" name="user_id" required></div>
<div class="col-md-3"><label class="form-label">Amount ($)</label><input type="number" class="form-control" name="amount" step="0.01" min="0" required></div>
<div class="col-md-3"><label class="form-label">SMS Credits</label><input type="number" class="form-control" name="sms_credits" step="0.01" min="0" required></div>
<div class="col-md-3"><button class="btn btn-primary w-100"><i class="bi bi-plus"></i> Add Payment</button></div></form></div></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>User</th><th>Type</th><th>Amount</th><th>Description</th><th>Status</th><th>Date</th></tr></thead><tbody>
<?php foreach ($transactions as $t): ?><tr>
<td><?= htmlspecialchars($t['user_name']) ?></td><td><span class="tag-chip"><?= $t['type'] ?></span></td>
<td>$<?= number_format((float)$t['amount'], 2) ?></td><td class="fs-sm"><?= htmlspecialchars($t['description'] ?? '') ?></td>
<td><span class="badge badge-status badge-<?= $t['status'] === 'completed' ? 'delivered' : 'pending' ?>"><?= ucfirst($t['status']) ?></span></td>
<td class="fs-xs"><?= date('M d, H:i', strtotime($t['created_at'])) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
