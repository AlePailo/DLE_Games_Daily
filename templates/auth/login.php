<?php
/** @var string $csrf_token */
?>
<section class="login-container">
    <h1>Login</h1>
    <form action="<?= BASE_URL ?>/login" method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="form-group">
            <label for="email">Indirizzo Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required aria-required="true">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required aria-required="true">
        </div>
        <div class="form-check">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Remember this device</label>
        </div>
        <button type="submit">Login</button>
    </form>
</section>