<?php 
/**
 * @var string|null $username
 * @var string|null $user_icon_url 
 */

$isLoggedIn = !empty($username);

?>

<header class="headbar">
    <?php if($isLoggedIn): ?>
        <a href="<?= BASE_URL ?>/profile" aria-label="Your profile" class="user-profile">
            <span><?= htmlspecialchars($username) ?></span>
            <img src="<?= BASE_PATH ?>/assets/img/<?= htmlspecialchars($user_icon_url ?? BASE_URL . '/assets/img/default-avatar.png') ?>" alt="Your avatar">
        </a>
    <?php else: ?>
        <div class="auth-actions">
            <a href="<?= BASE_URL ?>/login" class="btn btn-secondary">Login</a>
            <a href="<?= BASE_URL ?>/register" class="btn btn-primary">Sign Up</a>
        </div>
    <?php endif; ?>
</header>