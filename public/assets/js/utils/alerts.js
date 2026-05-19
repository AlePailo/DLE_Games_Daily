document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toast').forEach(toast => initToastEvents(toast))
})

function showAlert(type, message) {
    const container = document.getElementById('toast-container')
    if(!container) return

    const toast = document.createElement('div')
    toast.className = `toast toast-${type}`
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-icon"></span>
            <p class="toast-message">${message}</p>
        </div>
        <button type="button" class="toast-close">&times;</button>
        <div class="toast-progress"></div>
    `
    console.log('sium')

    container.appendChild(toast)
    initToastEvents(toast)
}

function initToastEvents(toast) {
    const closeBtn = toast.querySelector('.toast-close')
    closeBtn.onclick = () => dismissToast(toast)

    /*const closeBtn = toast.querySelector('.toast-close');
    
    if (closeBtn) {
        closeBtn.onclick = () => dismissToast(toast);
    } else {
        console.warn("Attenzione: .toast-close non trovato nel toast!", toast);
    }
        */

    setTimeout(() => dismissToast(toast), 5000)
}

function dismissToast(toast) {
    if(!toast || toast.classList.contains('fade-out')) return

    toast.classList.add('fade-out')
    toast.addEventListener('animationend', () => toast.remove())
}