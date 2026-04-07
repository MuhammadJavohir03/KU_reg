(function () {
    const percentEl = document.getElementById('percent-text');
    const loaderWrapper = document.getElementById('loader-wrapper');
    let currentPercent = 0;
    let isFinished = false;

    // Sahifa scrollini to'xtatib turish
    document.body.style.overflow = "hidden";

    const updateProgress = () => {
        if (isFinished) return;

        // Boshida tez, oxirida (90% dan keyin) sekinlashadi
        let step = currentPercent < 70 ? Math.floor(Math.random() * 10) + 2 : Math.floor(Math.random() * 2) + 1;
        currentPercent += step;

        if (currentPercent >= 97) {
            currentPercent = 97; // 'load' eventini kutish nuqtasi
        }

        if (percentEl) {
            percentEl.innerText = currentPercent + "%";
        }

        if (!isFinished) {
            let delay = currentPercent > 80 ? 250 : 60;
            setTimeout(updateProgress, delay);
        }
    };

    const hideLoader = () => {
        if (isFinished) return;
        isFinished = true;
        
        if (percentEl) percentEl.innerText = "100%";
        
        // 100% ni foydalanuvchi ko'rishi uchun 300ms kutamiz
        setTimeout(() => {
            if (loaderWrapper) {
                loaderWrapper.style.opacity = "0";
                // Blur effektini ham sekin yo'qotish
                loaderWrapper.style.backdropFilter = "blur(0px) brightness(1)";
                
                setTimeout(() => {
                    loaderWrapper.style.display = "none";
                    document.body.style.overflow = "auto"; // Scrollni qaytarish
                }, 600);
            }
        }, 300);
    };

    // Resurslar (rasmlar, css) to'liq yuklanganda
    window.addEventListener('load', hideLoader);

    // Favqulodda yopish (agar juda sekin bo'lsa - 8 soniya)
    setTimeout(hideLoader, 8000);

    updateProgress();
})();