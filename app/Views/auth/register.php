<?php use App\Core\Csrf; ?>

<form method="POST" action="/register">
    <?= Csrf::field() ?>

    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" name="name" placeholder="John Doe" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Phone Number <span class="text-muted">(optional)</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-phone"></i></span>
            <input type="tel" class="form-control" name="phone" placeholder="+1234567890">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" name="password" placeholder="Min. 8 characters" minlength="8" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm password" required>
        </div>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="terms" required>
        <label class="form-check-label fs-sm" for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">
        <i class="bi bi-person-plus"></i> Create Account
    </button>

    <div class="auth-footer">
        Already have an account? <a href="/login">Sign in</a>
    </div>
</form>
