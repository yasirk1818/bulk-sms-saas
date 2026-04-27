<?php use App\Core\Csrf; /** @var array $user @var array $loginLogs */ ?>
<div class="page-header"><div><h1 class="page-title">Profile Settings</h1></div></div>
<div class="row g-4">
<div class="col-xl-8">
<div class="card mb-4"><div class="card-header"><h5><i class="bi bi-person me-2"></i>Personal Information</h5></div><div class="card-body">
<form method="POST" action="/profile"><div class="row g-3"><?= Csrf::field() ?>
<div class="col-md-6"><label class="form-label">Full Name</label><input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled></div>
<div class="col-md-6"><label class="form-label">Phone</label><input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
<div class="col-md-3"><label class="form-label">Timezone</label><select class="form-select" name="timezone"><option value="UTC" <?= ($user['timezone']??'UTC')==='UTC'?'selected':'' ?>>UTC</option><option value="America/New_York">Eastern</option><option value="America/Chicago">Central</option><option value="America/Denver">Mountain</option><option value="America/Los_Angeles">Pacific</option><option value="Europe/London">London</option><option value="Asia/Karachi">Pakistan</option></select></div>
<div class="col-md-3"><label class="form-label">Language</label><select class="form-select" name="language"><option value="en">English</option></select></div>
</div><button type="submit" class="btn btn-primary mt-3"><i class="bi bi-check"></i> Update Profile</button></form></div></div>

<div class="card"><div class="card-header"><h5><i class="bi bi-lock me-2"></i>Change Password</h5></div><div class="card-body">
<form method="POST" action="/profile/password"><div class="row g-3"><?= Csrf::field() ?>
<div class="col-md-4"><label class="form-label">Current Password</label><input type="password" class="form-control" name="current_password" required></div>
<div class="col-md-4"><label class="form-label">New Password</label><input type="password" class="form-control" name="new_password" minlength="8" required></div>
<div class="col-md-4"><label class="form-label">Confirm</label><input type="password" class="form-control" name="new_password_confirmation" required></div>
</div><button type="submit" class="btn btn-warning mt-3"><i class="bi bi-lock"></i> Change Password</button></form></div></div>
</div>
<div class="col-xl-4">
<div class="card mb-4"><div class="card-header"><h5>Account Info</h5></div><div class="card-body">
<table class="table table-sm"><tr><td class="text-muted">Role</td><td><span class="tag-chip"><?= str_replace('_',' ',$user['role']) ?></span></td></tr>
<tr><td class="text-muted">Status</td><td><span class="badge badge-status badge-active"><?= ucfirst($user['status']) ?></span></td></tr>
<tr><td class="text-muted">Balance</td><td><strong class="text-accent"><?= number_format((float)$user['sms_balance'], 0) ?> SMS</strong></td></tr>
<tr><td class="text-muted">Joined</td><td><?= date('M d, Y', strtotime($user['created_at'])) ?></td></tr></table></div></div>
<div class="card"><div class="card-header"><h5>Recent Logins</h5></div><div class="card-body p-0">
<table class="table table-sm mb-0"><tbody><?php foreach ($loginLogs as $l): ?><tr><td class="fs-xs"><code><?= $l['ip_address'] ?></code> <?= $l['browser'] ?><br><span class="text-muted"><?= date('M d H:i', strtotime($l['created_at'])) ?></span></td></tr><?php endforeach; ?></tbody></table></div></div>
</div></div>
