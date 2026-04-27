<?php use App\Core\Csrf; /** @var array $packages */ ?>
<div class="page-header"><div><h1 class="page-title">Packages</h1></div><a href="/admin/packages/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create Package</a></div>
<div class="row g-3">
<?php foreach ($packages as $pkg): ?>
<div class="col-md-4">
    <div class="card"><div class="card-body text-center">
        <h5 class="mb-1"><?= htmlspecialchars($pkg['name']) ?></h5>
        <div class="stat-card-value text-accent my-2">$<?= number_format((float)$pkg['price'], 2) ?></div>
        <div class="mb-2"><span class="tag-chip"><?= number_format($pkg['sms_quota']) ?> SMS</span> <span class="tag-chip"><?= $pkg['validity_days'] ?> days</span></div>
        <p class="fs-sm text-muted"><?= htmlspecialchars($pkg['description'] ?? '') ?></p>
        <?php if ($pkg['is_trial']): ?><span class="badge bg-info">Trial</span><?php endif; ?>
        <span class="badge badge-status badge-<?= $pkg['is_active'] ? 'active' : 'inactive' ?>"><?= $pkg['is_active'] ? 'Active' : 'Inactive' ?></span>
        <div class="mt-3 d-flex gap-2 justify-content-center">
            <a href="/admin/packages/<?= $pkg['id'] ?>/edit" class="btn btn-sm btn-secondary"><i class="bi bi-pencil"></i> Edit</a>
            <form method="POST" action="/admin/packages/<?= $pkg['id'] ?>/delete" style="display:inline"><?= Csrf::field() ?><button class="btn btn-sm btn-danger" data-confirm="Delete this package?"><i class="bi bi-trash"></i></button></form>
        </div>
    </div></div>
</div>
<?php endforeach; ?>
<?php if (empty($packages)): ?><div class="col-12"><div class="card"><div class="card-body text-center text-muted py-4">No packages yet</div></div></div><?php endif; ?>
</div>
