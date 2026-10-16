

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
            searchField: ['text'],
            dropdownParent: 'body',
            maxOptions: 500,
            onDropdownOpen: function() {
                this.positionDropdown();
            },
        });

        ts.on('change', (value) => {
            el.value = value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });

        el.addEventListener('change', () => {
            if (ts.getValue() !== el.value) {
                ts.setValue(el.value, true);
            }
        });
    } catch (e) {
        console.error('TomSelect init error:', e);
    }
}

// Override TomSelect's positionDropdown for smart viewport-aware positioning (auto flip up/down & fixed viewport alignment)
TomSelect.prototype.positionDropdown = function() {
    if (!this.isOpen) return;

    const control = this.control;
    const dropdown = this.dropdown;
    const content = dropdown.querySelector('.ts-dropdown-content') || dropdown;
    const rect = control.getBoundingClientRect();

    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;
    const spaceBelow = viewportHeight - rect.bottom;
    const spaceAbove = rect.top;

    // Minimum space needed below to open downwards comfortably
    const minHeightNeeded = 220;
    const openUpwards = (spaceBelow < minHeightNeeded && spaceAbove > spaceBelow);

    // Apply fixed positioning relative to viewport so parent overflows never clip
    dropdown.style.position = 'fixed';
    dropdown.style.zIndex = '999999';

    // Width: match control or minimum 320px for clear readability of optgroups & material names
    const desiredWidth = Math.max(rect.width, 320);
    const maxAvailableWidth = viewportWidth - 24;
    const finalWidth = Math.min(desiredWidth, maxAvailableWidth);

    dropdown.style.width = `${finalWidth}px`;

    // Horizontal alignment with screen edge safety
    let left = rect.left;
    if (left + finalWidth > viewportWidth - 12) {
        left = Math.max(8, viewportWidth - finalWidth - 12);
    }
    dropdown.style.left = `${left}px`;

    if (openUpwards) {
        // Open upwards
        const availableHeight = Math.min(320, Math.max(140, spaceAbove - 20));
        dropdown.style.top = 'auto';
        dropdown.style.bottom = `${viewportHeight - rect.top + 4}px`;
        content.style.maxHeight = `${availableHeight}px`;
        dropdown.classList.add('ts-dropdown-up');
        dropdown.classList.remove('ts-dropdown-down');
    } else {
        // Open downwards
        const availableHeight = Math.min(320, Math.max(140, spaceBelow - 20));
        dropdown.style.bottom = 'auto';
        dropdown.style.top = `${rect.bottom + 4}px`;
        content.style.maxHeight = `${availableHeight}px`;
        dropdown.classList.add('ts-dropdown-down');
        dropdown.classList.remove('ts-dropdown-up');
    }
};

// Automatically update open dropdown position when scrolling (including inner scroll containers like <main>)
const updateOpenDropdowns = () => {
    document.querySelectorAll('select.tom-select').forEach((el) => {
        if (el.tomselect && el.tomselect.isOpen) {
            const rect = el.tomselect.control.getBoundingClientRect();
            // If control scrolled out of viewport, close it
            if (rect.bottom < 40 || rect.top > window.innerHeight - 40) {
                el.tomselect.close();
            } else {
                el.tomselect.positionDropdown();
            }
        }
    });
};

window.addEventListener('scroll', updateOpenDropdowns, { capture: true, passive: true });
window.addEventListener('resize', updateOpenDropdowns, { passive: true });

// Initialize on page load and watch for dynamic additions (e.g. Alpine.js template rows)
document.addEventListener('DOMContentLoaded', () => {
    // Init existing
    document.querySelectorAll('.tom-select').forEach(initTomSelect);

    // Watch for new elements inserted into or removed from the DOM
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            // Handle additions
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

            // Handle removals (clean up destroyed TomSelect instances & body dropdowns)
            mutation.removedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    node.querySelectorAll?.('select.tom-select').forEach((sel) => {
                        if (sel.tomselect) sel.tomselect.destroy();
                    });
                    if (node.tagName === 'SELECT' && node.tomselect) {
                        node.tomselect.destroy();
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

