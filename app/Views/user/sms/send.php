<?php
use App\Core\Csrf;
/** @var array $senderIds */
/** @var array $templates */
/** @var array $groups */
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Send SMS</h1>
        <p class="page-subtitle">Send a single SMS message</p>
    </div>
    <nav>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Send SMS</li>
        </ol>
    </nav>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-send-fill me-2"></i>Compose Message</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/sms/send" id="smsForm">
                    <?= Csrf::field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Recipient Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="text" class="form-control" name="recipient" placeholder="+1234567890" required>
                            </div>
                            <div class="form-text">Include country code (e.g., +1 for US)</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Sender ID</label>
                            <select class="form-select" name="sender_id">
                                <option value="">Default Gateway Sender</option>
                                <?php foreach ($senderIds as $sid): ?>
                                    <option value="<?= htmlspecialchars($sid['sender_id']) ?>"><?= htmlspecialchars($sid['sender_id']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Template (Optional)</label>
                            <select class="form-select" id="templateSelect">
                                <option value="">Write custom message</option>
                                <?php foreach ($templates as $tpl): ?>
                                    <option value="<?= htmlspecialchars($tpl['content']) ?>"><?= htmlspecialchars($tpl['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="message" rows="5" placeholder="Type your message here..." data-sms-counter="#smsCounter" required></textarea>
                            <div class="d-flex justify-content-between mt-2">
                                <div id="smsCounter" class="form-text">
                                    <span>0</span> chars | <span>1</span> part(s) | GSM
                                </div>
                                <div class="form-text">
                                    Variables: {name}, {phone}, {email}, {company}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Schedule (Optional)</label>
                            <input type="datetime-local" class="form-control" name="schedule_at">
                            <div class="form-text">Leave empty to send immediately</div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send-fill"></i> Send SMS
                        </button>
                        <a href="/sms/history" class="btn btn-secondary btn-lg ms-2">
                            <i class="bi bi-clock-history"></i> View History
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header"><h5><i class="bi bi-info-circle me-2"></i>SMS Info</h5></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Your Balance</span>
                    <strong class="text-accent"><?= number_format((float)(\App\Core\Session::getUser()['sms_balance'] ?? 0), 0) ?> SMS</strong>
                </div>
                <hr style="border-color:var(--border-color)">
                <h6 class="mb-2 fs-sm">Character Limits</h6>
                <table class="table table-sm fs-xs mb-0">
                    <tr><td>GSM (Standard)</td><td>160 chars / 153 multipart</td></tr>
                    <tr><td>Unicode</td><td>70 chars / 67 multipart</td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5><i class="bi bi-lightning me-2"></i>Quick Actions</h5></div>
            <div class="card-body">
                <a href="/sms/bulk" class="btn btn-outline-primary w-100 mb-2"><i class="bi bi-envelope-paper"></i> Bulk SMS</a>
                <a href="/campaigns/create" class="btn btn-outline-primary w-100 mb-2"><i class="bi bi-megaphone"></i> New Campaign</a>
                <a href="/contacts" class="btn btn-outline-primary w-100"><i class="bi bi-person-lines-fill"></i> Contacts</a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('templateSelect')?.addEventListener('change', function() {
    if (this.value) {
        document.querySelector('textarea[name="message"]').value = this.value;
        document.querySelector('textarea[name="message"]').dispatchEvent(new Event('input'));
    }
});
</script>
