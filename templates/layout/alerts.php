<div id="toast-container" class="toast-container">
    <?php 
    // Messaggi passati dal Controller via PHP
    $types = [
        'error'   => $error   ?? null, 
        'success' => $success ?? null, 
        'info'    => $info    ?? null
    ];

    foreach ($types as $type => $message): 
        if ($message): 
            //Split multiple messages
            $message = str_replace(['<br/>', '<br />', "\n"], '<br>', $message);
            $lines = array_filter(explode('<br>', $message)); 
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
                            <p><?= htmlspecialchars(trim($lines[0])) ?></p>
                        <?php endif; ?>
                        
                    </div>
                </div>
                <button type="button" class="toast-close">&times;</button>
                <div class="toast-progress"></div>
            </div>
        <?php endif;
    endforeach; ?>

</div>