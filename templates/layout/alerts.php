<div id="flash-messages" class="container">
    <?php if(!empty($error)): ?>
        <div class="alert alert-error" role="alert" aria-live="assertive">
            <span class="sr-only">Error: </span><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($success)): ?>
        <div class="alert alert-success" role="alert" aria-live="assertive">
            <span class="sr-only">Error: </span><?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($info)): ?>
        <div class="alert alert-info" role="alert" aria-live="assertive">
            <span class="sr-only">Error: </span><?= htmlspecialchars($info) ?>
        </div>
    <?php endif; ?>
</div>