<?php
/**
 * @var array  $leaderboard   Array of leaderboard entries
 * @var array  $franchises    Array of Franchise entities
 * @var string $metric        Current metric: 'avg_attempts' | 'max_streak' | 'games_played'
 * @var string $franchise     Current franchise: 'global' | franchise id
 * @var string $csrfToken
 */

$metrics = [
    'avg_attempts' => 'Avg Attempts',
    'max_streak'   => 'Best Streak',
    'games_played' => 'Games Played',
];
?>

<div class="leaderboards-container f-col-container">

    <header class="leaderboards-header">
        <h1 class="section-title">Leaderboards</h1>

        <!-- Franchise dropdown -->
        <div class="franchise-select-wrap">
            <select id="franchise-select" class="franchise-select" aria-label="Select franchise">
                <option value="global" <?= $franchise === 'global' ? 'selected' : '' ?>>
                    Global
                </option>
                <?php foreach ($franchises as $f): ?>
                    <option value="<?= $f->getId() ?>" <?= $franchise == $f->getId() ? 'selected' : '' ?>>
                        <?= htmlspecialchars($f->getName()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </header>

    <!-- Metric tabs -->
    <div class="metric-tabs" role="tablist" aria-label="Leaderboard metric">
        <?php foreach ($metrics as $key => $label): ?>
            <button
                type="button"
                class="metric-tab <?= $metric === $key ? 'active' : '' ?>"
                data-metric="<?= $key ?>"
                role="tab"
                aria-selected="<?= $metric === $key ? 'true' : 'false' ?>"
            >
                <?= $label ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Leaderboard table -->
    <div class="leaderboard-wrap section-card" id="leaderboard-wrap">
        <?php include __DIR__ . '/components/leaderboard-table.php' ?>
    </div>

</div>