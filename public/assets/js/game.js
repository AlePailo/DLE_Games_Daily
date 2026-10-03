import { showAlert } from "./utils/alerts.js";

class Game {
    constructor(config) {
        this.initState(config)
        this.initDOMElements()
        this.initEvents()
        this.restoreGameState(config)
    }
        
    initState(config) {
        this.appConfig = this._parseAppConfig()

        this.slug = config.slug
        this.characters = config.characters
        this.guessedIds = new Set(config.guessedIds)
        this.isCompleted = config.isCompleted || false
        this.completedData = config.completedData || null

        this.isDropdownOpen = false
        this.isSubmitting = false
        this.currentFocus = -1
        this.previouslyFocused = null
        this.blurTimeout = null
    }

    initDOMElements() {
        // Search
        this.input = document.getElementById('character-search')
        this.clearBtn = document.getElementById('clear-character-search')
        this.dropdown = document.getElementById('autocomplete-results')
        
        // Game actions
        this.tableBody = document.getElementById('guesses-body')
        this.surrenderBtn = document.getElementById('surrender-btn')

        // Game recap modal
        this.modal = document.getElementById('result-modal')
        this.modalTitle = document.getElementById('modal-title')
        this.modalImg = document.getElementById('modal-character-img')
        this.modalAttempts = document.getElementById('modal-attempts')
        this.modalStats = document.getElementById('modal-stats')
        this.modalGuest = document.getElementById('modal-guest')
        this.modalOverlay = document.querySelector('.modal-overlay')
        this.modalCloseBtn = document.querySelector('.modal-close')
        this.modalOpenBtn = document.getElementById('open-result-modal')

        // Game recap modal stats
        this.statPlayed = document.getElementById('stat-played')
        this.statWinrate = document.getElementById('stat-winrate')
        this.statCurrentStreak = document.getElementById('stat-current-streak')
        this.statMaxStreak = document.getElementById('stat-max-streak')
    }

    initEvents() {
        // Search input
        this.input.addEventListener('keydown', (e) => this.handleKeyboardNavigation(e))
        this.input.addEventListener('input', (e) => this.handleInput(e.target.value))
        this.input.addEventListener('blur', () => {
            this.blurTimeout = setTimeout(() => this.hideDropdown(), 150)
        })
        this.input.addEventListener('focus', () => clearTimeout(this.blurTimeout))

        // Clear button
        this.clearBtn.addEventListener('mousedown', (e) => {
            e.preventDefault()
            this.clearInput()
        })

        // Surrender button
        this.surrenderBtn.addEventListener('click', () => this.surrender())

        // Modal
        this.modal.addEventListener('keydown', (e) => {
            if(e.key === 'Escape') this.closeModal()
        })
        this.modalOverlay.addEventListener('click', () => this.closeModal())
        this.modalOpenBtn.addEventListener('click', () => this.openModal(this.completedData))
        this.modalCloseBtn.addEventListener('click', () => this.closeModal())
    }

    restoreGameState(config) {
        // Repopulate table with existing guesses
        if (config.previousGuesses?.length > 0) {
            config.previousGuesses.forEach(guess => this.appendGuessRow(guess, true))
        }

        // Restore completed game state (UI)
        if(this.isCompleted) {
            this.input.disabled = true
            if(config.completedData) {
                this.openModal(config.completedData)
            }
        }

        this.enableSurrenderCheck()
    }


    // =========================================================================
    // SEARCH & AUTOCOMPLETE
    // =========================================================================

    handleInput(value) {
        this.currentFocus = -1

        const query = value.toLowerCase().trim()
        if(query.length < 1) {
            this.clearBtn.hidden = true
            this.hideDropdown()
            return
        }

        this.clearBtn.hidden = false

        const availableCharacters = this.characters.filter(char => !this.guessedIds.has(char.id))
        const matches = this._filterAndSortMatches(availableCharacters, query)

        this.renderDropdown(matches)
    }

    handleKeyboardNavigation(e) {
        if(!this.isDropdownOpen) return

        if(this.isSubmitting || this.isCompleted) {
            if(e.key === 'Enter') e.preventDefault()
            return
        }

        const items = this.dropdown.querySelectorAll('.autocomplete-item')
        if(items.length < 1) return

        if(e.key === 'ArrowDown') {
            e.preventDefault();
            (this.currentFocus === items.length - 1) ? this.currentFocus = 0 : this.currentFocus++
            this.setActiveItem(items)
        } else if(e.key === 'ArrowUp') {
            e.preventDefault();
            (this.currentFocus === 0) ? this.currentFocus = items.length - 1 : this.currentFocus--
            this.setActiveItem(items)
        } else if(e.key === 'Enter') {
            e.preventDefault()
            const itemToSelect = (this.currentFocus > -1 && items[this.currentFocus]) 
                ? items[this.currentFocus]
                : items[0]
            if(itemToSelect) itemToSelect.click()
        }
    }

    renderDropdown(list) {
        this.dropdown.innerHTML = ''
        if(list.length < 1) {
            this.hideDropdown()
            return
        }

        const baseUrl = this.appConfig?.baseUrl || ''
        const fragment = document.createDocumentFragment()

        list.forEach(char => {
            const item = document.createElement('div')
            item.className = 'autocomplete-item'
            item.setAttribute('role', 'option')

            const imgSrc = char.image_url 
                ? `${baseUrl}/assets/img/characters_icons/${this.slug}/${char.image_url}`
                : `${baseUrl}/assets/img/default-avatar.png`

            item.innerHTML = `
                <img src="${imgSrc}" alt="${char.name}" class="autocomplete-img">
                <span class="autocomplete-name">${char.name}</span>
            `

            item.addEventListener('click', () => this.selectCharacter(char))
            fragment.appendChild(item)
        })
        
        this.dropdown.appendChild(fragment)
        this.isDropdownOpen = true
        this.dropdown.classList.add('is-open')
        this.input.setAttribute('aria-expanded', 'true')
    }

    hideDropdown() {
        this.isDropdownOpen = false
        this.dropdown.classList.remove('is-open')
        this.dropdown.innerHTML = ''
        this.input.setAttribute('aria-expanded', 'false')
        this.currentFocus = -1
    }

    setActiveItems(items) {
        items.forEach(item => item.classList.remove('active'))

        const activeItem = items[this.currentFocus]
        if(!activeItem) return

        activeItem.classList.add('active')

        const dropdownTop = this.dropdown.scrollTop
        const dropdownBottom = dropdownTop + this.dropdown.clientHeight
        const itemTop = activeItem.offsetTop
        const itemBottom = itemTop + activeItem.offsetHeight

        if (itemBottom > dropdownBottom) {
            this.dropdown.scrollTop = itemBottom - this.dropdown.clientHeight
        } else if (itemTop < dropdownTop) {
            this.dropdown.scrollTop = itemTop
        }
    }

    clearInput() {
        this.input.value = ''
        this.clearBtn.hidden = true
        this.hideDropdown()
        this.input.focus()
    }


    // =========================================================================
    // GAME ACTIONS
    // =========================================================================

    selectCharacter(char) {
        if (this.isSubmitting) return

        this.clearInput()
        this.submitGuess(char.id)
    }

    async submitGuess(characterId) {
        if(this.isSubmitting) return

        this.isSubmitting = true
        this.input.disabled = true

        try {
            const data = await this._apiPost(`/api/play/${this.slug}/attempt`, { character_id: characterId })

            if(!data) return

            if(data.success) {
                this.guessedIds.add(characterId)
                //this.input.value = ''
                this.clearBtn.hidden = true
                this.enableSurrenderCheck()
                this.appendGuessRow(data)

                if (data.solved) {
                    this.isCompleted = true
                    this.completedData = data.completed_data
                    this.surrenderBtn.hidden = true
                    this.openModal(this.completedData)
                }
            } else {
                showAlert('error', data.message || 'Something went wrong')    
            }
        } catch(e) {
            showAlert('error', 'Error submitting guess')
        } finally {
            this.isSubmitting = false
            if(!this.isCompleted) {
                this.input.disabled = false
                this.input.focus()
            }
        }
    }

    async surrender() {
        if(this.isSubmitting) return

        this.isSubmitting = true
        this.input.disabled = true

        try {
            const data = await this._apiPost(`/api/play/${this.slug}/surrender`)
            if(!data) return

            if (data.success) {
                this.isCompleted   = true
                this.completedData = data.completed_data
                this.surrenderBtn.hidden = true
                this.openModal(this.completedData)
            }
        } catch(e) {
            showAlert('error', 'Error submitting surrender')
        } finally {
            this.isSubmitting = false
            if(!this.isCompleted) {
                this.input.disabled = false
                this.input.focus()
            } 
        }
    }

    enableSurrenderCheck() {
        if(this.guessedIds.size >= 3 && !this.isCompleted) {
            this.surrenderBtn.hidden = false
        }
    }

    appendGuessRow(data, isInitialLoad = false) {
        const attemptData = data.attempt || data
        const charName = attemptData.character.name
        const nameStatusClass = (attemptData.character.status || 'wrong').toLowerCase()
        const charImage = attemptData.character?.image_url
        const attributes = attemptData.attributes || {}

        const row = document.createElement('tr')
        row.className = 'guess-row'
        if(!isInitialLoad) row.classList.add('animate-row-entry')

        let cellsHtml = `
            <td class="guess-cell cell-image">
                <img src="${this.appConfig?.baseUrl}/assets/img/characters_icons/${this.slug}/${charImage}" alt="${charName}" class="character-icon">
            </td>
            <td class="guess-cell ${nameStatusClass}">
                ${charName}
            </td>
        `

        // Dynamic attributes cells generation
        for (const [, attrData] of Object.entries(attributes)) {
            const statusClass = (attrData.status || '').toLowerCase()
            cellsHtml += `<td class="guess-cell ${statusClass}">${attrData.value ?? ''}</td>`
        }

        row.innerHTML = cellsHtml

        // Last attempt pushed to top of the table
        this.tableBody.prepend(row)
    }


    // =========================================================================
    // MODAL
    // =========================================================================

    openModal(completedData) {
        const baseUrl = this.appConfig?.baseUrl || ''
        const char = completedData.correct_char

        this.modalImg.src = char.image_url
            ? `${baseUrl}/assets/img/characters_icons/${this.slug}/${char.image_url}`
            : `${baseUrl}/assets/img/default-avatar.png`
        this.modalImg.alt = char.name
        this.modalTitle.textContent = char.name
        this.modalAttempts.textContent = completedData.attempts_count === undefined
            ? 'You gave up'
            : `Guessed in ${completedData.attempts_count} attempts`

        if(completedData.stats) {
            this.statPlayed.textContent = completedData.stats.games_played
            this.statWinrate.textContent = `${completedData.stats.win_rate}%`
            this.statCurrentStreak.textContent = completedData.stats.current_streak
            this.statMaxStreak.textContent = completedData.stats.max_streak
            this.modalStats.hidden = false
            this.modalGuest.hidden = true
        } else {
            this.modalStats.hidden = true
            this.modalGuest.hidden = false
        }

        this.modal.classList.add('is-open')
        this.previouslyFocused = document.activeElement
        this.modal.focus()
        this.modal.addEventListener('keydown', this.trapFocus)
    }

    closeModal() {
        this.modal.classList.remove('is-open')
        this.modal.removeEventListener('keydown', this.trapFocus)
        this.previouslyFocused?.focus()
        this.modalOpenBtn.hidden = false
    }

    trapFocus = (e) => {
        if(e.key !== 'Tab') return

        const focusable = this.modal.querySelectorAll('button, a, input, [tabindex]:not([tabindex="-1"])')
        const first = focusable[0]
        const last = focusable[focusable.length - 1]

        if(e.shiftKey && document.activeElement === first) {
            e.preventDefault()
            last.focus()
        } else if(!e.shiftKey && document.activeElement === last) {
            e.preventDefault()
            first.focus()
        }
    }


    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    _parseAppConfig() {
        const el = document.getElementById('app-config')
        return el ? JSON.parse(el.textContent) : {}
    }

    _filterAndSortMatches(characters, query) {
        return characters
            .filter(char => {
                const nameParts = char.name.toLowerCase().split(' ')
                return nameParts.some(part => part.startsWith(query))
            })
            .sort((a, b) => {
                const nameA = a.name.toLowerCase()
                const nameB = b.name.toLowerCase()
                const aFirst = nameA.startsWith(query);
                const bFirst = nameB.startsWith(query);

                // 1st priority: First name match first, then last name match
                if (aFirst && !bFirst) return -1;
                if (!aFirst && bFirst) return 1;

                // 2nd priority: When there are more matches on same criteria, sort by alphabetical order
                return nameA.localeCompare(nameB);
            })
    }

    async _apiPost(endpoint, body = null) {
        const response = await fetch(`${this.appConfig?.baseUrl}/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.appConfig?.csrfToken
            },
            ...(body && { body: JSON.stringify(body) }),
            keepalive: true
        })

        if (!response.ok) {
            console.error('API error:', await response.text())
            return null
        }
 
        return response.json()
    }
}
 
 
document.addEventListener('DOMContentLoaded', () => {
    const configElement = document.getElementById('game-config')
    if (!configElement) return
 
    try {
        const config = JSON.parse(configElement.textContent)
        configElement.remove()
        new Game(config)
    } catch (e) {
        console.error('Error parsing game config:', e)
    }
})