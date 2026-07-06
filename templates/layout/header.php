<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'DLE Games Daily'?></title>
    
    <?php if(isset($css) && is_array($css)): ?>
        <?php foreach($css as $sheet): ?>
            <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= htmlspecialchars($sheet) ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <script id="app-config" type="application/json">
        {
            "baseUrl": "<?= BASE_URL ?>"
        }
    </script>
</head>
<body>
    <?php require BASE_PATH . 'templates/layout/alerts.php' ?>