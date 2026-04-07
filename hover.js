// Sichqoncha harakatini kuzatish
document.addEventListener('mousemove', e => {
    document.body.style.setProperty('--mouse-x', e.clientX + 'px');
    document.body.style.setProperty('--mouse-y', e.clientY + 'px');
});

// Bosgandagi animatsiya
document.addEventListener('mousedown', e => {
    const ripple = document.createElement('div');
    ripple.className = 'click-effect';
    ripple.style.left = e.clientX + 'px';
    ripple.style.top = e.clientY + 'px';

    document.body.appendChild(ripple);

    // Animatsiya tugagach elementni o'chirish
    setTimeout(() => {
        ripple.remove();
    }, 600);
});