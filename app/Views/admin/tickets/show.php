<?php use App\Core\Csrf; /** @var array $ticket @var array $replies */ ?>
<div class="page-header"><div><h1 class="page-title">Ticket #<?= $ticket['ticket_number'] ?></h1><p class="page-subtitle"><?= htmlspecialchars($ticket['subject']) ?></p></div>
<div class="d-flex gap-2"><span class="badge badge-status badge-<?= match($ticket['status']){ 'open'=>'pending','answered'=>'delivered','closed'=>'inactive',default=>'active'} ?>"><?= ucfirst($ticket['status']) ?></span>
<?php if ($ticket['status'] !== 'closed'): ?><form method="POST" action="/admin/tickets/<?= $ticket['id'] ?>/close"><?= Csrf::field() ?><button class="btn btn-sm btn-warning">Close</button></form><?php endif; ?></div></div>
<div class="card mb-4"><div class="card-body"><strong>User:</strong> <?= htmlspecialchars($ticket['user_name']) ?> (<?= $ticket['user_email'] ?>)<br><strong>Priority:</strong> <?= $ticket['priority'] ?> | <strong>Category:</strong> <?= $ticket['category'] ?? '-' ?></div></div>
<?php foreach ($replies as $reply): ?>
<div class="card mb-3"><div class="card-body">
<div class="d-flex justify-content-between mb-2"><strong><?= htmlspecialchars($reply['name']) ?> <span class="tag-chip"><?= $reply['is_staff_reply'] ? 'Staff' : 'User' ?></span></strong><span class="fs-xs text-muted"><?= date('M d, Y H:i', strtotime($reply['created_at'])) ?></span></div>
<div><?= nl2br(htmlspecialchars($reply['message'])) ?></div>
</div></div>
<?php endforeach; ?>
<?php if ($ticket['status'] !== 'closed'): ?>
<div class="card"><div class="card-header"><h5>Reply</h5></div><div class="card-body"><form method="POST" action="/admin/tickets/<?= $ticket['id'] ?>/reply"><?= Csrf::field() ?><textarea class="form-control mb-3" name="message" rows="4" required placeholder="Type your reply..."></textarea><button class="btn btn-primary"><i class="bi bi-send"></i> Send Reply</button></form></div></div>
<?php endif; ?>
