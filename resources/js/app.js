

import Alpine from 'alpinejs';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.min.css';

window.Alpine = Alpine;
window.TomSelect = TomSelect;

// Function to safely initialize Tom Select
function initTomSelect(el) {
    if (!el || el.tomselect || el.dataset.tomselectInitialized) return;
    el.dataset.tomselectInitialized = 'true';

    try {
        const ts = new TomSelect(el, {
            create: false,
            placeholder: el.getAttribute('placeholder') || 'Select an option',
            searchField: ['text']
        });

        ts.on('change', (value) => {
            el.value = value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
    } catch (e) {
        console.error('TomSelect init error:', e);
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
                    if (node.classList.contains('ts-wrapper') || node.classList.contains('ts-control')) {
                        return;
                    }
                    if (node.classList.contains('tom-select')) {
                        initTomSelect(node);
                    } else {
                        node.querySelectorAll('.tom-select').forEach(initTomSelect);
                    }
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
