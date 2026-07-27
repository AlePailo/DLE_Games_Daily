class Game {
    constructor(config) {
        const appConfigElement = document.getElementById('app-config');
        this.appConfig = appConfigElement ? JSON.parse(appConfigElement.textContent) : {};

        this.characters = config.characters
        this.guessedIds = new Set(config.guessedIds)
        this.isCompleted = config.isCompleted || false

        //DOM elements
        this.input = document.getElementById('character-search')
        this.dropdown = document.getElementById('autocomplete-results')
        this.tableBody = document.getElementById('guesses-body')

        this.slug = config.slug
        this.currentFocus = -1
        this.initEvents()

        if (config.previousGuesses && config.previousGuesses.length > 0) {
            config.previousGuesses.forEach(guess => {
                this.appendGuessRow(guess, true)
            })
        }
    }

    initEvents() {
        this.input.addEventListener('keydown', (e) => this.handleKeyboardNavigation(e))

        this.input.addEventListener('input', (e) => this.handleInput(e.target.value))

        document.addEventListener('click', (e) => {
            if(!this.input.contains(e.target) && !this.dropdown.contains(e.target)) {
                this.hideDropdown()
            }
        })
    }

    handleKeyboardNavigation(e) {
        const items = this.dropdown.querySelectorAll('.autocomplete-item')
        if(items.length < 1) return

        if(e.key === 'ArrowDown') {
            //e.preventDefault()

            (this.currentFocus === items.length - 1) ? this.currentFocus = 0 : this.currentFocus++

            this.setActiveItem(items)
        } else if(e.key === 'ArrowUp') {
            //e.preventDefault()

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

    handleInput(value) {
        this.currentFocus = -1

        const query = value.toLowerCase().trim()
        if(query.length < 1) {
            this.hideDropdown()
            return
        }

        const availableCharacters = this.characters.filter(char => !this.guessedIds.has(char.id))

        const matches = availableCharacters.filter(char => {
            const nameParts = char.name.toLowerCase().split(' ')

            return nameParts.some(part => part.startsWith(query))
        }).sort((a, b) => {
            const nameA = a.name.toLowerCase()
            const nameB = b.name.toLowerCase()

            const aStartsWithFirst = nameA.startsWith(query);
            const bStartsWithFirst = nameB.startsWith(query);

            // Priorità 1: Chi inizia col NOME va prima di chi inizia col COGNOME
            if (aStartsWithFirst && !bStartsWithFirst) return -1;
            if (!aStartsWithFirst && bStartsWithFirst) return 1;

            // Priorità 2: A parità di tipo di match, ordine alfabetico classico
            return nameA.localeCompare(nameB);
        })

        this.renderDropdown(matches)
    }

    renderDropdown(list) {
        this.dropdown.innerHTML = ''
        if(list.length < 1) {
            this.hideDropdown()
            return
        }

        const baseUrl = this.appConfig?.baseUrl || '';

        list.forEach(char => {
            const item = document.createElement('div')
            item.className = 'autocomplete-item'

            const imgSrc = char.image_url 
                ? `${baseUrl}/assets/img/characters_icons/${this.slug}/${char.image_url}`
                : `${baseUrl}/assets/img/default-avatar.png`

            item.innerHTML = `
                <img src="${imgSrc}" alt="${char.name}" class="autocomplete-img">
                <span class="autocomplete-name">${char.name}</span>
            `

            item.addEventListener('click', () => this.selectCharacter(char))
            this.dropdown.appendChild(item)
        })

        this.dropdown.style.display = 'block'
    }

    hideDropdown() {
        this.dropdown.style.display = 'none'
        this.currentFocus = -1
    }

    selectCharacter(char) {
        this.input.value = ''
        this.hideDropdown()
        this.submitGuess(char.id)
    }

    async submitGuess(characterId) {
        this.input.disabled = true

        try {
            const response = await fetch(`${this.appConfig?.baseUrl}/api/play/${this.slug}/attempt`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.appConfig?.csrfToken
                },
                body: JSON.stringify({
                    character_id: characterId
                }),
                keepalive: true
            })

            if (!response.ok) {
                const errorHtml = await response.text();
                console.error("PHP Error Response:", errorHtml);
                return;
            }

            const data = await response.json()

            console.log(data)

            if(data.success) {
                this.guessedIds.add(characterId)
                this.input.value = ''

                this.appendGuessRow(data);

                if (data.solved) {
                    this.isCompleted = true;
                    alert('Character guessed!')
                }                         //TODO: Victory modal
            } else {
                alert(data.message || 'Something went wrong')           //TODO: integrate showAlerts() from module
            }
        } catch(e) {
            console.error('Error submitting guess', e)              //TODO: integrate showAlerts() from module
        } finally {
            if(!this.isCompleted) {
                this.input.disabled = false
                this.input.focus()
            }
        }
    }

    appendGuessRow(data, isInitialLoad = false) {
        const row = document.createElement('tr')
        row.className = 'guess-row'

        if(!isInitialLoad) {
            row.classList.add('animate-row-entry')
        }

        const attemptData = data.attempt || data

        const charName = attemptData.character.name
        const nameStatusClass = (attemptData.character.status || 'wrong').toLowerCase()
        const charImage = attemptData.character?.image_url
        const attributes = attemptData.attributes || {}

        let cellsHtml = `
            <td class="guess-cell cell-image">
                <img src="${this.appConfig?.baseUrl}/assets/img/characters_icons/${this.slug}/${charImage}" alt="${charName}" class="character-icon">
            </td>
            <td class="guess-cell ${nameStatusClass}">
                ${charName}
            </td>
        `

        // Generazione DINAMICA delle celle degli attributi
        for (const [key, attrData] of Object.entries(attributes)) {
            const statusClass = (attrData.status || '').toLowerCase()
            const val = attrData.value ?? ''

            cellsHtml += `<td class="guess-cell ${statusClass}">${val}</td>`
        }

        row.innerHTML = cellsHtml

        // Inserisce il tentativo in cima alla tabella
        this.tableBody.prepend(row)
    }

    setActiveItem(items) {
        items.forEach(item => item.classList.remove('active'))

        const activeItem = items[this.currentFocus]
        if(activeItem) {
            activeItem.classList.add('active')
            activeItem.scrollIntoView({block: 'nearest'})
        }
    }
}


document.addEventListener('DOMContentLoaded', () => {
    const configElement = document.getElementById('game-config');

    if (configElement) {
        try {
            // 1. Parsing del JSON contenuto nel tag <script>
            const rawConfig = JSON.parse(configElement.textContent);

            // 2. Rimuoviamo immediatamente il tag <script> dal DOM per non lasciare tracce
            configElement.remove();

            // 3. (Opzionale) Congeliamo l'oggetto config per sicurezza extra
            const config = Object.freeze(rawConfig);

            // 4. Istanziamo la classe Game
            window.gameInstance = new Game(config);

        } catch (e) {
            console.error('Errore nel parsing della configurazione del gioco:', e);
        }
    }
})