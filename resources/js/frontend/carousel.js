/**
 * Frontend Carousel Initializer Module
 */
export function initCarousels() {
    if (typeof jQuery !== 'undefined' && jQuery.fn.owlCarousel) {
        jQuery('.main_slider').owlCarousel({
            items: 1,
            loop: true,
            dots: true,
            autoplay: true,
            autoplayTimeout: 4000,
            autoplayHoverPause: true,
            margin: 0,
            nav: false
        });

        jQuery('.category-slider').owlCarousel({
            margin: 15,
            loop: false,
            dots: false,
            nav: true,
            responsive: {
                0: { items: 3 },
                600: { items: 5 },
                1000: { items: 8 }
            }
        });
    }
}