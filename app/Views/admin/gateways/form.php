<?php use App\Core\Csrf; /** @var array|null $gateway */ $isEdit = $gateway !== null; ?>
<div class="page-header"><div><h1 class="page-title"><?= $isEdit ? 'Edit' : 'Add' ?> Gateway</h1></div></div>
<div class="card"><div class="card-body">
    <form method="POST" action="<?= $isEdit ? '/admin/gateways/' . $gateway['id'] : '/admin/gateways' ?>">
        <?= Csrf::field() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Gateway Name</label><input type="text" class="form-control" name="name" value="<?= htmlspecialchars($gateway['name'] ?? '') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Type</label><select class="form-select" name="type">
                <?php foreach(['http_get'=>'HTTP GET','http_post'=>'HTTP POST','json_api'=>'JSON API'] as $v=>$l): ?><option value="<?= $v ?>" <?= ($gateway['type'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
            </select></div>
            <div class="col-12"><label class="form-label">API URL</label><input type="url" class="form-control" name="api_url" value="<?= htmlspecialchars($gateway['api_url'] ?? '') ?>" required></div>
            <div class="col-md-6"><label class="form-label">API Key</label><input type="text" class="form-control" name="api_key" value="<?= htmlspecialchars($gateway['api_key'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">API Secret</label><input type="text" class="form-control" name="api_secret" value="<?= htmlspecialchars($gateway['api_secret'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Recipient Param</label><input type="text" class="form-control" name="recipient_param" value="<?= htmlspecialchars($gateway['recipient_param'] ?? 'to') ?>"></div>
            <div class="col-md-4"><label class="form-label">Message Param</label><input type="text" class="form-control" name="message_param" value="<?= htmlspecialchars($gateway['message_param'] ?? 'message') ?>"></div>
            <div class="col-md-4"><label class="form-label">Sender ID Param</label><input type="text" class="form-control" name="sender_id_param" value="<?= htmlspecialchars($gateway['sender_id_param'] ?? 'from') ?>"></div>
            <div class="col-md-4"><label class="form-label">Default Sender ID</label><input type="text" class="form-control" name="default_sender_id" value="<?= htmlspecialchars($gateway['default_sender_id'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Cost Per SMS</label><input type="number" class="form-control" name="cost_per_sms" value="<?= $gateway['cost_per_sms'] ?? '0.0100' ?>" step="0.0001" min="0"></div>
            <div class="col-md-4"><label class="form-label">Priority (lower = higher)</label><input type="number" class="form-control" name="priority" value="<?= $gateway['priority'] ?? 50 ?>" min="1" max="100"></div>
            <div class="col-md-3"><label class="form-label">Rate Limit (msg/min)</label><input type="number" class="form-control" name="rate_limit" value="<?= $gateway['rate_limit'] ?? 30 ?>"></div>
            <div class="col-md-3"><label class="form-label">Timeout (sec)</label><input type="number" class="form-control" name="timeout" value="<?= $gateway['timeout'] ?? 30 ?>"></div>
            <div class="col-md-3"><label class="form-label">Retry Attempts</label><input type="number" class="form-control" name="retry_attempts" value="<?= $gateway['retry_attempts'] ?? 3 ?>"></div>
            <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" <?= ($gateway['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($gateway['status'] ?? 'inactive') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
            <div class="col-md-6"><label class="form-label">Extra Params (JSON)</label><textarea class="form-control" name="extra_params" rows="3"><?= htmlspecialchars($gateway['extra_params'] ?? '{}') ?></textarea></div>
            <div class="col-md-6"><label class="form-label">Custom Headers (JSON)</label><textarea class="form-control" name="headers" rows="3"><?= htmlspecialchars($gateway['headers'] ?? '{}') ?></textarea></div>
            <div class="col-12">
                <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="supports_unicode" id="supUni" <?= ($gateway['supports_unicode'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label" for="supUni">Supports Unicode</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="supports_dlr" id="supDlr" <?= ($gateway['supports_dlr'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label" for="supDlr">Supports DLR</label></div>
            </div>
        </div>
        <div class="mt-4"><button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> <?= $isEdit ? 'Update' : 'Create' ?> Gateway</button> <a href="/admin/gateways" class="btn btn-secondary">Cancel</a></div>
    </form>
</div></div>
