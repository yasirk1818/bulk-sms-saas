<?php use App\Core\Csrf; /** @var array $ticket @var array $replies */ ?>
<div class="page-header"><div><h1 class="page-title">Ticket #<?= $ticket['ticket_number'] ?></h1><p class="page-subtitle"><?= htmlspecialchars($ticket['subject']) ?></p></div>
<span class="badge badge-status badge-<?= match($ticket['status']){ 'open'=>'pending','answered'=>'delivered','closed'=>'inactive',default=>'active'} ?>"><?= ucfirst($ticket['status']) ?></span></div>
<?php foreach ($replies as $reply): ?>
<div class="card mb-3 <?= $reply['is_staff_reply'] ? 'border-start border-accent border-3' : '' ?>"><div class="card-body">
<div class="d-flex justify-content-between mb-2"><strong><?= htmlspecialchars($reply['name']) ?> <?php if ($reply['is_staff_reply']): ?><span class="tag-chip">Staff</span><?php endif; ?></strong><span class="fs-xs text-muted"><?= date('M d, Y H:i', strtotime($reply['created_at'])) ?></span></div>
<div><?= nl2br(htmlspecialchars($reply['message'])) ?></div></div></div>
<?php endforeach; ?>
<?php if ($ticket['status'] !== 'closed'): ?>
<div class="card"><div class="card-header"><h5>Reply</h5></div><div class="card-body"><form method="POST" action="/tickets/<?= $ticket['id'] ?>/reply"><?= Csrf::field() ?><textarea class="form-control mb-3" name="message" rows="4" required placeholder="Your reply..."></textarea><button class="btn btn-primary"><i class="bi bi-send"></i> Send Reply</button></form></div></div>
<?php endif; ?>
