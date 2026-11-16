(() => {
    const root = document.documentElement;
    const isArabic = root.lang === 'ar';

    const themeButtons = [
        document.getElementById('theme-toggle'),
        document.getElementById('theme-toggle-mobile'),
    ].filter(Boolean);

    const languageMenu = document.getElementById('language-menu');
    const languageButton = document.getElementById('language-menu-button');
    const languageDropdown = document.getElementById('language-dropdown');

    const mobileButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuOpenIcon = document.getElementById('menu-open-icon');
    const menuCloseIcon = document.getElementById('menu-close-icon');

    function updateThemeControls() {
        const light = root.classList.contains('theme-light');

        document.querySelectorAll('.hag-icon-sun').forEach((icon) => {
            icon.classList.toggle('hidden', light);
        });

        document.querySelectorAll('.hag-icon-moon').forEach((icon) => {
            icon.classList.toggle('hidden', !light);
        });

        document.querySelectorAll('.hag-theme-label').forEach((label) => {
            label.textContent = light
                ? (isArabic ? 'الوضع الداكن' : 'Dark mode')
                : (isArabic ? 'الوضع الفاتح' : 'Light mode');
        });

        themeButtons.forEach((button) => {
            button.setAttribute('aria-pressed', String(light));
            button.setAttribute(
                'aria-label',
                light
                    ? (isArabic ? 'تفعيل الوضع الداكن' : 'Switch to dark mode')
                    : (isArabic ? 'تفعيل الوضع الفاتح' : 'Switch to light mode')
            );
        });
    }

    themeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const light = !root.classList.contains('theme-light');
            root.classList.toggle('theme-light', light);

            try {
                localStorage.setItem('hag-theme', light ? 'light' : 'dark');
            } catch {}

            updateThemeControls();
        });
    });

    window.addEventListener('hag-public-theme-change', updateThemeControls);
    updateThemeControls();

    function setLanguageMenu(open) {
        if (!languageButton || !languageDropdown) return;

        languageDropdown.classList.toggle('hidden', !open);
        languageButton.setAttribute('aria-expanded', String(open));
    }

    languageButton?.addEventListener('click', () => {
        setLanguageMenu(languageDropdown?.classList.contains('hidden') ?? false);
    });

    document.addEventListener('click', (event) => {
        if (languageMenu && !languageMenu.contains(event.target)) {
            setLanguageMenu(false);
        }
    });

    languageMenu?.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setLanguageMenu(false);
            languageButton?.focus();
        }
    });

    function setMobileMenu(open) {
        if (!mobileButton || !mobileMenu) return;

        mobileMenu.classList.toggle('hidden', !open);
        mobileButton.setAttribute('aria-expanded', String(open));
        menuOpenIcon?.classList.toggle('hidden', open);
        menuCloseIcon?.classList.toggle('hidden', !open);
    }

    mobileButton?.addEventListener('click', () => {
        setMobileMenu(mobileMenu.classList.contains('hidden'));
    });

    mobileMenu?.querySelectorAll('.mobile-link').forEach((link) => {
        link.addEventListener('click', () => setMobileMenu(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setMobileMenu(false);
    });

    const navbar = document.getElementById('navbar');

    if (navbar) {
        const updateNavbar = () => {
            navbar.classList.toggle('shadow-2xl', window.scrollY > 20);
        };

        window.addEventListener('scroll', updateNavbar, { passive: true });
        updateNavbar();
    }
})();
