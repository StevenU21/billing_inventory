/**
 * Alpine.js Data Initialization
 * Main data object for Alpine.js components
 */
export function alpineData() {
  function getTheme() {
    if (window.localStorage.getItem('theme')) {
      return window.localStorage.getItem('theme')
    }
    return 'system'
  }

  function applyTheme(theme) {
    let isDark = false;

    if (theme === 'dark') {
      isDark = true;
    } else if (theme === 'light') {
      isDark = false;
    } else if (theme === 'system') {
      isDark = !!window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    if (isDark) {
      document.documentElement.classList.add('dark');
      document.documentElement.classList.add('theme-dark');
    } else {
      document.documentElement.classList.remove('dark');
      document.documentElement.classList.remove('theme-dark');
    }

    // Save logic is handled in toggle/set methods
  }

  return {
    theme: getTheme(),
    init() {
      // Apply initial theme
      this.setTheme(this.theme, false);

      // Listen for system changes if in system mode
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if (this.theme === 'system') {
          this.setTheme('system', false);
        }
      });
    },
    dark: false, // Legacy for compatibility
    toggleTheme() {
      // Cyclical toggle: light -> dark -> system -> light
      if (this.theme === 'light') {
        this.setTheme('dark');
      } else if (this.theme === 'dark') {
        this.setTheme('system');
      } else {
        this.setTheme('light');
      }
    },
    setTheme(value, save = true) {
      this.theme = value;

      let isDark = false;
      if (value === 'dark') {
        isDark = true;
      } else if (value === 'light') {
        isDark = false;
      } else if (value === 'system') {
        isDark = !!window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      }

      this.dark = isDark;

      // Apply classes directly
      if (isDark) {
        document.documentElement.classList.add('dark');
        document.documentElement.classList.add('theme-dark');
      } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.remove('theme-dark');
      }

      if (save) {
        window.localStorage.setItem('theme', value);
        // Optional: Call backend to sync native window
        fetch('/admin/settings/theme', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ theme: value })
        }).catch(err => console.error('Failed to sync theme', err));
      }
    },
    isSideMenuOpen: false,
    toggleSideMenu() {
      this.isSideMenuOpen = !this.isSideMenuOpen
    },
    closeSideMenu() {
      this.isSideMenuOpen = false
    },
    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
    toggleSidebarCollapse() {
      this.sidebarCollapsed = !this.sidebarCollapsed
      localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed)
    },
    isNotificationsMenuOpen: false,
    toggleNotificationsMenu() {
      this.isNotificationsMenuOpen = !this.isNotificationsMenuOpen
    },
    closeNotificationsMenu() {
      this.isNotificationsMenuOpen = false
    },
    isProfileMenuOpen: false,
    toggleProfileMenu() {
      this.isProfileMenuOpen = !this.isProfileMenuOpen
    },
    closeProfileMenu() {
      this.isProfileMenuOpen = false
    },
    isPagesMenuOpen: false,
    togglePagesMenu() {
      this.isPagesMenuOpen = !this.isPagesMenuOpen
    },
    // Modal
    isModalOpen: false,
    trapCleanup: null,
    openModal() {
      this.isModalOpen = true
      this.trapCleanup = focusTrap(document.querySelector('#modal'))
    },
    closeModal() {
      this.isModalOpen = false
      this.trapCleanup()
    },
  }
}


