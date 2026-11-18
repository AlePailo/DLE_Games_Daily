<?php declare(strict_types = 1);

namespace App\Console;

use App\Core\Transaction;
use App\Model\Entity\UserFranchiseStats;

/**
 * Reconstruct user_franchise_stats starting from closed sessions, re-applying in chronological order
 * the same rule used in game logic (UserFranchiseStats::updateStats).
 * Without $apply only shows the differences.
 */
class RebuildUserStatsCommand {
    public function __construct(
        private \PDO $pdo,
        private Transaction $transaction
    ) {}

    public function execute(bool $apply = false) : void {
        $rebuilt = $this->replaySessions();
        $current = $this->loadCurrentStats();

        $toWrite = [];
        foreach($rebuilt as $key => $stats) {
            $diffs = $this->diff($current[$key] ?? null, $stats);

            if($diffs === []) {
                continue;
            }

            $toWrite[$key] = $stats;
            [$userId, $franchiseId] = explode(':', $key);
            echo "user $userId / franchise $franchiseId: " . ($diffs === ['new'] ? 'missing row, it will be created' : implode(', ', $diffs)) . "\n";
        }

        foreach(array_diff_key($current, $rebuilt) as $key => $row) {
            [$userId, $franchiseId] = explode(':', $key);
            echo "user $userId / franchise $franchiseId: statis without closed sessions (untouched).\n";
        }

        if($toWrite === []) {
            echo "No differences: stats match with sessions.\n";
            return;
        }

        if(!$apply) {
            echo "\nDry run: " . count($toWrite) . " rows need to be updated. Run the script again with --apply to proceed with the update.\n";
            return;
        }

        $this->transaction->run(function () use ($toWrite, $current) {
            foreach($toWrite as $key => $stats) {
                isset($current[$key]) ? $this->update($stats) : $this->insert($stats);
            }
        });

        echo "\n" . count($toWrite) . " row(s) updated.\n";
    }

    /** @return array<string, UserFranchiseStats> key "userId:franchiseId" */
    private function replaySessions() : array {
        $rows = $this->pdo->query("SELECT gs.user_id, dc.franchise_id, dc.challenge_date, gs.solved, gs.attempts_count
            FROM game_sessions gs
            INNER JOIN daily_challenges dc ON dc.id = gs.challenge_id
            WHERE gs.user_id IS NOT NULL AND gs.completed_at IS NOT NULL
            ORDER BY gs.user_id, dc.franchise_id, dc.challenge_date")->fetchAll();

        $rebuilt = [];
        foreach($rows as $row) {
            $key = $row['user_id'] . ':' . $row['franchise_id'];

            $rebuilt[$key] ??= new UserFranchiseStats(
                id: 0, userId: (int)$row['user_id'], franchiseId: (int)$row['franchise_id'],
                gamesPlayed: 0, gamesWon: 0, currentStreak: 0, maxStreak: 0, avgAttempts: 0.0,
                lastWinDate: null, updatedAt: new \DateTimeImmutable()
            );

            $rebuilt[$key]->updateStats(
                (bool)$row['solved'],
                (int)$row['attempts_count'],
                new \DateTimeImmutable($row['challenge_date'])
            );
        }

        return $rebuilt;
    }

    private function loadCurrentStats() : array {
        $current = [];
        foreach($this->pdo->query("SELECT * FROM user_franchise_stats")->fetchAll() as $row) {
            $current[$row['user_id'] . ':' . $row['franchise_id']] = $row;
        }

        return $current;
    }

    /** @return string[] readable differences; ['new'] if row doesn't exist currently */
    private function diff(?array $row, UserFranchiseStats $stats) : array {
        if($row === null) {
            return ['new'];
        }

        $diffs = [];
        $compare = [
            'played' => [(int)$row['games_played'], $stats->getGamesPlayed()],
            'won' => [(int)$row['games_won'], $stats->getGamesWon()],
            'streak' => [(int)$row['current_streak'], $stats->getCurrentStreak()],
            'max_streak' => [(int)$row['max_streak'], $stats->getMaxStreak()],
        ];
        foreach($compare as $label => [$old, $new]) {
            if($old !== $new) $diffs[] = "{$label} {$old}→{$new}";
        }

        if(abs((float)$row['avg_attempts'] - $stats->getAvgAttempts()) > 0.0001) {
            $diffs[] = 'avg ' . round((float)$row['avg_attempts'], 2) . '→' . round($stats->getAvgAttempts(), 2);
        }

        $newLastWin = $stats->getLastWinDate()?->format('Y-m-d');
        if(($row['last_win_date'] ?? null) !== $newLastWin) {
            $diffs[] = 'last_win ' . ($row['last_win_date'] ?? 'null') . '→' . ($newLastWin ?? 'null');
        }

        return $diffs;
    }

    private function insert(UserFranchiseStats $stats) : void {
        $stmt = $this->pdo->prepare("INSERT INTO user_franchise_stats
            (user_id, franchise_id, games_played, games_won, current_streak, max_streak, avg_attempts, last_win_date)
            VALUES (:user_id, :franchise_id, :games_played, :games_won, :current_streak, :max_streak, :avg_attempts, :last_win_date)");
        $stmt->execute($this->params($stats) + ['user_id' => $stats->getUserId(), 'franchise_id' => $stats->getFranchiseId()]);
    }

    private function update(UserFranchiseStats $stats) : void {
        $stmt = $this->pdo->prepare("UPDATE user_franchise_stats SET
            games_played = :games_played, games_won = :games_won, current_streak = :current_streak,
            max_streak = :max_streak, avg_attempts = :avg_attempts, last_win_date = :last_win_date
            WHERE user_id = :user_id AND franchise_id = :franchise_id");
        $stmt->execute($this->params($stats) + ['user_id' => $stats->getUserId(), 'franchise_id' => $stats->getFranchiseId()]);
    }

    private function params(UserFranchiseStats $stats) : array {
        return [
            'games_played'   => $stats->getGamesPlayed(),
            'games_won'      => $stats->getGamesWon(),
            'current_streak' => $stats->getCurrentStreak(),
            'max_streak'     => $stats->getMaxStreak(),
            'avg_attempts'   => $stats->getAvgAttempts(),
            'last_win_date'  => $stats->getLastWinDate()?->format('Y-m-d'),
        ];
    }
}