document.onreadystatechange = function () {
    const percentText = document.getElementById("percent-text");
    const loader = document.getElementById("loader-wrapper");

    // Sahifadagi barcha muhim elementlarni olamiz (rasmlar, scriptlar va h.k.)
    const resources = document.querySelectorAll('img, script, link[rel="stylesheet"]');
    const totalResources = resources.length;
    let loadedResources = 0;

    if (totalResources === 0) {
        // Agar sahifa bo'm-bo'sh bo'lsa, srazu 100% qilamiz
        updateProgress(100);
    }

    function updateProgress(percent) {
        percentText.innerText = Math.round(percent) + "%";

        if (percent >= 100) {
            setTimeout(() => {
                loader.classList.add("loader-hidden");
            }, 500);
        }
    }

    // Har bir resurs yuklanganda hisoblagichni oshiramiz
    resources.forEach((resource) => {
        // Agar resurs allaqachon yuklangan bo'lsa (keshdan)
        if (resource.complete) {
            incrementLoader();
        } else {
            // Yuklanishni kutamiz
            resource.addEventListener('load', incrementLoader);
            resource.addEventListener('error', incrementLoader); // Xato bo'lsa ham davom etaveradi
        }
    });

    function incrementLoader() {
        loadedResources++;
        const percentage = (loadedResources / totalResources) * 100;
        updateProgress(percentage);
    }
};

// Qo'shimcha xavfsizlik: Agar biror resurs qolib ketsa, 
// sahifa to'liq yuklanganda baribir yopiladi
window.onload = function () {
    const loader = document.getElementById("loader-wrapper");
    const percentText = document.getElementById("percent-text");
    percentText.innerText = "100%";
    setTimeout(() => {
        loader.classList.add("loader-hidden");
    }, 500);
};