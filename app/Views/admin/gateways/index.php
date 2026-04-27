<?php use App\Core\Csrf; /** @var array $gateways */ ?>
<div class="page-header"><div><h1 class="page-title">Gateway Management</h1><p class="page-subtitle">Manage SMS gateways and routing</p></div>
<a href="/admin/gateways/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Gateway</a></div>

<div class="card">
    <div class="table-responsive"><table class="table"><thead><tr><th>Gateway</th><th>Type</th><th>Cost/SMS</th><th>Sent</th><th>Success Rate</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php if (empty($gateways)): ?><tr><td colspan="7" class="text-center text-muted py-4">No gateways configured</td></tr>
    <?php else: foreach ($gateways as $gw): ?>
        <tr>
            <td><strong><?= htmlspecialchars($gw['name']) ?></strong><div class="fs-xs text-muted"><?= $gw['is_default'] ? 'Default' : '' ?></div></td>
            <td><span class="tag-chip"><?= $gw['type'] ?></span></td>
            <td>$<?= number_format((float)$gw['cost_per_sms'], 4) ?></td>
            <td><?= number_format($gw['total_sent']) ?></td>
            <td><?= $gw['total_sent'] > 0 ? round(($gw['total_delivered'] / $gw['total_sent']) * 100, 1) : 0 ?>%</td>
            <td><span class="badge badge-status badge-<?= $gw['status'] === 'active' ? 'active' : 'inactive' ?>"><?= ucfirst($gw['status']) ?></span></td>
            <td>
                <div class="d-flex gap-1">
                    <a href="/admin/gateways/<?= $gw['id'] ?>/edit" class="btn btn-sm btn-secondary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="/admin/gateways/<?= $gw['id'] ?>/toggle" style="display:inline"><?= Csrf::field() ?><button class="btn btn-sm btn-<?= $gw['status'] === 'active' ? 'warning' : 'success' ?>"><i class="bi bi-<?= $gw['status'] === 'active' ? 'pause' : 'play' ?>"></i></button></form>
                    <button class="btn btn-sm btn-info" onclick="testGateway(<?= $gw['id'] ?>)"><i class="bi bi-activity"></i></button>
                </div>
            </td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody></table></div>
</div>
<script>function testGateway(id){fetch('/admin/gateways/'+id+'/test').then(r=>r.json()).then(d=>{App.toast(d.status==='up'?'Gateway UP ('+d.response_time+'ms)':'Gateway DOWN: '+(d.error||''),d.status==='up'?'success':'error')})}</script>
