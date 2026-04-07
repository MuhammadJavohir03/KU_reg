<div id="loader-wrapper">
    <div class="book-container">
        <div class="book">
            <div class="page"></div>
            <div class="page"></div>
            <div class="page"></div>
            <div class="page"></div>
            <div class="page"></div>
        </div>
        <div class="loader-text">
            <span id="percent-text">0%</span>
            <p>Bilim yuklanmoqda...</p>
        </div>
    </div>
</div>

<style>
    #loader-wrapper {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999999;
        /* Oq fon butunlay olib tashlandi */
        background: rgba(0, 0, 0, 0) !important;
        /* Shishasimon effekt va orqa fonni biroz qorong'ulashtirish */
        backdrop-filter: blur(15px) brightness(0.8);
        -webkit-backdrop-filter: blur(15px) brightness(0.8);
        transition: opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .book-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        /* Elementlar ko'rinishi uchun soya */
        filter: drop-shadow(0 10px 25px rgba(0, 0, 0, 0.3));
    }

    .book {
        width: 60px;
        height: 45px;
        position: relative;
        perspective: 150px;
    }

    .page {
        width: 30px;
        height: 45px;
        position: absolute;
        left: 30px;
        transform-origin: left center;
        border-radius: 0 4px 4px 0;
        animation: flip 1.5s infinite ease-in-out;
    }

    /* DINAMIK RANG TIZIMI */
    <?php if ($is_night): ?>

    /* Tungi rejim: Kitob va matn oqaradi */
    .page {
        background: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    #percent-text {
        color: #ffffff !important;
    }

    .loader-text p {
        color: #ffffff !important;
        opacity: 0.9;
    }

    #loader-wrapper {
        background: rgba(15, 23, 42, 0.3) !important;
    }

    <?php else: ?>

    /* Kunduzgi rejim: Moviy ranglar */
    .page {
        background: #4e73df;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    #percent-text {
        background: linear-gradient(to right, #4e73df, #224abe);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .loader-text p {
        color: #4e73df;
    }

    <?php endif; ?>

    /* Varaqlash animatsiyasi */
    .page:nth-child(1) {
        animation-delay: 0.1s;
        z-index: 5;
    }

    .page:nth-child(2) {
        animation-delay: 0.2s;
        z-index: 4;
    }

    .page:nth-child(3) {
        animation-delay: 0.3s;
        z-index: 3;
    }

    .page:nth-child(4) {
        animation-delay: 0.4s;
        z-index: 2;
    }

    .page:nth-child(5) {
        animation-delay: 0.5s;
        z-index: 1;
    }

    @keyframes flip {
        0% {
            transform: rotateY(0deg);
        }

        80%,
        100% {
            transform: rotateY(-180deg);
        }
    }

    .loader-text {
        margin-top: 35px;
        text-align: center;
        font-family: 'Inter', sans-serif;
    }

    #percent-text {
        font-size: 38px;
        font-weight: 900;
        display: block;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .loader-text p {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin-top: 10px;
    }
</style>