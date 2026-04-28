<?php use App\Core\Csrf; /** @var array $senderIds */ ?>
<div class="page-header"><div><h1 class="page-title">Sender IDs</h1></div>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestModal"><i class="bi bi-plus-lg"></i> Request Sender ID</button></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>Sender ID</th><th>Purpose</th><th>Status</th><th>Date</th></tr></thead><tbody>
<?php if (empty($senderIds)): ?><tr><td colspan="4" class="text-center text-muted py-4">No sender IDs</td></tr>
<?php else: foreach ($senderIds as $s): ?><tr>
<td><strong><?= htmlspecialchars($s['sender_id']) ?></strong></td><td class="fs-sm"><?= htmlspecialchars($s['purpose'] ?? '-') ?></td>
<td><span class="badge badge-status badge-<?= $s['status'] === 'approved' ? 'delivered' : ($s['status'] === 'rejected' ? 'failed' : 'pending') ?>"><?= ucfirst($s['status']) ?></span></td>
<td class="fs-sm"><?= date('M d, Y', strtotime($s['created_at'])) ?></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>
<div class="modal fade" id="requestModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Request Sender ID</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="/sender-ids/request"><div class="modal-body"><?= Csrf::field() ?>
<div class="mb-3"><label class="form-label">Sender ID</label><input type="text" class="form-control" name="sender_id" maxlength="11" required><div class="form-text">Max 11 characters, alphanumeric</div></div>
<div class="mb-3"><label class="form-label">Purpose</label><textarea class="form-control" name="purpose" rows="2"></textarea></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Submit Request</button></div></form></div></div></div>
