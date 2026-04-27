<?php use App\Core\Csrf; ?>

<form method="POST" action="/forgot-password">
    <?= Csrf::field() ?>

    <p class="text-muted fs-sm mb-3">Enter your email address and we'll send you a link to reset your password.</p>

    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" name="email" placeholder="name@example.com" required autofocus>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">
        <i class="bi bi-send"></i> Send Reset Link
    </button>

    <div class="auth-footer">
        <a href="/login"><i class="bi bi-arrow-left"></i> Back to Login</a>
    </div>
</form>
