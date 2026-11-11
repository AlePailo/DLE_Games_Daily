<?php
/**
 * Pagina di errore AUTONOMA: va inclusa con un semplice require (mai con View::render).
 * Non deve dipendere da layout, sessione, database o variabili passate dal chiamante.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something went wrong | DLE Games Daily</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/errors.css">
</head>
<body>
    <main class="error-page">
        <p class="error-code" aria-hidden="true">500</p>
        <h1 class="section-title">Something went wrong</h1>
        <p class="error-text">We couldn't complete your request. Please try again later.</p>
        <a href="<?= BASE_URL ?>/" class="btn btn-primary">Return to home</a>
    </main>
</body>
</html>