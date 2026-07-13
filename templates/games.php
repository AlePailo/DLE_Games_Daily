<?php
/**
 *  @var array $franchises      Array containing all franchises objects
 *  @var array $favouriteIds    Array containing user's favourite franchises' ids
 *  @var bool $isLoggedIn        Indicates if user is logged in or in guest mode
 */
?>

<div class="f-col-container">
    
    <div class="games-header-section">
        <div class="header-titles">
            <h1>Franchises list</h1>
            <p>Pick one and guess today's character</p>
        </div>
        
        <div class="search-wrapper">
            <span class="search-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg></span>
            
            <input 
                type="search" 
                id="search-input" 
                placeholder="Search for a franchise..." 
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
                $isFavourite = $isLoggedIn && in_array($franchise->getId(), $favouriteIds, true);

                require BASE_PATH . 'templates/components/franchise-card.php';
            }?>
        <?php else: ?>
            <div class="empty-state" id="empty-state" role="status">
                <p>No franchises found</p>
            </div>
        <?php endif; ?>
    </section>
</div>