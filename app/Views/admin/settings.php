<?php use App\Core\Csrf; /** @var array $settings @var array $groups */ ?>
<div class="page-header"><div><h1 class="page-title">System Settings</h1></div></div>
<form method="POST" action="/admin/settings"><?= Csrf::field() ?>
<?php foreach ($groups as $group => $items): ?>
<div class="card mb-4"><div class="card-header"><h5><i class="bi bi-gear me-2"></i><?= ucfirst(str_replace('_', ' ', $group)) ?></h5></div><div class="card-body"><div class="row g-3">
<?php foreach ($items as $item): ?>
<div class="col-md-6">
    <label class="form-label"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $item['setting_key']))) ?></label>
    <?php if ($item['setting_type'] === 'boolean'): ?>
        <select class="form-select" name="<?= $item['setting_key'] ?>"><option value="1" <?= $item['setting_value'] === '1' ? 'selected' : '' ?>>Enabled</option><option value="0" <?= $item['setting_value'] !== '1' ? 'selected' : '' ?>>Disabled</option></select>
    <?php elseif ($item['setting_type'] === 'textarea'): ?>
        <textarea class="form-control" name="<?= $item['setting_key'] ?>" rows="3"><?= htmlspecialchars($item['setting_value'] ?? '') ?></textarea>
    <?php else: ?>
        <input type="text" class="form-control" name="<?= $item['setting_key'] ?>" value="<?= htmlspecialchars($item['setting_value'] ?? '') ?>">
    <?php endif; ?>
    <?php if ($item['description']): ?><div class="form-text"><?= htmlspecialchars($item['description']) ?></div><?php endif; ?>
</div>
<?php endforeach; ?>
</div></div></div>
<?php endforeach; ?>
<button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check"></i> Save Settings</button>
</form>
