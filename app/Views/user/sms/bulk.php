<?php
use App\Core\Csrf;
/** @var array $senderIds */
/** @var array $templates */
/** @var array $groups */
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Bulk SMS</h1>
        <p class="page-subtitle">Send messages to multiple recipients</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><h5><i class="bi bi-envelope-paper-fill me-2"></i>Bulk Message</h5></div>
            <div class="card-body">
                <form method="POST" action="/sms/bulk" enctype="multipart/form-data">
                    <?= Csrf::field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Sender ID</label>
                            <select class="form-select" name="sender_id">
                                <option value="">Default</option>
                                <?php foreach ($senderIds as $sid): ?>
                                    <option value="<?= htmlspecialchars($sid['sender_id']) ?>"><?= htmlspecialchars($sid['sender_id']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Schedule (Optional)</label>
                            <input type="datetime-local" class="form-control" name="schedule_at">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Recipients</label>
                            <ul class="nav nav-tabs mb-3" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#manualTab">Manual Entry</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#groupTab">From Groups</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#csvTab">CSV Upload</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="manualTab">
                                    <textarea class="form-control" name="numbers" rows="5" placeholder="Enter phone numbers (one per line, or comma-separated)&#10;+1234567890&#10;+4412345678"></textarea>
                                </div>
                                <div class="tab-pane fade" id="groupTab">
                                    <?php if (empty($groups)): ?>
                                        <p class="text-muted">No groups yet. <a href="/contacts/groups">Create one</a></p>
                                    <?php else: ?>
                                        <?php foreach ($groups as $group): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="group_ids[]" value="<?= $group['id'] ?>" id="group_<?= $group['id'] ?>">
                                                <label class="form-check-label" for="group_<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?> <span class="text-muted">(<?= $group['member_count'] ?? $group['contacts_count'] ?> contacts)</span></label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="tab-pane fade" id="csvTab">
                                    <input type="file" class="form-control" name="csv_file" accept=".csv,.txt,.xlsx">
                                    <div class="form-text">Upload CSV/TXT file with phone numbers in the first column</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="message" rows="5" placeholder="Type your message..." data-sms-counter="#bulkSmsCounter" required></textarea>
                            <div id="bulkSmsCounter" class="form-text mt-1"><span>0</span> chars | <span>1</span> part(s) | GSM</div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-send-fill"></i> Send Bulk SMS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h5><i class="bi bi-wallet2 me-2"></i>Balance</h5></div>
            <div class="card-body text-center">
                <div class="stat-card-value text-accent"><?= number_format((float)(\App\Core\Session::getUser()['sms_balance'] ?? 0), 0) ?></div>
                <div class="stat-card-label">SMS Credits Remaining</div>
            </div>
        </div>
    </div>
</div>
