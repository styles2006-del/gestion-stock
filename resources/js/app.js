/**
 * Gestion de Stock — interactions front-end
 */

// ---------- Thème clair / sombre ----------
const THEME_KEY = 'stock-theme';

function applyTheme(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
}

function currentTheme() {
    const stored = localStorage.getItem(THEME_KEY);
    if (stored) {
        return stored;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

applyTheme(currentTheme());

// ---------- Sidebar mobile ----------
function toggleSidebar(open) {
    const sidebar = document.getElementById('app-sidebar');
    const overlay = document.getElementById('app-overlay');
    if (!sidebar || !overlay) {
        return;
    }
    const shouldOpen = open ?? !sidebar.classList.contains('translate-x-0');
    sidebar.classList.toggle('-translate-x-full', !shouldOpen);
    sidebar.classList.toggle('translate-x-0', shouldOpen);
    overlay.classList.toggle('hidden', !shouldOpen);
}

document.addEventListener('DOMContentLoaded', () => {
    // Thème
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            localStorage.setItem(THEME_KEY, next);
            applyTheme(next);
            document.dispatchEvent(new CustomEvent('theme-changed', { detail: next }));
        });
    });

    // Sidebar
    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', () => toggleSidebar());
    });
    document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
        button.addEventListener('click', () => toggleSidebar(false));
    });

    // Confirmation de suppression
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // Fermeture automatique des alertes
    document.querySelectorAll('[data-auto-dismiss]').forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = 'opacity .4s, transform .4s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-6px)';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });
});

// ---------- Lignes de documents dynamiques ----------
function initDocumentLines() {
    const container = document.getElementById('lines-container');
    const template = document.getElementById('line-template');
    const addButton = document.getElementById('add-line');
    if (!container || !template) {
        return;
    }

    const recalc = () => {
        let subtotal = 0;
        container.querySelectorAll('[data-line]').forEach((row) => {
            const qty = parseFloat(row.querySelector('[data-qty]')?.value || 0);
            const price = parseFloat(row.querySelector('[data-price]')?.value || 0);
            const total = qty * price;
            subtotal += total;
            const cell = row.querySelector('[data-line-total]');
            if (cell) {
                cell.textContent = formatMoney(total);
            }
        });

        const taxRate = parseFloat(document.getElementById('tax_rate')?.value || 0);
        const tax = subtotal * (taxRate / 100);
        setText('summary-subtotal', formatMoney(subtotal));
        setText('summary-tax', formatMoney(tax));
        setText('summary-total', formatMoney(subtotal + tax));
    };

    const reindex = () => {
        container.querySelectorAll('[data-line]').forEach((row, index) => {
            row.querySelectorAll('[data-name]').forEach((input) => {
                input.name = input.dataset.name.replace('__INDEX__', String(index));
            });
        });
    };

    addButton?.addEventListener('click', () => {
        const html = template.innerHTML.replaceAll('__INDEX__', String(container.children.length));
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const row = wrapper.firstElementChild;
        container.appendChild(row);
        reindex();
        recalc();
    });

    container.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-remove-line]');
        if (remove) {
            if (container.querySelectorAll('[data-line]').length > 1) {
                remove.closest('[data-line]').remove();
                reindex();
                recalc();
            }
        }
    });

    container.addEventListener('input', (event) => {
        if (event.target.matches('[data-qty], [data-price]')) {
            recalc();
        }
    });

    document.getElementById('tax_rate')?.addEventListener('input', recalc);

    // Pré-remplissage du prix lors du choix d'un article
    container.addEventListener('change', (event) => {
        const select = event.target.closest('[data-product-select]');
        if (!select) {
            return;
        }
        const option = select.selectedOptions[0];
        const row = select.closest('[data-line]');
        if (option?.dataset.price && row) {
            const priceInput = row.querySelector('[data-price]');
            if (priceInput && (!priceInput.value || priceInput.dataset.auto !== 'false')) {
                priceInput.value = option.dataset.price;
                recalc();
            }
        }
    });

    recalc();
}

function formatMoney(value) {
    return new Intl.NumberFormat(document.documentElement.lang === 'en' ? 'en-GB' : 'fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0);
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = value;
    }
}

document.addEventListener('DOMContentLoaded', initDocumentLines);
