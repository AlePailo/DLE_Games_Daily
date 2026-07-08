<?php
/**
 * @var object $franchise single franchise data
 * @var bool $isFavourite clarifies if the franchise is part of user's favourites
 */
?>

<div 
    class="franchise-card" 
    data-id="<?= $franchise->getId() ?>" 
    data-name="<?= strtolower($franchise->getName()) ?>"
    aria-label="Franchise: <?= htmlspecialchars($franchise->getName()) ?>"
>
    
    <div class="card-banner" style="background-image: url('<?= BASE_URL ?>/assets/img/backgrounds/<?= $franchise->getBgImageUrl() ?>');">
        <button 
            class="btn-favourite <?= $isFavourite ? 'is-favorite' : '' ?>"
            data-id="<?= $franchise->getId() ?>"
            aria-label="Inserisci <?= htmlspecialchars($franchise->getName()) ?> nei preferiti"
            aria-pressed="<?= $isFavourite ? 'true' : 'false' ?>"
        >
            <span aria-hidden="true">
                <svg class="favourite-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star-icon lucide-star"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>
            </span>
            
        </button>
    </div>

    <div class="card-content">
        <div class="card-icon-wrapper" aria-hidden="true">
            <img src="<?= BASE_URL ?>/assets/img/games_icons/<?= $franchise->getIconUrl() ?>" alt="">
        </div>
        
        <h3 class="franchise-title"><?= htmlspecialchars($franchise->getName()) ?></h3>
        
        <a 
            href="<?= BASE_URL ?>/play/<?= $franchise->getSlug() ?>" 
            class="btn-play"
            aria-label="Gioca con il franchise <?= htmlspecialchars($franchise->getName()) ?>"
        >
            <span>PLAY</span>
            <span class="btn-arrow" aria-hidden="true">→</span>
        </a>
    </div>

</div>