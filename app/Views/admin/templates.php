<?php use App\Core\Csrf; /** @var array $templates @var array $pagination */ ?>
<div class="page-header"><div><h1 class="page-title">Template Approval</h1></div></div>
<div class="card mb-3"><div class="card-body"><form method="GET" class="d-flex gap-2"><select class="form-select" name="status" style="width:200px"><option value="">All</option><option value="pending" <?= ($status??'')==='pending'?'selected':'' ?>>Pending</option><option value="approved" <?= ($status??'')==='approved'?'selected':'' ?>>Approved</option><option value="rejected" <?= ($status??'')==='rejected'?'selected':'' ?>>Rejected</option></select><button class="btn btn-primary">Filter</button></form></div></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>User</th><th>Name</th><th>Content</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($templates as $t): ?><tr>
<td><?= htmlspecialchars($t['user_name']) ?></td><td><?= htmlspecialchars($t['name']) ?></td>
<td class="truncate" style="max-width:300px"><?= htmlspecialchars(mb_substr($t['content'], 0, 60)) ?></td>
<td><span class="badge badge-status badge-<?= $t['status'] === 'approved' ? 'delivered' : ($t['status'] === 'rejected' ? 'failed' : 'pending') ?>"><?= ucfirst($t['status']) ?></span></td>
<td><?php if ($t['status'] === 'pending'): ?>
<form method="POST" action="/admin/templates/<?= $t['id'] ?>/approve" style="display:inline"><?= Csrf::field() ?><button class="btn btn-sm btn-success"><i class="bi bi-check"></i></button></form>
<form method="POST" action="/admin/templates/<?= $t['id'] ?>/reject" style="display:inline"><?= Csrf::field() ?><input type="hidden" name="reason" value="Does not meet guidelines"><button class="btn btn-sm btn-danger"><i class="bi bi-x"></i></button></form>
<?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
