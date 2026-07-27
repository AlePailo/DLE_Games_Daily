<?php
/**
 * @var object $franchise
 */
?>

<style>
    .main-wrapper {
        --franchise-backdrop: url('<?= BASE_URL ?>/assets/img/backgrounds/<?= $franchise->getBgImageUrl() ?>');
    }
</style>

<div class="game-container unavailable-container">
    <header class="game-header">
        <h1 class="section-title"><?= htmlspecialchars($franchise->getName()) ?></h1>
    </header>

    <div class="alert-box info-box">
        <h2>Today's challenge isn't out yet</h2>
        <a href="<?= BASE_URL ?>/" class="btn btn-primary">Home</a>
    </div>
</div>