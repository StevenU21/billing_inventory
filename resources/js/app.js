/**
 * Main Application Entry Point
 * Imports and initializes all required modules
 */

// Core dependencies
import './bootstrap';
import '../css/app.css';
import Alpine from 'alpinejs';
import 'flowbite';
import 'flowbite-datepicker';

// Application modules
import { alpineData } from './alpine-data';
import notificationsBell from './notifications-bell';
import { initLoader } from './loader';
import {
    salesDropdownMenu,
    dropdownMenu,
    usuariosDropdownMenu,
    inventoryDropdownMenu,
    purchaseDropdownMenu,
    adminDropdownMenu
} from './sidebar-data';

// Import and expose Chart.js globally
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Initialize Loader
initLoader();

// Register Alpine Data
Alpine.data('data', alpineData);
Alpine.data('salesDropdownMenu', salesDropdownMenu);
Alpine.data('dropdownMenu', dropdownMenu);
Alpine.data('usuariosDropdownMenu', usuariosDropdownMenu);
Alpine.data('inventoryDropdownMenu', inventoryDropdownMenu);
Alpine.data('purchaseDropdownMenu', purchaseDropdownMenu);
Alpine.data('adminDropdownMenu', adminDropdownMenu);

// Make available globally for x-data="{ ...notificationsBell(...) }" usage
window.notificationsBell = notificationsBell;

window.Alpine = Alpine;
Alpine.start();
