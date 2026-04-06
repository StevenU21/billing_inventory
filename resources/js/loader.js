/**
 * Loading Screen Logic
 * Handles the display of the loading screen during navigation and page loads.
 */
export function initLoader() {
    const loader = document.getElementById('app-loader');
    if (!loader) return;

    // Function to show the loader
    const showLoader = () => {
        loader.style.display = 'flex';
        // Force a reflow to ensure the transition works if we were hiding it
        void loader.offsetWidth;
        loader.classList.remove('opacity-0', 'pointer-events-none');
    };

    // Function to hide the loader
    const hideLoader = () => {
        loader.classList.add('opacity-0', 'pointer-events-none');
        setTimeout(() => {
            // Only set display none if it's still hidden (avoid race conditions)
            if (loader.classList.contains('opacity-0')) {
                loader.style.display = 'none';
            }
        }, 300);
    };

    // Hide loader on initial load
    window.addEventListener('load', hideLoader);

    // Handle bfcache (back/forward cache)
    // This ensures the page is usable when returning via back button
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            hideLoader();
        }
    });

    // Intercept link clicks
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');

        // Ignore if not a link
        if (!link) return;

        // Ignore if link has target="_blank"
        if (link.target === '_blank') return;

        // Ignore if modifier keys are pressed (Ctrl, Shift, Alt, Meta)
        if (e.ctrlKey || e.shiftKey || e.altKey || e.metaKey) return;

        // Ignore if it's a download link
        if (link.hasAttribute('download')) return;

        // Ignore if it's an anchor link to the same page
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

        // Ignore if explicitly marked to not show loader
        if (link.hasAttribute('data-no-loader')) return;

        // Check if it's an internal link
        const url = new URL(link.href);
        if (url.origin !== window.location.origin) return;

        // If we got here, it's a standard internal navigation
        showLoader();
    });

    // Intercept form submissions
    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;

        const form = e.target;
        const submitter = e.submitter;

        // Ignore if form has target="_blank"
        if (form.target === '_blank') return;

        // Ignore if explicitly marked to not show loader (on form or submitter button)
        if (form.hasAttribute('data-no-loader') || (submitter && submitter.hasAttribute('data-no-loader'))) return;

        // Check if the form is valid (if browser validation is enabled)
        if (!form.checkValidity()) return;

        showLoader();
    });

    // Failsafe: auto-hide after 10s in case something goes wrong
    setTimeout(hideLoader, 10000);
}
