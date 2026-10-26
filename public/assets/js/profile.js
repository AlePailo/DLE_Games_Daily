document.addEventListener('DOMContentLoaded', () => {
    const appConfig = (() => {
        const el = document.getElementById('app-config')
        return el ? JSON.parse(el.textContent) : {}
    })()

    // =========================================================================
    // EDIT MODE TOGGLE
    // =========================================================================

    const editBtn    = document.getElementById('profile-edit-btn')
    const cancelBtn  = document.getElementById('profile-cancel-btn')
    const profileView = document.getElementById('profile-view')
    const editForm   = document.getElementById('profile-edit-form')

    editBtn.addEventListener('click', () => {
        profileView.hidden = true
        editForm.hidden = false
        const usernameInput = document.getElementById('username-input')
        usernameInput.focus()
        const length = usernameInput.value.length
        usernameInput.setSelectionRange(length, length)
    })

    cancelBtn?.addEventListener('click', () => {
        profileView.hidden = false
        editForm.hidden = true
        resetUsernameStatus()
    })

    // =========================================================================
    // AVATAR SELECTION
    // =========================================================================

    const avatarOptions = document.querySelectorAll('.avatar-option input[type="radio"]')
    const avatarPreview = document.getElementById('profile-avatar-img')

    avatarOptions.forEach(radio => {
        radio.addEventListener('change', () => {
            const img = radio.closest('.avatar-option')?.querySelector('.avatar-option-img')
            if (img && avatarPreview) {
                avatarPreview.src = img.src
            }
            document.querySelectorAll('.avatar-option').forEach(opt => opt.classList.remove('selected'))
            radio.closest('.avatar-option')?.classList.add('selected')
        })
    })

    // =========================================================================
    // FRANCHISE STATS DROPDOWN
    // =========================================================================

    const franchiseSelect = document.getElementById('franchise-select')
    const panels = document.querySelectorAll('.franchise-stats-panel')

    franchiseSelect?.addEventListener('change', () => {
        const selectedId = franchiseSelect.value

        panels.forEach(panel => {
            panel.hidden = panel.id !== `stats-franchise-${selectedId}`
        })
    })
})