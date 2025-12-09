    const switchButton = document.getElementById('switch');
    const saveButton = document.querySelector('.btn-primary');

    document.addEventListener('DOMContentLoaded', () => {
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode'); 
        }
    });

    switchButton.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const darkModeEnabled = document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', darkModeEnabled ? 'enabled' : 'disabled'); 
    });

    saveButton.addEventListener('click', () => {
        const darkModeEnabled = document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', darkModeEnabled ? 'enabled' : 'disabled'); 
    });