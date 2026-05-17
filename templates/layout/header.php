<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'DLE Games Daily'?></title>
    <link rel="stylesheet" href="main.css">

    <?php if(isset($css) && is_array($css)): ?>
        <?php foreach($css as $sheet): ?>
            <link rel="stylesheet" href="css/<?= htmlspecialchars($sheet) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <main>
        <?php require BASE_PATH . 'templates/layout/alerts.php' ?>