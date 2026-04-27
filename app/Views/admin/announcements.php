<?php use App\Core\Csrf; /** @var array $announcements */ ?>
<div class="page-header"><div><h1 class="page-title">Announcements</h1></div></div>
<div class="card mb-4"><div class="card-header"><h5>Create Announcement</h5></div><div class="card-body">
<form method="POST" action="/admin/announcements"><?= Csrf::field() ?>
<div class="row g-3"><div class="col-md-8"><input type="text" class="form-control" name="title" placeholder="Announcement title" required></div>
<div class="col-md-4"><select class="form-select" name="type"><option value="info">Info</option><option value="warning">Warning</option><option value="success">Success</option><option value="danger">Critical</option></select></div>
<div class="col-12"><textarea class="form-control" name="content" rows="3" placeholder="Announcement content..." required></textarea></div></div>
<button type="submit" class="btn btn-primary mt-3"><i class="bi bi-megaphone"></i> Publish</button></form></div></div>
<?php foreach ($announcements as $a): ?>
<div class="card mb-3"><div class="card-body d-flex justify-content-between align-items-start">
<div><span class="badge bg-<?= $a['type'] ?> mb-2"><?= ucfirst($a['type']) ?></span><h5><?= htmlspecialchars($a['title']) ?></h5><p class="mb-0"><?= nl2br(htmlspecialchars($a['content'])) ?></p><small class="text-muted"><?= date('M d, Y H:i', strtotime($a['created_at'])) ?></small></div>
<form method="POST" action="/admin/announcements/<?= $a['id'] ?>/delete"><?= Csrf::field() ?><button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form></div></div>
<?php endforeach; ?>
