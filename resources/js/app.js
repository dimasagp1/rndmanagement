

import Alpine from 'alpinejs';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.min.css';

window.Alpine = Alpine;
window.TomSelect = TomSelect;

// Function to safely initialize Tom Select
function initTomSelect(el) {
    if (!el.tomselect) {
        new TomSelect(el, {
            create: false,
            placeholder: el.getAttribute('placeholder') || 'Select an option',
            searchField: ['text']
        });
    }
}

// Initialize on page load and watch for dynamic additions (e.g. Alpine.js template rows)
document.addEventListener('DOMContentLoaded', () => {
    // Init existing
    document.querySelectorAll('.tom-select').forEach(initTomSelect);

    // Watch for new elements inserted into the DOM
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    if (node.classList.contains('tom-select')) {
                        initTomSelect(node);
                    }
                    node.querySelectorAll('.tom-select').forEach(initTomSelect);
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

Alpine.start();
