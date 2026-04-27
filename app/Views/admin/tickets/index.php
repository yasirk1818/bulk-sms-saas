<?php /** @var array $tickets @var array $pagination */ ?>
<div class="page-header"><div><h1 class="page-title">Support Tickets</h1></div></div>
<div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>#</th><th>User</th><th>Subject</th><th>Priority</th><th>Status</th><th>Last Reply</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($tickets as $t): ?><tr>
<td><code><?= $t['ticket_number'] ?></code></td><td><?= htmlspecialchars($t['user_name']) ?></td><td><?= htmlspecialchars($t['subject']) ?></td>
<td><span class="tag-chip"><?= $t['priority'] ?></span></td>
<td><span class="badge badge-status badge-<?= match($t['status']){ 'open' => 'pending', 'in_progress' => 'active', 'answered' => 'delivered', 'closed' => 'inactive', default => 'pending'} ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span></td>
<td class="fs-xs"><?= $t['last_reply_at'] ? date('M d H:i', strtotime($t['last_reply_at'])) : '-' ?></td>
<td><a href="/admin/tickets/<?= $t['id'] ?>" class="btn btn-sm btn-secondary"><i class="bi bi-eye"></i></a></td></tr><?php endforeach; ?>
</tbody></table></div></div>
