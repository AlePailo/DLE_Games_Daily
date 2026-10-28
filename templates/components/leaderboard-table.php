<?php
/**
 * @var array  $leaderboard
 * @var string $metric
 */

$metricLabels = [
    'avg_attempts' => 'Avg Attempts',
    'max_streak'   => 'Best Streak',
    'games_played' => 'Games Played',
];

$metricLabel = $metricLabels[$metric] ?? $metric;
?>

<?php if (empty($leaderboard)): ?>
    <p class="leaderboard-empty">No data available yet. Be the first to play!</p>
<?php else: ?>
    <table class="leaderboard-table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Player</th>
                <th scope="col"><?= htmlspecialchars($metricLabel) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leaderboard as $i => $entry): ?>
                <tr class="leaderboard-row <?= $i < 3 ? 'leaderboard-top-' . ($i + 1) : '' ?>">
                    <td class="leaderboard-rank">
                        <?php if ($i === 0): ?>🥇
                        <?php elseif ($i === 1): ?>🥈
                        <?php elseif ($i === 2): ?>🥉
                        <?php else: ?><?= $i + 1 ?>
                        <?php endif; ?>
                    </td>
                    <td class="leaderboard-player">
                        <img
                            src="<?= BASE_URL ?>/assets/img/avatars/<?= htmlspecialchars($entry['user_icon_url'] ?? 'default.png') ?>"
                            alt="<?= htmlspecialchars($entry['username']) ?>"
                            class="leaderboard-avatar"
                        >
                        <span><?= htmlspecialchars($entry['username']) ?></span>
                    </td>
                    <td class="leaderboard-value">
                        <?php if ($metric === 'avg_attempts'): ?>
                            <?= number_format((float)($entry['avg_attempts'] ?? 0), 2) ?>
                        <?php else: ?>
                            <?= (int)($entry[$metric] ?? 0) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>