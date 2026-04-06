export function salesDropdownMenu() {
    return {
        isOpen: localStorage.getItem('salesDropdownOpen') === 'true',
        toggleSalesDropdown() {
            this.isOpen = !this.isOpen;
            localStorage.setItem('salesDropdownOpen', this.isOpen);
        },
        init() {
            this.isOpen = localStorage.getItem('salesDropdownOpen') === 'true';
        }
    }
}

export function dropdownMenu() {
    return {
        isOpen: localStorage.getItem('catalogDropdownOpen') === 'true',
        toggleDropdown() {
            this.isOpen = !this.isOpen;
            localStorage.setItem('catalogDropdownOpen', this.isOpen);
        },
        init() {
            this.isOpen = localStorage.getItem('catalogDropdownOpen') === 'true';
        }
    }
}

export function usuariosDropdownMenu() {
    return {
        isOpen: localStorage.getItem('usuariosDropdownOpen') === 'true',
        toggleUsuariosDropdown() {
            this.isOpen = !this.isOpen;
            localStorage.setItem('usuariosDropdownOpen', this.isOpen);
        },
        init() {
            this.isOpen = localStorage.getItem('usuariosDropdownOpen') === 'true';
        }
    }
}

export function inventoryDropdownMenu() {
    return {
        isOpen: localStorage.getItem('inventoryDropdownOpen') === 'true',
        toggleInventoryDropdown() {
            this.isOpen = !this.isOpen;
            localStorage.setItem('inventoryDropdownOpen', this.isOpen);
        },
        init() {
            this.isOpen = localStorage.getItem('inventoryDropdownOpen') === 'true';
        }
    }
}

export function purchaseDropdownMenu() {
    return {
        isOpen: localStorage.getItem('purchaseDropdownOpen') === 'true',
        togglePurchaseDropdown() {
            this.isOpen = !this.isOpen;
            localStorage.setItem('purchaseDropdownOpen', this.isOpen);
        },
        init() {
            this.isOpen = localStorage.getItem('purchaseDropdownOpen') === 'true';
        }
    }
}

export function adminDropdownMenu() {
    return {
        isOpen: localStorage.getItem('adminDropdownOpen') === 'true',
        toggleAdminDropdown() {
            this.isOpen = !this.isOpen;
            localStorage.setItem('adminDropdownOpen', this.isOpen);
        },
        init() {
            this.isOpen = localStorage.getItem('adminDropdownOpen') === 'true';
        }
    }
}
