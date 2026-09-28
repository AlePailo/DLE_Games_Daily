<?php
/**
 * @var object $franchise
 * @var array $characters
 * @var array $guessed_chars_ids
 * @var array $previous_guesses
 * @var bool $is_completed
 * @var array $completed_data
 */
?>

<script id="game-config" type="application/json">
    {
        "slug": <?= json_encode($franchise->getSlug()) ?>,
        "characters": <?= json_encode($characters) ?>,
        "guessedIds": <?= json_encode($guessed_chars_ids) ?>,
        "previousGuesses": <?= json_encode($previous_guesses) ?>,
        "isCompleted": <?= $is_completed ? 'true' : 'false' ?>,
        "completedData": <?= json_encode($completed_data) ?>
    }
</script>

<style>
    .main-wrapper {
        --franchise-backdrop: url('<?= BASE_URL ?>/assets/img/backgrounds/<?= $franchise->getBgImageUrl() ?>');
    }
</style>

<div class="game-container">

    <header class="game-header">
        <h1 class="section-title"><?= htmlspecialchars($franchise->getName()) ?></h1>
        <p class="game-subtitle">Guess today's character.</p>
    </header>

    <section class="search-section" aria-label="Character Selection">
        <div class="search-wrapper">
            <input 
                type="text" 
                id="character-search" 
                class="search-input" 
                placeholder="Type a character name..." 
                autocomplete="off"
            >
            <button type="button" id="clear-character-search" class="search-input-clear" aria-label="Clear search input" aria-controls="character-search" hidden>
                <svg aria-hidden='true' focusable="false" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m8 8l4 4m0 0l4 4m-4-4l4-4m-4 4l-4 4"/></svg>
            </button>
            <div id="autocomplete-results" class="autocomplete-dropdown" role="listbox" aria-label="Suggestions"></div>
        </div>
    </section>

    <section class="guesses-section" aria-label="Your Guesses">
        <button type="button" id="open-result-modal" aria-label="Open results popup" aria-controls="result-modal" hidden>Show results</button>
        <div class="table-responsive-wrapper">
            <table class="guesses-table">
                <thead>
                    <tr>
                        <th scope="col">Image</th>
                        <th scope="col">Name</th>
                        <?php foreach($franchise->getAttributeDefinitions() as $attr): ?>
                            <th scope="col"><?= htmlspecialchars($attr->getLabel()) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody id="guesses-body">
                </tbody>
            </table>
        </div>
    </section>

    <div id="result-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" hidden>
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <button type="button" class="modal-close" aria-label="Close result popup">
                <svg aria-hidden='true' focusable="false" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m8 8l4 4m0 0l4 4m-4-4l4-4m-4 4l-4 4"/></svg>
            </button>

            <!-- Game recap -->
            <div class="modal-header">
                <img id="modal-character-img" alt="">
                <h2 id="modal-title"></h2>
                <p>Guessed in <span id="modal-attempts"></span> attempts</p>
            </div>

            <!-- Player section -->
            <div class="modal-body">
                <!-- Player stats if user has an account -->
                <div id="modal-stats" hidden>
                    <div>Games played <span id="stat-played"></span></div>
                    <div>Win rate <span id="stat-winrate"></span></div>
                    <div>Current streak <span id="stat-current-streak"></span></div>
                    <div>Max streak <span id="stat-max-streak"></span></div>
                </div>

                <!-- CTA to register if user is guest -->
                <div id="modal-guest" hidden>
                    <p>Create an account to save your progress</p>
                    <a href="<?= BASE_URL ?>/register">Register</a>
                    <a href="<?= BASE_URL ?>/login">Login</a>
                </div>
            </div>
        </div>
    </div>

</div>