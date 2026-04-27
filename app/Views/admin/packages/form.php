<?php use App\Core\Csrf; /** @var array|null $package */ $e = $package !== null; ?>
<div class="page-header"><div><h1 class="page-title"><?= $e ? 'Edit' : 'Create' ?> Package</h1></div></div>
<div class="card"><div class="card-body"><form method="POST" action="<?= $e ? '/admin/packages/' . $package['id'] : '/admin/packages' ?>">
<?= Csrf::field() ?>
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Name</label><input type="text" class="form-control" name="name" value="<?= htmlspecialchars($package['name'] ?? '') ?>" required></div>
<div class="col-md-6"><label class="form-label">Price ($)</label><input type="number" class="form-control" name="price" value="<?= $package['price'] ?? '0' ?>" step="0.01" min="0" required></div>
<div class="col-md-4"><label class="form-label">SMS Quota</label><input type="number" class="form-control" name="sms_quota" value="<?= $package['sms_quota'] ?? '100' ?>" min="0" required></div>
<div class="col-md-4"><label class="form-label">Validity (days)</label><input type="number" class="form-control" name="validity_days" value="<?= $package['validity_days'] ?? '30' ?>" min="1" required></div>
<div class="col-md-4"><label class="form-label">Cost per SMS</label><input type="number" class="form-control" name="cost_per_sms" value="<?= $package['cost_per_sms'] ?? '0.0100' ?>" step="0.0001" min="0"></div>
<div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($package['description'] ?? '') ?></textarea></div>
<div class="col-12"><label class="form-label">Features (one per line)</label><textarea class="form-control" name="features" rows="4"><?= htmlspecialchars(is_string($package['features'] ?? '') ? implode("\n", json_decode($package['features'], true) ?? []) : '') ?></textarea></div>
<div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" class="form-control" name="sort_order" value="<?= $package['sort_order'] ?? 0 ?>"></div>
<div class="col-md-4"><div class="form-check mt-4"><input type="checkbox" class="form-check-input" name="is_active" id="isActive" <?= ($package['is_active'] ?? 1) ? 'checked' : '' ?>><label class="form-check-label" for="isActive">Active</label></div></div>
<div class="col-md-4"><div class="form-check mt-4"><input type="checkbox" class="form-check-input" name="is_trial" id="isTrial" <?= ($package['is_trial'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label" for="isTrial">Trial Package</label></div></div>
</div>
<div class="mt-4"><button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> <?= $e ? 'Update' : 'Create' ?></button> <a href="/admin/packages" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
