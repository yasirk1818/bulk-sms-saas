<?php use App\Core\Csrf; /** @var array $senderIds */ ?>
<div class="page-header"><div><h1 class="page-title">Sender ID Approval</h1></div></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>User</th><th>Sender ID</th><th>Purpose</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($senderIds as $s): ?><tr>
<td><?= htmlspecialchars($s['user_name']) ?></td><td><strong><?= htmlspecialchars($s['sender_id']) ?></strong></td><td class="fs-sm"><?= htmlspecialchars($s['purpose'] ?? '-') ?></td>
<td><span class="badge badge-status badge-<?= $s['status'] === 'approved' ? 'delivered' : ($s['status'] === 'rejected' ? 'failed' : 'pending') ?>"><?= ucfirst($s['status']) ?></span></td>
<td><?php if ($s['status'] === 'pending'): ?>
<form method="POST" action="/admin/sender-ids/<?= $s['id'] ?>/approve" style="display:inline"><?= Csrf::field() ?><button class="btn btn-sm btn-success"><i class="bi bi-check"></i></button></form>
<form method="POST" action="/admin/sender-ids/<?= $s['id'] ?>/reject" style="display:inline"><?= Csrf::field() ?><input type="hidden" name="reason"><button class="btn btn-sm btn-danger"><i class="bi bi-x"></i></button></form>
<?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
