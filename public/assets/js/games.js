import { showAlert } from './utils/alerts.js'
    
const searchInput = document.querySelector('#search-input')
const cards = document.querySelectorAll('.franchise-card')
const statusLive = document.querySelector('#search-result-status')

if(searchInput) {
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim()
        let visibleCount = 0

        cards.forEach(card => {
            const name = card.dataset.name
            if(name.includes(query)) {
                card.style.display = 'flex';
                visibleCount++
            } else {
                card.style.display = 'none'
            }
        })

        if(statusLive) {
            statusLive.textContent = `Found ${visibleCount} franchises`
        }
    })
}



const pageContainer = document.querySelector('.games-page-container')
if(pageContainer) {
    const favouriteButtons = document.querySelectorAll('.btn-favourite')

    const APP_CONFIG = JSON.parse(document.getElementById('app-config').textContent)

    favouriteButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault()
            e.stopPropagation()

            if(APP_CONFIG.isGuest) {
                showAlert('error', 'Login is needed to perform this action')
                return
            }

            const franchiseId = parseInt(button.dataset.id, 10)
            if (!franchiseId) {
                showAlert('error', "Can't identify franchise id.")
                return;
            }

            try {
                const response = await fetch(`${APP_CONFIG.baseUrl}/api/favourites/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type' : 'application/json',
                        'X-CSRF-TOKEN': APP_CONFIG.csrfToken
                    },
                    body: JSON.stringify({franchise_id: franchiseId})
                })

                if(!response.ok) throw new Error('Server or network error')

                const data = await response.json()

                if(data.success) {
                    const isFav = data.is_favourite

                    if(isFav) {
                        button.classList.add('is-favorite')
                        showAlert('success', 'Franchise added to favourites!')
                    } else {
                        button.classList.remove('is-favorite')
                        showAlert('info', 'Franchise removed from favourites.')
                    }
                    button.setAttribute('aria-pressed', isFav ? 'true' : 'false')
                }
            } catch(e) {
                console.log('An error occurred', e)
            }
        })
    })
}