<?php 
/**
 * @var string  $content        Template dynamic content (injected from view)
 * @var bool    $withNav        Whether pages require navigation or not
 */
?>

<?php

if(!function_exists('checkActive')) {
    function checkActive(string $linkPath) : string {
        $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if($linkPath === '/') {
            $isActive = $currentUri === '/';
        } else {
            $isActive = ($currentUri === $linkPath || str_starts_with($currentUri, $linkPath));
        }

        return $isActive ? 'class="active" aria-current="page"' : '';
    }
}

?>

<?php require BASE_PATH . 'templates/layout/header.php';
require BASE_PATH . 'templates/layout/alerts.php'; ?>

<?php if($withNav): ?>
    <div class="app-layout">
        <?php require BASE_PATH . 'templates/layout/sidebar.php'; ?>
        <div class="main-wrapper">
            <?php require BASE_PATH . 'templates/layout/headbar.php'; ?>
            <main id="main-content">
                <?= $content ?>
            </main>
        </div>
    </div>
    <?php require 'templates/layout/bottombar.php'; ?>
<?php else: ?>
    <main id="main-content">
        <?= $content ?>
    </main>
<?php endif; ?>

<?php require BASE_PATH . 'templates/layout/footer.php'; ?>