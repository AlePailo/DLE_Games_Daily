<?php 
/**
 * @var string  $content        Template dynamic content (injected from view)
 * @var bool    $withNav        Whether pages require navigation or not
 */
?>

<?php


if(!function_exists('checkActive')) {
    function checkActive(string $linkPath) : string {
        $currentUri = strtok($_SERVER['REQUEST_URI'], '?');
        $basePath = $_ENV['APP_BASE_PATH'] ?? '/';
        if (str_starts_with($currentUri, $basePath)) {
            $currentUri = '/' . ltrim(substr($currentUri, strlen($basePath)), '/');
        }

        if($linkPath === '/') {
            $isActive = $currentUri === '/';
        } else {
            $isActive = ($currentUri === $linkPath || str_starts_with($currentUri, $linkPath));
        }

        return $isActive ? 'class="active" aria-current="page"' : '';
    }
}

?>

<?php require BASE_PATH . 'templates/layout/header.php'; ?>

<?php if($withNav): ?>
    <div class="app-layout" id="app-layout">
        <script>
            if (localStorage.getItem('sidebar-state') === 'collapsed') {
                document.getElementById('app-layout').classList.add('sidebar-collapsed');
            }
        </script>
        <?php require BASE_PATH . 'templates/layout/sidebar.php'; ?>
        <div class="main-wrapper">
            <?php require BASE_PATH . 'templates/layout/headbar.php'; ?>
            <main id="main-content">
                <?= $content ?>
            </main>
            <?php require BASE_PATH . 'templates/layout/footer.php'; ?>
        </div>
    </div>
    <?php require BASE_PATH . 'templates/layout/bottombar.php'; ?>
<?php else: ?>
    <div class="main-wrapper">
        <main id="main-content">
            <?= $content ?>
        </main>
        <?php require BASE_PATH . 'templates/layout/footer.php'; ?>
    </div>
<?php endif; ?>