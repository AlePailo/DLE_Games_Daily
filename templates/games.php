<?php
/** @var array $franchises */
/** @var bool $isGuest */
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
            <?php foreach ($franchises as $franchise): ?>
                <div 
                    class="franchise-card" 
                    data-id="<?= $franchise['id'] ?>" 
                    data-name="<?= strtolower($franchise['name']) ?>"
                    aria-label="Franchise: <?= htmlspecialchars($franchise['name']) ?>"
                >
                    
                    <div class="card-banner" style="background-image: url('<?= BASE_URL ?>/assets/img/backgrounds/<?= $franchise['banner_url'] ?>');">
                        <button 
                            class="btn-favourite <?= $franchise['is_favourite'] ? 'is-favorite' : '' ?>"
                            data-id="<?= $franchise['id'] ?>"
                            aria-label="Inserisci <?= htmlspecialchars($franchise['name']) ?> nei preferiti"
                            aria-pressed="<?= $franchise['is_favourite'] ? 'true' : 'false' ?>"
                        >
                            <span aria-hidden="true">
                                <svg class="favourite-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star-icon lucide-star"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>
                            </span>
                            
                        </button>
                    </div>

                    <div class="card-content">
                        <div class="card-icon-wrapper" aria-hidden="true">
                            <img src="<?= BASE_URL ?>/assets/img/games_icons/<?= $franchise['icon_url'] ?>" alt="">
                        </div>
                        
                        <h3 class="franchise-title"><?= htmlspecialchars($franchise['name']) ?></h3>
                        
                        <a 
                            href="<?= BASE_URL ?>/play/<?= $franchise['slug'] ?>" 
                            class="btn-play"
                            aria-label="Gioca con il franchise <?= htmlspecialchars($franchise['name']) ?>"
                        >
                            <span>PLAY</span>
                            <span class="btn-arrow" aria-hidden="true">→</span>
                        </a>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" id="empty-state" role="status">
                <p>No franchises found</p>
            </div>
        <?php endif; ?>
    </section>
</div>