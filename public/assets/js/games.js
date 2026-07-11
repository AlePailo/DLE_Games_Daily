import { initFavourites } from './favourites.js'


function initGameSearch() {
    const searchInput = document.querySelector('#search-input')
    const cards = document.querySelectorAll('.franchise-card')
    const statusLive = document.querySelector('#search-result-status')

    if(!searchInput) return

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


document.addEventListener('DOMContentLoaded', () => {
    initGameSearch()
    initFavourites()
})