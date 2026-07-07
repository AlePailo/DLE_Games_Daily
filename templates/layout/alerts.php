<div id="toast-container" class="toast-container">
    <?php 
    $types = ['error', 'success', 'info'];
    foreach ($types as $type):
        $message = $$type ?? null; 
        
        if ($message):
            $lines = is_array($message) ? $message : array_filter(explode('<br>', str_replace(["\r\n", "\n", "<br />"], '<br>', $message)));
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
                            <p><?= htmlspecialchars(trim(reset($lines))) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <button type="button" class="toast-close">&times;</button>
                <div class="toast-progress"></div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>