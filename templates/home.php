<?php 
/**
 * @var $favourites         Array containing user's favourite franchises objects
 */
?>

<section 
    id="favourites-grid"
    class="franchises-grid"
    aria-label="Lista dei franchise di gioco"
>
    <?php if (!empty($favourites)): ?>
        <?php foreach ($favourites as $franchise) {
            $isFavourite = true; 

            require BASE_PATH . 'templates/components/franchise-card.php';
        }?>
    <?php else: ?>
        <div class="empty-state" role="status">
            <p>No franchises found</p>
        </div>
    <?php endif; ?>
</section>