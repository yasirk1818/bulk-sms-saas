<?php /** @var array $tickets */ ?>
<div class="page-header"><div><h1 class="page-title">Support Tickets</h1></div>
<a href="/tickets/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Ticket</a></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>#</th><th>Subject</th><th>Priority</th><th>Status</th><th>Last Reply</th><th>Actions</th></tr></thead><tbody>
<?php if (empty($tickets)): ?><tr><td colspan="6" class="text-center text-muted py-4">No tickets yet</td></tr>
<?php else: foreach ($tickets as $t): ?><tr>
<td><code><?= $t['ticket_number'] ?></code></td><td><?= htmlspecialchars($t['subject']) ?></td>
<td><span class="tag-chip"><?= $t['priority'] ?></span></td>
<td><span class="badge badge-status badge-<?= match($t['status']){ 'open'=>'pending','answered'=>'delivered','closed'=>'inactive',default=>'active'} ?>"><?= ucfirst($t['status']) ?></span></td>
<td class="fs-xs"><?= $t['last_reply_at'] ? date('M d H:i', strtotime($t['last_reply_at'])) : '-' ?></td>
<td><a href="/tickets/<?= $t['id'] ?>" class="btn btn-sm btn-secondary"><i class="bi bi-eye"></i></a></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>
