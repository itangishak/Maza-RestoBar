// translation.js

// Called automatically on DOM load
document.addEventListener('DOMContentLoaded', function() {
    // Decide default language (English) or use localStorage
    const systemLang = navigator.language || navigator.userLanguage;
    let defaultLang = 'en';
    if (systemLang.startsWith('fr')) {
      defaultLang = 'fr';
    }
    const currentLang = localStorage.getItem('userLang') || defaultLang;
    loadTranslations(currentLang);
  });
  
  /**
   * Fetch the JSON file for the chosen lang and run translatePage.
   * If it fails, fallback to 'en'.
   */
  function loadTranslations(lang) {
    fetch(`../languages/${lang}.json`)  // Adjust path if needed
      .then(response => {
        if (!response.ok) {
          throw new Error(`Cannot fetch translations: ${response.statusText}`);
        }
        return response.json();
      })
      .then(translations => {
        localStorage.setItem('userLang', lang);
        translatePage(translations);
      })
      .catch(error => {
        console.error('Error loading translations:', error);
        // Fallback to English if fails
        if (lang !== 'en') {
          fetch('../languages/en.json')
            .then(resp => resp.json())
            .then(fallbackTranslations => {
              localStorage.setItem('userLang', 'en');
              translatePage(fallbackTranslations);
            })
            .catch(err => console.error('Error with fallback:', err));
        }
      });
  }
  
  /**
   * Go through every element with [data-key] and replace text with the translation.
   */
  function translatePage(translations) {
    document.querySelectorAll('[data-key]').forEach(element => {
      const key = element.getAttribute('data-key');
      if (translations[key]) {
        if (element.tagName === 'INPUT' || element.tagName === 'BUTTON') {
          element.value = translations[key];
        } else {
          element.textContent = translations[key];
        }
      }
    });
  }
  
  /**
   * Switch language manually (called from a dropdown or link)
   */
  function switchLanguage(lang) {
    loadTranslations(lang);
  }
  