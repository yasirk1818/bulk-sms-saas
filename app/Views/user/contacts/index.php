<?php use App\Core\Csrf; /** @var array $contacts @var array $pagination @var array $groups */ ?>
<div class="page-header"><div><h1 class="page-title">Contacts</h1></div>
<div class="d-flex gap-2"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal"><i class="bi bi-plus-lg"></i> Add Contact</button>
<button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-upload"></i> Import</button>
<a href="/contacts/export" class="btn btn-secondary"><i class="bi bi-download"></i> Export</a></div></div>

<div class="card mb-3"><div class="card-body"><form method="GET" class="d-flex gap-2"><input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search contacts..." style="max-width:300px"><button class="btn btn-primary"><i class="bi bi-search"></i></button></form></div></div>

<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>Phone</th><th>Name</th><th>Email</th><th>Country</th><th>Actions</th></tr></thead><tbody>
<?php if (empty($contacts)): ?><tr><td colspan="5" class="text-center text-muted py-4">No contacts yet</td></tr>
<?php else: foreach ($contacts as $c): ?><tr>
<td><code><?= htmlspecialchars($c['phone']) ?></code></td><td><?= htmlspecialchars($c['name'] ?? '-') ?></td>
<td class="fs-sm"><?= htmlspecialchars($c['email'] ?? '-') ?></td><td><?= $c['country_code'] ?? '-' ?></td>
<td><form method="POST" action="/contacts/<?= $c['id'] ?>/delete" style="display:inline"><?= Csrf::field() ?><button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div>
<?php if ($pagination['last_page'] > 1): ?><div class="card-footer"><nav><ul class="pagination mb-0"><?php for($i=1;$i<=$pagination['last_page'];$i++): ?><li class="page-item <?= $i===$pagination['current_page']?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav></div><?php endif; ?></div>

<!-- Add Contact Modal -->
<div class="modal fade" id="addContactModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Add Contact</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="/contacts"><div class="modal-body"><?= Csrf::field() ?>
<div class="mb-3"><label class="form-label">Phone</label><input type="tel" class="form-control" name="phone" required></div>
<div class="mb-3"><label class="form-label">Name</label><input type="text" class="form-control" name="name"></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
<div class="mb-3"><label class="form-label">Group</label><select class="form-select" name="group_id"><option value="">None</option><?php foreach($groups as $g): ?><option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option><?php endforeach; ?></select></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Add Contact</button></div></form></div></div></div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Import Contacts</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="/contacts/import" enctype="multipart/form-data"><div class="modal-body"><?= Csrf::field() ?>
<p class="text-muted fs-sm">Upload CSV file with columns: Phone, Name, Email</p>
<input type="file" class="form-control" name="file" accept=".csv,.txt" required>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Import</button></div></form></div></div></div>
