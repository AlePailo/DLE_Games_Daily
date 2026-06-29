document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('sidebar-toggle')
    const appLayout = document.getElementById('app-layout')

    if(toggleBtn && appLayout) {
        toggleBtn.addEventListener('click', () => {
            appLayout.classList.toggle('sidebar-collapsed')

            if(appLayout.classList.contains('sidebar-collapsed')) {
                localStorage.setItem('sidebar-state', 'collapsed')
            } else {
                localStorage.setItem('sidebar-state', 'expanded')
            }
        })
    }

    // Overflow elements catcher
    document.querySelectorAll('*').forEach(el => {
        if (el.offsetWidth > document.documentElement.offsetWidth) {
            console.log('Culprit found:', el)
        }
    })
})