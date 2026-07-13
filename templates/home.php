<?php 
/**
 * @var array $favourites         Array containing user's favourite franchises objects
 * @var bool $isLoggedIn
 */
?>

<div class="f-col-container">
    <?php if(!$isLoggedIn): ?>
        <section class="home-hero section-card" aria-labelledby="hero-title">
            <div class="hero-content">
                <h1 id="hero-title">Want the full experience ?</h1>
                <p>Keep your progress, save your favourites and suggest new franchises.</p>
                <a href="<?= BASE_URL ?>/register" class="btn btn-primary">Join the Community</a>
            </div>
        </section>

        <section class="onboarding-section" aria-labelledby="onboarding-title">
            <h2 id="onboarding-title" class="sr-only">How it works</h2>
            <div class="onboarding-grid">
                <div class="step section-card">
                    <span class="step-icon-wrapper" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sword-icon lucide-sword"><path d="m11 19-6-6"/><path d="m5 21-2-2"/><path d="m8 16-4 4"/><path d="M9.5 17.5 21 6V3h-3L6.5 14.5"/></svg>
                    </span>
                    <h3>1. Play daily</h3>
                    <p>Guess the daily character from a database of gaming and anime franchises.</p>
                </div>
                <div class="step section-card">
                    <span class="step-icon-wrapper" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star-icon lucide-star"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>
                    </span>
                    <h3>2. Choose your favourites</h3>
                    <p>Keep track of your favorites to access them instantly.</p>
                </div>
                <div class="step section-card">
                    <span class="step-icon-wrapper" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square-icon lucide-message-square"><path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/></svg>
                    </span>
                    <h3>3. Contribute</h3>
                    <p>Help us grow by suggesting franchises to add.</p>
                </div>
                <div class="step section-card">
                    <span class="step-icon-wrapper" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/></svg>
                    </span>
                    <h3>4. Make friends</h3>
                    <p>Add friends and compare your stats with them.</p>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="favourites-section" aria-labelledby="favourites-title">
            <h2 id="favourites-title" class="section-title">Your Favourites</h2>
            
            <div 
                id="favourites-grid"
                class="franchises-grid"
                aria-label="Your favourite franchises"
                aria-live="polite"
                data-empty-message="No favourite franchises yet."
            >
                <?php if (!empty($favourites)): ?>
                    <?php foreach ($favourites as $franchise) {
                        $isFavourite = true; 
                        require BASE_PATH . 'templates/components/franchise-card.php';
                    } ?>
                <?php else: ?>
                    <div class="empty-state" role="status">
                        <p class="empty-state-message">No favourite franchises yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="cta-contribute section-card" aria-labelledby="cta-title">
            <div class="cta-box">
                <div>
                    <h2 id="cta-title">Is your favorite franchise missing?</h2>
                    <p>This catalog is built by passionate players. Help us keep it updated!</p>
                </div>
                <a class="btn btn-primary" href="<?= BASE_URL ?>/contribute" aria-label="Submit a missing franchise">
                    Suggest a Franchise
                </a>
            </div>
        </section>
    <?php endif; ?>
</div>