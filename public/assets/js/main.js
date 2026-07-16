/*
    main.js - the only site-wide script
    just two jobs: the mobile hamburger menu and fading out flash toasts.
    page-specific scripts (password toggle, payment method switch, etc.)
    live inline in their own php files.
*/

(function () {

    // mobile nav toggle
    var toggleBtn = document.getElementById('nav-toggle');
    var nav       = document.getElementById('site-nav');

    if (toggleBtn && nav) {
        toggleBtn.addEventListener('click', function () {
            var isOpen = nav.classList.contains('nav-open');

            if (isOpen) {
                nav.classList.remove('nav-open');
                toggleBtn.setAttribute('aria-expanded', 'false');
            } else {
                nav.classList.add('nav-open');
                toggleBtn.setAttribute('aria-expanded', 'true');
            }
        });

        // close when clicking outside the nav
        document.addEventListener('click', function (e) {
            if (!nav.contains(e.target)) {
                nav.classList.remove('nav-open');
                toggleBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }


    // flash message auto-dismiss (4 seconds)
    var flashWrap = document.querySelector('.flash-wrap');

    if (flashWrap) {
        setTimeout(function () {
            flashWrap.style.transition = 'opacity 0.5s ease';
            flashWrap.style.opacity    = '0';

            setTimeout(function () {
                if (flashWrap.parentNode) {
                    flashWrap.parentNode.removeChild(flashWrap);
                }
            }, 500);
        }, 4000);
    }

})();
