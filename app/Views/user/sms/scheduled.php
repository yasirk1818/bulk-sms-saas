<?php /** @var array $scheduled */ ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Scheduled SMS</h1>
        <p class="page-subtitle">View and manage scheduled messages</p>
    </div>
    <a href="/sms/send" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Schedule New</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Recipient</th><th>Message</th><th>Scheduled For</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($scheduled)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No scheduled SMS</td></tr>
                <?php else: ?>
                    <?php foreach ($scheduled as $sms): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($sms['recipient']) ?></code></td>
                            <td class="truncate" style="max-width:200px"><?= htmlspecialchars(mb_substr($sms['message'], 0, 50)) ?></td>
                            <td><?= date('M d, Y H:i', strtotime($sms['scheduled_at'])) ?></td>
                            <td><span class="badge badge-status badge-queued">Scheduled</span></td>
                            <td>
                                <form method="POST" action="/sms/cancel/<?= $sms['id'] ?>" style="display:inline">
                                    <?= \App\Core\Csrf::field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger" data-confirm="Cancel this scheduled SMS?"><i class="bi bi-x"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
