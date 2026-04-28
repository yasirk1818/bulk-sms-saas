<?php use App\Core\Csrf; /** @var array $backups */ ?>
<div class="page-header"><div><h1 class="page-title">Backup Manager</h1></div>
<form method="POST" action="/admin/backups/create"><?= Csrf::field() ?><button class="btn btn-primary"><i class="bi bi-cloud-arrow-down"></i> Create Backup</button></form></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>Filename</th><th>Size</th><th>Date</th><th>Actions</th></tr></thead><tbody>
<?php if (empty($backups)): ?><tr><td colspan="4" class="text-center text-muted py-4">No backups yet</td></tr>
<?php else: foreach ($backups as $b): ?><tr>
<td><i class="bi bi-file-earmark-code me-2"></i><?= htmlspecialchars($b['name']) ?></td>
<td><?= round($b['size'] / 1024, 1) ?> KB</td><td class="fs-sm"><?= $b['date'] ?></td>
<td><a href="/admin/backups/download/<?= urlencode($b['name']) ?>" class="btn btn-sm btn-secondary"><i class="bi bi-download"></i></a>
<form method="POST" action="/admin/backups/delete/<?= urlencode($b['name']) ?>" style="display:inline"><?= Csrf::field() ?><button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>
