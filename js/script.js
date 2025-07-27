// Same JavaScript as previous implementation
const translations = {
    en: {
        // English translations
    },
    bn: {
        // Bangla translations
    }
};

let currentLang = 'en';

function switchLanguage(lang) {
    currentLang = lang;
    document.querySelectorAll('[data-i18n]').forEach(element => {
        const key = element.getAttribute('data-i18n');
        if (translations[lang][key]) {
            element.textContent = translations[lang][key];
        }
    });
    
    // Update UI elements
    if (lang === 'en') {
        document.getElementById('en-btn').classList.add('active', 'btn-success');
        document.getElementById('en-btn').classList.remove('btn-light');
        document.getElementById('bn-btn').classList.add('btn-light');
        document.getElementById('bn-btn').classList.remove('active', 'btn-success');
        document.documentElement.lang = 'en';
    } else {
        document.getElementById('bn-btn').classList.add('active', 'btn-success');
        document.getElementById('bn-btn').classList.remove('btn-light');
        document.getElementById('en-btn').classList.add('btn-light');
        document.getElementById('en-btn').classList.remove('active', 'btn-success');
        document.documentElement.lang = 'bn';
    }
}

// Event listeners
document.getElementById('en-btn').addEventListener('click', () => switchLanguage('en'));
document.getElementById('bn-btn').addEventListener('click', () => switchLanguage('bn'));

// Initialize
document.addEventListener('DOMContentLoaded', () => switchLanguage('en'));