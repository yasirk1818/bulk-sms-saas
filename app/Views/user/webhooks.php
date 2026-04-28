<?php use App\Core\Csrf; /** @var array $webhooks */ ?>
<div class="page-header"><div><h1 class="page-title">Webhooks</h1></div>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWebhookModal"><i class="bi bi-plus-lg"></i> Add Webhook</button></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>URL</th><th>Events</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($webhooks as $w): ?><tr>
<td><code class="fs-xs"><?= htmlspecialchars($w['url']) ?></code></td>
<td><?php foreach(json_decode($w['events']??'[]',true) as $e): ?><span class="tag-chip"><?= $e ?></span> <?php endforeach; ?></td>
<td><span class="badge badge-status badge-<?= $w['is_active'] ? 'active' : 'inactive' ?>"><?= $w['is_active'] ? 'Active' : 'Disabled' ?></span></td>
<td><form method="POST" action="/webhooks/<?= $w['id'] ?>/delete" style="display:inline"><?= Csrf::field() ?><button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
<div class="modal fade" id="addWebhookModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Add Webhook</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="/webhooks"><div class="modal-body"><?= Csrf::field() ?>
<div class="mb-3"><label class="form-label">Webhook URL</label><input type="url" class="form-control" name="url" required></div>
<div class="mb-3"><label class="form-label">Events</label>
<?php foreach(['sms.sent','sms.delivered','sms.failed','dlr.delivered','dlr.failed'] as $ev): ?>
<div class="form-check"><input type="checkbox" class="form-check-input" name="events[]" value="<?= $ev ?>" id="ev_<?= $ev ?>"><label class="form-check-label" for="ev_<?= $ev ?>"><?= $ev ?></label></div>
<?php endforeach; ?></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Create Webhook</button></div></form></div></div></div>
