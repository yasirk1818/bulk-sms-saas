<?php
use App\Core\Csrf;
/** @var array $users */
/** @var array $pagination */
?>

<div class="page-header">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage all platform users</p>
    </div>
    <a href="/admin/users/create" class="btn btn-primary"><i class="bi bi-person-plus"></i> Add User</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name or email...">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="role">
                    <option value="">All Roles</option>
                    <?php foreach (['super_admin','admin','reseller','staff','user'] as $r): ?>
                        <option value="<?= $r ?>" <?= $role === $r ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$r)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <?php foreach (['active','inactive','suspended','banned'] as $s): ?>
                        <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>User</th><th>Role</th><th>Balance</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar" style="width:32px;height:32px;font-size:0.7rem"><?= strtoupper(substr($u['name'],0,1)) ?></div>
                                <div>
                                    <strong><?= htmlspecialchars($u['name']) ?></strong>
                                    <div class="fs-xs text-muted"><?= htmlspecialchars($u['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="tag-chip"><?= str_replace('_',' ',$u['role']) ?></span></td>
                        <td><?= number_format((float)$u['sms_balance'], 0) ?></td>
                        <td><span class="badge badge-status badge-<?= $u['status'] === 'active' ? 'active' : ($u['status'] === 'suspended' ? 'pending' : 'inactive') ?>"><?= ucfirst($u['status']) ?></span></td>
                        <td class="fs-sm text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/admin/users/<?= $u['id'] ?>" class="btn btn-sm btn-secondary" title="View"><i class="bi bi-eye"></i></a>
                                <a href="/admin/users/<?= $u['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="/admin/users/<?= $u['id'] ?>/toggle-status" style="display:inline">
                                    <?= Csrf::field() ?>
                                    <button class="btn btn-sm btn-<?= $u['status'] === 'active' ? 'warning' : 'success' ?>" title="<?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?>">
                                        <i class="bi bi-<?= $u['status'] === 'active' ? 'pause' : 'play' ?>"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pagination['last_page'] > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="fs-sm text-muted">Showing <?= $pagination['from'] ?> - <?= $pagination['to'] ?> of <?= $pagination['total'] ?></span>
            <nav><ul class="pagination mb-0">
                <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                    <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= $role ?>&status=<?= $statusFilter ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    <?php endif; ?>
</div>
