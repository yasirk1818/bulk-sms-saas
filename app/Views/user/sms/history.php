<?php
/** @var array $logs */
/** @var array $pagination */
/** @var string $status */
/** @var string $search */
?>

<div class="page-header">
    <div>
        <h1 class="page-title">SMS History</h1>
        <p class="page-subtitle">View all sent messages</p>
    </div>
    <a href="/reports/export?type=sms" class="btn btn-secondary"><i class="bi bi-download"></i> Export</a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search recipient or message...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All</option>
                    <option value="sent" <?= $status === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Message</th>
                    <th>Gateway</th>
                    <th>Status</th>
                    <th>Parts</th>
                    <th>Cost</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No SMS history found</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($log['recipient']) ?></code></td>
                            <td class="truncate" style="max-width:200px" title="<?= htmlspecialchars($log['message']) ?>"><?= htmlspecialchars(mb_substr($log['message'], 0, 40)) ?>...</td>
                            <td class="fs-sm"><?= $log['gateway_id'] ?? '-' ?></td>
                            <td>
                                <span class="badge badge-status badge-<?= match($log['status']) { 'delivered' => 'delivered', 'sent' => 'active', 'failed' => 'failed', default => 'pending' } ?>">
                                    <?= ucfirst($log['status']) ?>
                                </span>
                            </td>
                            <td><?= $log['parts'] ?></td>
                            <td><?= number_format((float)$log['cost'], 4) ?></td>
                            <td class="fs-xs text-muted"><?= date('M d, H:i', strtotime($log['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination['last_page'] > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="fs-sm text-muted">Showing <?= $pagination['from'] ?> - <?= $pagination['to'] ?> of <?= $pagination['total'] ?></span>
            <nav>
                <ul class="pagination mb-0">
                    <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                        <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
