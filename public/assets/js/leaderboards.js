document.addEventListener('DOMContentLoaded', () => {
    const appConfig = (() => {
        const el = document.getElementById('app-config')
        return el ? JSON.parse(el.textContent) : {}
    })()

    const franchiseSelect = document.getElementById('franchise-select')
    const metricTabs      = document.querySelectorAll('.metric-tab')
    const leaderboardWrap = document.getElementById('leaderboard-wrap')

    let currentMetric    = document.querySelector('.metric-tab.active')?.dataset.metric ?? 'avg_attempts'
    let currentFranchise = franchiseSelect?.value ?? 'global'

    // =========================================================================
    // FETCH & RENDER
    // =========================================================================

    async function fetchAndRender() {
        leaderboardWrap.classList.add('leaderboard-loading')

        try {
            const params = new URLSearchParams({
                metric:    currentMetric,
                franchise: currentFranchise
            })

            const response = await fetch(
                `${appConfig.baseUrl}/api/leaderboards?${params}`,
                { headers: { 'Accept': 'application/json' } }
            )

            const data = await response.json()

            if (data.success) {
                renderTable(data.data)
            }
        } catch (e) {
            console.error('Leaderboard fetch failed:', e)
        } finally {
            leaderboardWrap.classList.remove('leaderboard-loading')
        }
    }

    function renderTable(entries) {
        if (!entries || entries.length === 0) {
            leaderboardWrap.innerHTML = `
                <p class="leaderboard-empty">No data available yet. Be the first to play!</p>
            `
            return
        }

        const metricLabels = {
            avg_attempts: 'Avg Attempts',
            max_streak:   'Best Streak',
            games_played: 'Games Played',
        }

        const medals = ['🥇', '🥈', '🥉']

        const rows = entries.map((entry, i) => {
            const rank = i < 3 ? medals[i] : i + 1
            const topClass = i < 3 ? `leaderboard-top-${i + 1}` : ''

            const value = currentMetric === 'avg_attempts'
                ? parseFloat(entry.avg_attempts ?? 0).toFixed(1)
                : parseInt(entry[currentMetric] ?? 0)

            const avatarUrl = entry.user_icon_url
                ? `${appConfig.baseUrl}/assets/img/avatars/${entry.user_icon_url}`
                : `${appConfig.baseUrl}/assets/img/avatars/default.png`

            return `
                <tr class="leaderboard-row ${topClass}">
                    <td class="leaderboard-rank">${rank}</td>
                    <td class="leaderboard-player">
                        <img src="${avatarUrl}" alt="${escapeHtml(entry.username)}" class="leaderboard-avatar">
                        <span>${escapeHtml(entry.username)}</span>
                    </td>
                    <td class="leaderboard-value">${value}</td>
                </tr>
            `
        }).join('')

        leaderboardWrap.innerHTML = `
            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Player</th>
                        <th scope="col">${metricLabels[currentMetric]}</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        `
    }

    // =========================================================================
    // EVENTS
    // =========================================================================

    metricTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            metricTabs.forEach(t => {
                t.classList.remove('active')
                t.setAttribute('aria-selected', 'false')
            })
            tab.classList.add('active')
            tab.setAttribute('aria-selected', 'true')

            currentMetric = tab.dataset.metric
            fetchAndRender()
        })
    })

    franchiseSelect?.addEventListener('change', () => {
        currentFranchise = franchiseSelect.value
        fetchAndRender()
    })

    // =========================================================================
    // HELPERS
    // =========================================================================

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
    }
})