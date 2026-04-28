<?php use App\Core\Csrf; ?>

<form method="POST" action="/login">
    <?= Csrf::field() ?>

    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" name="email" placeholder="name@example.com" required autofocus>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label d-flex justify-content-between">
            <span>Password</span>
            <a href="/forgot-password" class="fs-xs">Forgot password?</a>
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
        </div>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label fs-sm" for="remember">Remember me</label>
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">
        <i class="bi bi-box-arrow-in-right"></i> Sign In
    </button>

    <div class="auth-footer">
        Don't have an account? <a href="/register">Create one</a>
    </div>
</form>
