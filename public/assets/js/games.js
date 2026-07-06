document.addEventListener('DOMContentLoaded', () => {
    const pageContainer = document.querySelector('.games-page-container')
    if(!pageContainer) return

    const isGuest = pageContainer.dataset.isGuest === 'true'
    const favouriteButtons = document.querySelectorAll('.btn-favourite')

    const APP_CONFIG = JSON.parse(document.getElementById('app-config').textContent)

    favouriteButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault()

            if(isGuest) {
                alert('Login to save your favourites')
            }

            const franchiseId = parseInt(button.dataset.id, 10)

            try {
                const response = await fetch(`${APP_CONFIG.baseUrl}/api/favourites/toggle`, {
                    method: 'POST',
                    headers: {'Content-Type' : 'application/json'},
                    body: JSON.stringify({franchise_id: franchiseId})
                })

                if(!response.ok) throw new Error('Server or network error')

                const data = await response.json()

                if(data.success) {
                    const isFav = data.is_favourite

                    button.classList.toggle('is-favorite')
                    button.innerHTML = `<span aria-hidden="true">${isFav ? '★' : '☆'}</span>`;
                    button.setAttribute('aria-pressed', isFav ? 'true' : 'false')
                }
            } catch(e) {
                console.log('An error occurred', e)
            }
        })
    })
})