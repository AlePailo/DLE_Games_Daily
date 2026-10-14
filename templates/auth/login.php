<?php
/**
 * @var string $csrfToken
 * @var array $old
 * @var bool $show_resend 
 */
?>
<div class="auth-wrapper">
    <section class="form-container">
        <h1>Login</h1>
        <form action="<?= BASE_URL ?>/login" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required aria-required="true">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required aria-required="true" data-rules="required|min:8">
            </div>
            <div class="form-check">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Remember this device</label>
            </div>
            <button type="submit" class="btn-resend">Login</button>
        </form>
        <p class="auth-footer">Don't have an account? <a href="<?=  BASE_URL ?>/register">Register now!</a></p>
    </section>
    <?php if ($show_resend ?? false): ?>
        <div class="resend-section">
            <p>Didn't receive the verification email?</p>
            <form method="POST" action="<?= BASE_URL ?>/resend-verification">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input 
                    type="email" 
                    name="email" 
                    value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                    placeholder="Your email address"
                    required
                >
                <button type="submit" class="btn-resend">Resend verification email</button>
            </form>
        </div>
        <script>
            document.getElementById('resend-section')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
        </script>
    <?php endif; ?>
    <p class="auth-back">
        <a href="<?= BASE_URL ?>">← Back to home</a>
    </p>
</div>