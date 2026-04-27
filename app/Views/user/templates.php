<?php use App\Core\Csrf; /** @var array $templates */ ?>
<div class="page-header"><div><h1 class="page-title">SMS Templates</h1></div>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTemplateModal"><i class="bi bi-plus-lg"></i> Create Template</button></div>
<div class="row g-3">
<?php foreach ($templates as $t): ?>
<div class="col-md-4"><div class="card"><div class="card-body">
<div class="d-flex justify-content-between mb-2"><h6><?= htmlspecialchars($t['name']) ?></h6><span class="badge badge-status badge-<?= $t['status'] === 'approved' ? 'delivered' : ($t['status'] === 'rejected' ? 'failed' : 'pending') ?>"><?= ucfirst($t['status']) ?></span></div>
<p class="fs-sm text-muted"><?= htmlspecialchars(mb_substr($t['content'], 0, 100)) ?></p>
<div class="d-flex gap-1"><form method="POST" action="/templates/<?= $t['id'] ?>/delete"><?= Csrf::field() ?><button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form></div>
</div></div></div>
<?php endforeach; ?>
<?php if (empty($templates)): ?><div class="col-12"><div class="card"><div class="card-body text-center text-muted py-4">No templates yet</div></div></div><?php endif; ?>
</div>
<div class="modal fade" id="addTemplateModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Create Template</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="/templates"><div class="modal-body"><?= Csrf::field() ?>
<div class="mb-3"><label class="form-label">Template Name</label><input type="text" class="form-control" name="name" required></div>
<div class="mb-3"><label class="form-label">Category</label><input type="text" class="form-control" name="category" placeholder="e.g., Marketing, Transactional"></div>
<div class="mb-3"><label class="form-label">Content</label><textarea class="form-control" name="content" rows="5" required></textarea><div class="form-text">Variables: {name}, {phone}, {email}, {company}</div></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Create Template</button></div></form></div></div></div>
