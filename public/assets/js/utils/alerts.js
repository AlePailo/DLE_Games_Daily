/**
 * Shows toast dynamically
 * Exported to be used in other modules
 * @param {string} type - The toast type: 'success', 'error', 'info'
 * @param {string|Array} message - The toast message (string o array of strings)
 */

export function showAlert(type, message) {
    const container = document.getElementById('toast-container')
    if(!container) return

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.setAttribute('role', 'alert');

    let messageContent = '';
    if (Array.isArray(message)) {
        messageContent = `<ul class="toast-list">${message.map(msg => `<li>${escapeHtml(msg)}</li>`).join('')}</ul>`;
    } else if (typeof message === 'string' && message.includes('<br>')) {
        const lines = message.split('<br>').filter(line => line.trim() !== '');
        messageContent = `<ul class="toast-list">${lines.map(line => `<li>${escapeHtml(line)}</li>`).join('')}</ul>`;
    } else {
        messageContent = `<p>${escapeHtml(message)}</p>`;
    }

    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-icon"></span>
            <div class="toast-message">${messageContent}</div>
        </div>
        <button type="button" class="toast-close">&times;</button>
        <div class="toast-progress"></div>
    `;

    container.appendChild(toast);
    initToastEvents(toast);
}

export function initToastEvents(toast) {
    const closeBtn = toast.querySelector('.toast-close')
    if (closeBtn) {
        closeBtn.onclick = () => dismissToast(toast);
    }

    setTimeout(() => dismissToast(toast), 5000)
}

export function dismissToast(toast) {
    if(!toast || toast.classList.contains('fade-out')) return

    toast.classList.add('fade-out')
    toast.addEventListener('animationend', () => toast.remove())
}

function escapeHtml(string) {
    return String(string).replace(/[&<>"']/g, function (s) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[s];
    });
}