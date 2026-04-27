<?php use App\Core\Csrf; /** @var array $groups */ ?>
<div class="page-header"><div><h1 class="page-title">Contact Groups</h1></div>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGroupModal"><i class="bi bi-plus-lg"></i> Create Group</button></div>
<div class="row g-3">
<?php foreach ($groups as $g): ?>
<div class="col-md-4"><div class="card"><div class="card-body text-center">
<div class="stat-card-icon primary mx-auto mb-3"><i class="bi bi-people"></i></div>
<h5><?= htmlspecialchars($g['name']) ?></h5>
<p class="text-muted fs-sm"><?= htmlspecialchars($g['description'] ?? '') ?></p>
<span class="tag-chip"><?= $g['member_count'] ?? $g['contacts_count'] ?? 0 ?> contacts</span>
</div></div></div>
<?php endforeach; ?>
<?php if (empty($groups)): ?><div class="col-12"><div class="card"><div class="card-body text-center text-muted py-4">No groups yet</div></div></div><?php endif; ?>
</div>
<div class="modal fade" id="createGroupModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Create Group</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="/contacts/groups"><div class="modal-body"><?= Csrf::field() ?>
<div class="mb-3"><label class="form-label">Group Name</label><input type="text" class="form-control" name="name" required></div>
<div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Create</button></div></form></div></div></div>
