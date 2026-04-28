<?php use App\Core\Csrf; /** @var array|null $campaign @var array $groups @var array $templates @var array $senderIds @var array $gateways */ $e = $campaign !== null; ?>
<div class="page-header"><div><h1 class="page-title"><?= $e ? 'Edit' : 'Create' ?> Campaign</h1></div></div>
<div class="card"><div class="card-body"><form method="POST" action="<?= $e ? '/campaigns/' . $campaign['id'] : '/campaigns' ?>">
<?= Csrf::field() ?>
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Campaign Name</label><input type="text" class="form-control" name="name" value="<?= htmlspecialchars($campaign['name'] ?? '') ?>" required></div>
<div class="col-md-3"><label class="form-label">Type</label><select class="form-select" name="type"><option value="instant" <?= ($campaign['type'] ?? '') === 'instant' ? 'selected' : '' ?>>Instant</option><option value="scheduled" <?= ($campaign['type'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option></select></div>
<div class="col-md-3"><label class="form-label">Schedule At</label><input type="datetime-local" class="form-control" name="scheduled_at" value="<?= $campaign['scheduled_at'] ?? '' ?>"></div>
<div class="col-md-6"><label class="form-label">Sender ID</label><select class="form-select" name="sender_id"><option value="">Default</option><?php foreach($senderIds as $s): ?><option value="<?= htmlspecialchars($s['sender_id']) ?>"><?= htmlspecialchars($s['sender_id']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Gateway</label><select class="form-select" name="gateway_id"><option value="">Auto (Best Route)</option><?php foreach($gateways as $g): ?><option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Template</label><select class="form-select" name="template_id" id="campaignTemplate"><option value="">None</option><?php foreach($templates as $t): ?><option value="<?= $t['id'] ?>" data-content="<?= htmlspecialchars($t['content']) ?>"><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Target Groups</label>
<?php foreach($groups as $g): ?><div class="form-check"><input type="checkbox" class="form-check-input" name="group_ids[]" value="<?= $g['id'] ?>" id="grp<?= $g['id'] ?>"><label class="form-check-label" for="grp<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?> (<?= $g['member_count'] ?? $g['contacts_count'] ?? 0 ?>)</label></div><?php endforeach; ?>
<?php if (empty($groups)): ?><p class="text-muted">No groups. <a href="/contacts/groups">Create one first</a></p><?php endif; ?></div>
<div class="col-12"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="5" required placeholder="Type campaign message..."><?= htmlspecialchars($campaign['message'] ?? '') ?></textarea>
<div class="form-text">Variables: {name}, {phone}, {email}, {company}</div></div></div>
<div class="mt-4"><button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-megaphone"></i> <?= $e ? 'Update' : 'Launch' ?> Campaign</button></div>
</form></div></div>
