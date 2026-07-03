

import Alpine from 'alpinejs';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.min.css';

window.Alpine = Alpine;
window.TomSelect = TomSelect;

// Initialize Tom Select for all elements with .tom-select class
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tom-select').forEach((el) => {
        if (!el.tomselect) {
            new TomSelect(el, {
                create: false,
                placeholder: el.getAttribute('placeholder') || 'Select an option',
                searchField: ['text']
            });
        }
    });
});

Alpine.start();
