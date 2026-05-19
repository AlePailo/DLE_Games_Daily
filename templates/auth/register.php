<?php
/** @var string $csrf_token */
?>
<div class="auth-wrapper">
    <section class="form-container">
        <h1>Create an account</h1>
        <form action="<?= BASE_URL ?>/register" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($old['username'] ?? '') ?>" required aria-required="true" data-rules="required|min:3">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required aria-required="true" data-rules="required|email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required aria-required="true" data-rules="required|min:8">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required aria-required="true" data-rules="required|match:password">
            </div>
            <button type="submit">Register</button>
        </form>
        <p class="auth-footer">Already got an account? <a href="<?=  BASE_URL ?>/login">Go to login page</a></p>
    </section>
</div>