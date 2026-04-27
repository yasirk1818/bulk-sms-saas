<?php use App\Core\Csrf; ?>
<div class="page-header"><div><h1 class="page-title">Create User</h1></div></div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/users">
            <?= Csrf::field() ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Name</label><input type="text" class="form-control" name="name" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" class="form-control" name="phone"></div>
                <div class="col-md-6"><label class="form-label">Password</label><input type="password" class="form-control" name="password" minlength="8" required></div>
                <div class="col-md-4"><label class="form-label">Role</label><select class="form-select" name="role"><?php foreach(['user','staff','reseller','admin'] as $r): ?><option value="<?= $r ?>"><?= ucfirst(str_replace('_',' ',$r)) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div class="col-md-4"><label class="form-label">SMS Balance</label><input type="number" class="form-control" name="sms_balance" value="0" min="0" step="0.01"></div>
                <div class="col-md-6"><label class="form-label">Package</label><select class="form-select" name="package_id"><option value="">None</option><?php foreach($packages as $p): ?><option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="mt-4"><button type="submit" class="btn btn-primary"><i class="bi bi-person-plus"></i> Create User</button> <a href="/admin/users" class="btn btn-secondary">Cancel</a></div>
        </form>
    </div>
</div>
