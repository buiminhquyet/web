/**
 * QUYETDEV Shop - Main Script
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Preloader
    const preloader = document.getElementById('preloader');
    if (preloader) {
        let preloaderHidden = false;
        const hidePreloader = () => {
            if(preloaderHidden) return;
            preloaderHidden = true;
            preloader.style.opacity = '0';
            setTimeout(() => { preloader.style.display = 'none'; }, 500);
        };
        
        window.addEventListener('load', () => {
            setTimeout(hidePreloader, 300);
        });
        
        // Failsafe: if window.load doesn't fire within 2s, hide it anyway
        setTimeout(hidePreloader, 2000);
    }

    // 2. Theme Toggle
    const themeBtn = document.getElementById('theme-toggle');
    const html = document.documentElement;
    
    const applyTheme = (theme) => {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        const icon = document.querySelector('#theme-toggle i');
        if (icon) {
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    };

    const initialTheme = localStorage.getItem('theme') || 'dark';
    applyTheme(initialTheme);

    if (themeBtn) {
        themeBtn.addEventListener('click', (e) => {
            const curr = html.getAttribute('data-theme');
            const next = curr === 'dark' ? 'light' : 'dark';
            applyTheme(next);
        });
    }

    // 3. Spotlight Effect
    const cards = document.querySelectorAll('.spotlight');
    cards.forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
            
            // For older browsers or specific CSS logic
            const ripple = card.querySelector('.spotlight-overlay');
            if (ripple) {
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
            }
        });
    });

    // 4. Ripple Effect on Buttons
    const rippleBtns = document.querySelectorAll('.btn-premium, .btn-secondary');
    rippleBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const x = e.clientX - e.target.offsetLeft;
            const y = e.clientY - e.target.offsetTop;
            
            const ripple = document.createElement('span');
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            ripple.className = 'btn-ripple';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // 5. Navbar Sticky Background
    window.addEventListener('scroll', () => {
        const header = document.querySelector('header');
        if (window.scrollY > 50) {
            header.classList.add('glass');
            header.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
        } else {
            header.classList.remove('glass');
            header.style.boxShadow = 'none';
        }
    });

    // 6. Mobile Nav Active State
    const mobileItems = document.querySelectorAll('.mobile-item');
    mobileItems.forEach(item => {
        item.addEventListener('click', () => {
            mobileItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
        });
    });
});
