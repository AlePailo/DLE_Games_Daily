<?php
/**
 *  @var array $franchises
 *  @var array $favouriteIds
 *  @var bool $isGuest
 */
?>

<div class="games-page-container" data-is-guest="<?= $isGuest ? 'true' : 'false' ?>">
    
    <div class="games-header-section">
        <div class="header-titles">
            <h1>Franchises list</h1>
            <p>Pick one and guess today's character</p>
        </div>
        
        <div class="search-wrapper">
            <span class="search-icon" aria-hidden="true">🔍</span>
            
            <input 
                type="search" 
                id="search-input" 
                placeholder="Cerca un gioco o un anime..." 
                autocomplete="off"
                value=""
                aria-label="Cerca tra i franchise disponibili"
                aria-controls="franchises-grid"
            >
        </div>
    </div>

    <div id="search-results-status" class="sr-only" aria-live="polite"></div>

    <section 
        class="franchises-grid" 
        id="franchises-grid" 
        aria-label="Lista dei franchise di gioco"
    >
        <?php if (!empty($franchises)): ?>
            <?php foreach ($franchises as $franchise) {
                $isFavourite = !$isGuest && in_array($franchise->getId(), $favouriteIds, true);

                require BASE_PATH . 'templates/components/franchise-card.php';
            }?>
        <?php else: ?>
            <div class="empty-state" id="empty-state" role="status">
                <p>No franchises found</p>
            </div>
        <?php endif; ?>
    </section>
</div>