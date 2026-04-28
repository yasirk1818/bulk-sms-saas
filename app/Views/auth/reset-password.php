<?php use App\Core\Csrf; ?>

<form method="POST" action="/reset-password">
    <?= Csrf::field() ?>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

    <div class="mb-3">
        <label class="form-label">New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" name="password" placeholder="Min. 8 characters" minlength="8" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm password" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">
        <i class="bi bi-check-circle"></i> Reset Password
    </button>

    <div class="auth-footer">
        <a href="/login"><i class="bi bi-arrow-left"></i> Back to Login</a>
    </div>
</form>
