<?php use App\Core\Csrf; /** @var array $items @var array $pagination */ ?>
<div class="page-header"><div><h1 class="page-title">Number Blacklist</h1></div></div>
<div class="card mb-4"><div class="card-header"><h5>Add Numbers</h5></div><div class="card-body">
<form method="POST" action="/admin/blacklist"><?= Csrf::field() ?>
<div class="row g-3"><div class="col-md-8"><textarea class="form-control" name="numbers" rows="3" placeholder="Enter phone numbers (one per line, or comma-separated)" required></textarea></div>
<div class="col-md-4"><input type="text" class="form-control mb-2" name="reason" placeholder="Reason (optional)"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus"></i> Add to Blacklist</button></div></div></form></div></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>Phone</th><th>Reason</th><th>Added</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($items as $item): ?><tr><td><code><?= htmlspecialchars($item['phone']) ?></code></td><td class="fs-sm"><?= htmlspecialchars($item['reason'] ?? '-') ?></td>
<td class="fs-xs"><?= date('M d, Y', strtotime($item['created_at'])) ?></td>
<td><form method="POST" action="/admin/blacklist/<?= $item['id'] ?>/delete" style="display:inline"><?= Csrf::field() ?><button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
