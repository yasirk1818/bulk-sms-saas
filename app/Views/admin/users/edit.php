<?php use App\Core\Csrf; /** @var array $user */ ?>
<div class="page-header"><div><h1 class="page-title">Edit User: <?= htmlspecialchars($user['name']) ?></h1></div></div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/users/<?= $user['id'] ?>">
            <?= Csrf::field() ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Name</label><input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">New Password</label><input type="password" class="form-control" name="password" placeholder="Leave empty to keep current"></div>
                <div class="col-md-4"><label class="form-label">Role</label><select class="form-select" name="role"><?php foreach(['user','staff','reseller','admin','super_admin'] as $r): ?><option value="<?= $r ?>" <?= $user['role'] === $r ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$r)) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><?php foreach(['active','inactive','suspended','banned'] as $s): ?><option value="<?= $s ?>" <?= $user['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">SMS Balance</label><input type="number" class="form-control" name="sms_balance" value="<?= $user['sms_balance'] ?>" min="0" step="0.01"></div>
                <div class="col-md-6"><label class="form-label">Package</label><select class="form-select" name="package_id"><option value="">None</option><?php foreach($packages as $p): ?><option value="<?= $p['id'] ?>" <?= ($user['package_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="mt-4"><button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Update User</button> <a href="/admin/users/<?= $user['id'] ?>" class="btn btn-secondary">Cancel</a></div>
        </form>
    </div>
</div>
