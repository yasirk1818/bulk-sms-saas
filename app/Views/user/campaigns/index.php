<?php /** @var array $campaigns @var array $pagination */ ?>
<div class="page-header"><div><h1 class="page-title">Campaigns</h1></div><a href="/campaigns/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Campaign</a></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Status</th><th>Recipients</th><th>Delivered</th><th>Failed</th><th>Date</th><th>Actions</th></tr></thead><tbody>
<?php if (empty($campaigns)): ?><tr><td colspan="7" class="text-center text-muted py-4">No campaigns yet</td></tr>
<?php else: foreach ($campaigns as $c): ?><tr>
<td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
<td><span class="badge badge-status badge-<?= match($c['status']){ 'running'=>'active','completed'=>'delivered','failed'=>'failed','scheduled'=>'pending',default=>'inactive'} ?>"><?= ucfirst($c['status']) ?></span></td>
<td><?= number_format($c['total_recipients'] ?? 0) ?></td><td class="text-success"><?= number_format($c['total_delivered'] ?? 0) ?></td><td class="text-danger"><?= number_format($c['total_failed'] ?? 0) ?></td>
<td class="fs-sm"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
<td><a href="/campaigns/<?= $c['id'] ?>" class="btn btn-sm btn-secondary"><i class="bi bi-eye"></i></a>
<?php if (in_array($c['status'], ['draft','scheduled'])): ?><a href="/campaigns/<?= $c['id'] ?>/edit" class="btn btn-sm btn-secondary"><i class="bi bi-pencil"></i></a><?php endif; ?></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>
