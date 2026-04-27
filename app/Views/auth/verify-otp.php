<?php use App\Core\Csrf; use App\Core\Session; ?>

<?php $debugCode = Session::getFlash('otp_code_debug'); ?>
<?php if ($debugCode): ?>
    <div class="alert alert-info fs-sm">
        <i class="bi bi-info-circle"></i> Debug OTP Code: <strong><?= $debugCode ?></strong>
        <small class="d-block mt-1">(In production, this would be sent via email/SMS)</small>
    </div>
<?php endif; ?>

<form method="POST" action="/verify-otp">
    <?= Csrf::field() ?>

    <p class="text-muted fs-sm mb-3">Enter the 6-digit verification code sent to your email/phone.</p>

    <div class="mb-3">
        <label class="form-label">Verification Code</label>
        <input type="text" class="form-control text-center" name="code" placeholder="000000"
               maxlength="6" pattern="[0-9]{6}" style="font-size:1.5rem;letter-spacing:8px" required autofocus>
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">
        <i class="bi bi-shield-check"></i> Verify
    </button>

    <div class="auth-footer">
        <span class="text-muted">Didn't receive the code?</span>
        <a href="#" onclick="resendOtp()" id="resendBtn">Resend Code</a>
    </div>
</form>

<script>
function resendOtp() {
    const btn = document.getElementById('resendBtn');
    btn.textContent = 'Sending...';
    btn.style.pointerEvents = 'none';

    fetch('/resend-otp', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.debug_code) {
            alert('New OTP: ' + data.debug_code);
        }
        btn.textContent = 'Code Sent!';
        setTimeout(() => { btn.textContent = 'Resend Code'; btn.style.pointerEvents = 'auto'; }, 60000);
    })
    .catch(() => { btn.textContent = 'Resend Code'; btn.style.pointerEvents = 'auto'; });
}
</script>
