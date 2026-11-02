/* Public theme preference belongs to the existing homepage controls. */
(() => {
    const root = document.documentElement;
    const key = 'hag-theme';
    let media;
    try { media = window.matchMedia('(prefers-color-scheme: light)'); } catch {}
    function preference() {
        try { const value = window.localStorage.getItem(key); return value === 'light' || value === 'dark' ? value : null; }
        catch { return null; }
    }
    function syncClasses(light) {
        if (root.classList.contains('theme-light') !== light) root.classList.toggle('theme-light', light);
        if (root.classList.contains('dark') === light) root.classList.toggle('dark', !light);
        root.style.colorScheme = light ? 'light' : 'dark';
    }
    function announce() { window.dispatchEvent(new window.Event('hag-public-theme-change')); }
    function refresh() {
        const saved = preference();
        syncClasses(saved === 'light' || (saved === null && Boolean(media?.matches)));
        announce();
    }
    refresh();
    window.addEventListener('storage', event => { if (event.key === key || event.key === null) refresh(); });
    window.addEventListener('pageshow', refresh);
    media?.addEventListener?.('change', () => { if (preference() === null) refresh(); });
    // The homepage already changes theme-light and saves hag-theme. Adapt its
    // existing control to the Tailwind dark class without another writer/button.
    if (typeof window.MutationObserver === 'function') {
        new window.MutationObserver(() => {
            syncClasses(root.classList.contains('theme-light'));
        }).observe(root, {attributes: true, attributeFilter: ['class']});
    }
})();
