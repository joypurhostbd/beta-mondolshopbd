/**
 * Main Frontend Entrypoint
 * MondolShopBD Modular Monolith
 */
import { initLiveSearch } from './frontend/search';
import { initCart } from './frontend/cart';
import { initCarousels } from './frontend/carousel';

document.addEventListener('DOMContentLoaded', () => {
    initLiveSearch();
    initCart();
    initCarousels();
});