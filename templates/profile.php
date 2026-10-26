<?php
/**
 * @var object  $user             User object
 * 
 * @var array   $franchiseStats   Array di ['franchise' => Franchise, 'stats' => UserFranchiseStats]
 * @var array   $franchise_stats  Array [franchise_name => UserFranchiseStats]
 * 
 * @var array   $globalStats      ['games_played' => int, 'games_won' => int, 'win_rate' => float, 'max_streak' => int, 'avg_attempts' => float]
 * @var array   $avatars          Array di avatar predefiniti disponibili
 * @var string  $csrfToken
 */

?>

<?php

$hasFranchiseStats = !empty($franchise_stats);
$hasMultipleFranchises = count($franchise_stats) > 1;
$defaultFranchise = $hasFranchiseStats ? array_key_first($franchise_stats) : null;
?>

<div class="profile-container f-col-container">

    <!-- =====================================================================
         HEADER PROFILO
         ===================================================================== -->
    <section class="profile-header section-card" aria-label="Profile">
        <div class="profile-header-inner" id="profile-view">
            <div class="profile-avatar-wrap">
                <img
                    src="<?= BASE_URL ?>/assets/img/avatars/<?= htmlspecialchars($user->getUserIconUrl() ?? 'default.png') ?>"
                    alt="<?= htmlspecialchars($user->getUsername()) ?>'s avatar"
                    class="profile-avatar"
                    id="profile-avatar-img"
                >
            </div>
            <div class="profile-info">
                <h1 class="profile-username" id="profile-username-display">
                    <?= htmlspecialchars($user->getUsername()) ?>
                </h1>
                <p class="profile-since">Member since <?= $user->getCreatedAt()->format('F Y') ?></p>
            </div>
            <button type="button" class="btn btn-secondary profile-edit-btn" id="profile-edit-btn" aria-label="Edit profile">
                <svg aria-hidden="true" focusable="false" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </button>
        </div>

        <!-- Modalità modifica — nascosta di default -->
        <form
            class="profile-edit-form"
            id="profile-edit-form"
            method="POST"
            action="<?= BASE_URL ?>/profile/update"
            hidden
        >
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <!-- Username -->
            <div class="form-group">
                <label for="username-input">Username</label>
                <div class="input-with-status">
                    <input
                        type="text"
                        id="username-input"
                        name="username"
                        value="<?= htmlspecialchars($user->getUsername()) ?>"
                        autocomplete="username"
                        data-current="<?= htmlspecialchars($user->getUsername()) ?>"
                        aria-describedby="username-status"
                    >
                    <span id="username-status" class="username-status" aria-live="polite"></span>
                </div>
            </div>

            <!-- Selezione avatar -->
            <div class="avatar-picker">
                <p class="avatar-picker-label">Choose an avatar</p>
                <div class="avatar-grid" role="radiogroup" aria-label="Avatar selection">
                    <?php foreach ($avatars as $avatar): ?>
                        <label class="avatar-option <?= $user->getUserIconUrl() === $avatar ? 'selected' : '' ?>">
                            <input
                                type="radio"
                                name="avatar"
                                value="<?= htmlspecialchars($avatar) ?>"
                                <?= $user->getUserIconUrl() === $avatar ? 'checked' : '' ?>
                                class="sr-only"
                            >
                            <img
                                src="<?= BASE_URL ?>/assets/img/avatars/<?= htmlspecialchars($avatar) ?>"
                                alt="Avatar option"
                                class="avatar-option-img"
                            >
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="profile-edit-actions">
                <button type="button" class="btn btn-ghost" id="profile-cancel-btn">Cancel</button>
                <button type="submit" class="btn btn-primary" id="profile-save-btn">Save changes</button>
            </div>
        </form>
    </section>

    <!-- =====================================================================
         STATS GLOBALI
         ===================================================================== -->
    <section class="profile-section" aria-label="Overall stats">
        <h2 class="section-title underlined">Overall Stats</h2>
        <?php if ($hasFranchiseStats): ?>
            <div class="stats-global-grid">
                <div class="stat-card">
                    <span class="stat-value"><?= $globalStats['games_played'] ?></span>
                    <span class="stat-label">Games played</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value"><?= $globalStats['surrenders'] ?></span>
                    <span class="stat-label">Surrenders</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value"><?= $globalStats['max_streak'] ?></span>
                    <span class="stat-label">Best streak</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value"><?= number_format($globalStats['avg_attempts'], 2) ?></span>
                    <span class="stat-label">Avg attempts</span>
                </div>
            </div>
        <?php else: ?>
            <p class="empty-state">Play your first game to see your stats!</p>
        <?php endif; ?>
    </section>

    <!-- =====================================================================
         STATS PER FRANCHISE
         ===================================================================== -->
    <?php if ($hasFranchiseStats): ?>
        <section class="profile-section" aria-label="Franchise stats">
            <div class="franchise-stats-header">
                <h2 class="section-title underlined">Franchise Stats</h2>

                <div class="franchise-select-wrap">
                    <select
                        id="franchise-select"
                        class="franchise-select"
                        aria-label="Select franchise"
                        <?= !$hasMultipleFranchises ? 'disabled' : '' ?>
                    >
                        <?php foreach ($franchise_stats as $key => $value): ?>
                            <option value="<?= $value->getFranchiseId() ?>">
                                <?= htmlspecialchars($key) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php foreach ($franchise_stats as $key => $value): ?>
                <div
                    class="franchise-stats-panel"
                     id="stats-franchise-<?= $value->getFranchiseId() ?>"
                    <?= $key !== $defaultFranchise ? 'hidden' : '' ?>
                >
                    <div class="stats-franchise-grid">
                        <div class="stat-card">
                            <span class="stat-value"><?= $value->getGamesPlayed() ?></span>
                            <span class="stat-label">Games played</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value"><?= $value->getSurrenders() ?></span>
                            <span class="stat-label">Surrenders</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value"><?= $value->getCurrentStreak() ?></span>
                            <span class="stat-label">Current streak</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value"><?= $value->getMaxStreak() ?></span>
                            <span class="stat-label">Best streak</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value"><?= number_format($value->getAvgAttempts(), 2) ?></span>
                            <span class="stat-label">Avg attempts</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

</div>