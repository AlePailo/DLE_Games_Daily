<div id="toast-container" class="toast-container">
    <?php 
    $types = ['error', 'success', 'info'];
    foreach ($types as $type):
        $message = $$type ?? null;
        if($message === null) continue;

        $lines = $message !== null ? array_filter((array) $message) : [];
        
        if (empty($lines)) continue;
        ?>
        <div class="toast toast-<?= $type ?>" role="alert">
            <div class="toast-content">
                <span class="toast-icon"></span>
                <div class="toast-message">
                    <?php if (count($lines) > 1): ?>
                        <ul class="toast-list">
                            <?php foreach ($lines as $line): ?>
                                <li><?= htmlspecialchars(trim($line)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p><?= htmlspecialchars(reset($lines)) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <button type="button" class="toast-close" aria-label="Close notification">&times;</button>
            <div class="toast-progress"></div>
        </div>
    <?php endforeach; ?>
</div>