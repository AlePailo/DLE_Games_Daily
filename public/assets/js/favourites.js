import { showAlert } from "./utils/alerts.js";


export function initFavourites() {
    const favouriteButtons = document.querySelectorAll('.btn-favourite')
    const appConfigElement = document.getElementById('app-config')

    if(!favouriteButtons.length || !appConfigElement) return

    const APP_CONFIG = JSON.parse(appConfigElement.textContent)
    favouriteButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault()
            e.stopPropagation()

            if(!APP_CONFIG.isLoggedIn) {
                showAlert('error', 'Login is required to perform this action.')
                return
            }

            const franchiseId = parseInt(button.dataset.id, 10)
            if(!franchiseId) {
                showAlert('error', "Can't identify franchise id.")
                return
            }

            await toggleFavourite(button, franchiseId, APP_CONFIG)
        })
    })
}

async function toggleFavourite(button, franchiseId, config) {
    try {
        const response = await fetch(`${config.baseUrl}/api/favourites/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            },
            body: JSON.stringify({franchise_id : franchiseId})
        })
        
        if(!response.ok) throw new Error('Server or network error.')

        const data = await response.json()
        if(!data.success) return

        const isFav = data.is_favourite
        const card = button.closest('.franchise-card')

        button.classList.toggle('is-favorite', isFav)
        button.setAttribute('aria-pressed', isFav ? 'true' : 'false')
        
        if(isFav) {
            showAlert('success', 'Franchise added to favourites!')
        } else {
            showAlert('info', 'Franchise removed from favourites')

            const isInsideFavouritesGrid = button.closest('#favourites-grid') !== null
            if(isInsideFavouritesGrid && card) {
                handleHomeRemoval(card)
            }
        }
    
    } catch(e) {
        console.error('An error occurred', e)
        showAlert('error', 'Something went wrong. Please try again later.')
    }
}

function handleHomeRemoval(card) {
    card.classList.add('is-leaving')

    setTimeout(() => {
        card.remove()

        const grid = document.querySelector('#favourites-grid')
        if (!grid) return

        const remainingCards = grid.querySelectorAll('.franchise-card')
    
        if (remainingCards.length === 0) {
        
            const emptyMessage = grid.dataset.emptyMessage || 'No franchises found'
        
        
            grid.innerHTML = `
                <div class="empty-state" role="status">
                    <p>${emptyMessage}</p>
                </div>
            `
        }
    }, 300)
}