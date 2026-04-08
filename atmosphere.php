<?php
// Includes/atmosphere.php
date_default_timezone_set('Asia/Tashkent');

$now = new DateTime();
$hour = (int)$now->format('H');
$day_of_week = (int)$now->format('N');
$time_str = $now->format('H:i'); // 24 soatlik format [cite: 2026-04-06]

// Ish vaqti: Dush-Jum, 08:30-18:00
$is_working = ($day_of_week <= 5 && $time_str >= '08:30' && $time_str <= '18:00');
$is_night = ($hour < 7 || $hour >= 18 || ($hour == 18 && $minute >= 30));
?>

<style>
    /* --- ASOSIY FON VA ATMOSFERA --- */
    body {
        margin: 0;
        min-height: 100vh;
        transition: background 3s cubic-bezier(0.4, 0, 0.2, 1);
        background: <?= $is_night 
            ? 'radial-gradient(circle at 50% 0%, #00567a 0%, #0011a7 100%)' 
            : 'radial-gradient(circle at 50% 0%, #38bdf8 0%, #818cf8 100%)' ?> !important;
        background-attachment: fixed;
    }

    /* --- ULTRA MODERN DYNAMIC ISLAND --- */
    .dynamic-island {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        padding: 4px 6px;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border-radius: 30px;
        border: 0.5px solid rgba(255, 255, 255, 0.2);
        z-index: 100000;
        box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        min-width: 180px;
        justify-content: space-between;
    }

    /* Hover qilinganda island kengayadi */
    .dynamic-island:hover {
        padding: 4px 18px;
        min-width: 240px;
        background: rgba(0, 0, 0, 0.95);
    }

    .di-section-left {
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.1);
        padding: 6px 14px;
        border-radius: 25px;
        margin-right: 10px;
    }

    .di-time {
        font-size: 16px;
        font-weight: 800;
        color: #fff;
        letter-spacing: 0.5px;
        font-family: 'JetBrains Mono', monospace; /* Raqamlar sakramashi uchun */
    }

    .di-section-right {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-right: 12px;
    }

    .di-status-text {
        font-size: 11px;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.9);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        white-space: nowrap;
    }

    .di-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: <?= $is_working ? '#2ecc71' : '#e74c3c' ?>;
        box-shadow: 0 0 12px <?= $is_working ? '#2ecc71' : '#e74c3c' ?>;
        position: relative;
    }

    .di-indicator::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        border: 2px solid inherit;
        background: inherit;
        opacity: 0.4;
        animation: di-pulse 2s infinite;
    }

    @keyframes di-pulse {
        0% { transform: scale(1); opacity: 0.4; }
        100% { transform: scale(2.5); opacity: 0; }
    }

    /* --- MOBIL MOSLASHUV (Aqlli) --- */
    @media (max-width: 600px) {
        .dynamic-island {
            top: 10px;
            min-width: 140px;
            padding: 3px 5px;
            transform: translateX(-50%) scale(0.9);
        }
        .di-status-text {
            font-size: 10px;
        }
        /* Mobilda matnni qisqartirish */
        .di-status-text span { display: none; }
        .di-status-text::after { content: '<?= $is_working ? "ISHDA" : "DAMDA" ?>'; }
        
        .dynamic-island:hover {
            min-width: 160px;
            padding: 3px 8px;
        }
    }

    /* --- BARGLAR TIZIMI --- */
    .leaf-p {
        position: fixed; top: -20px; z-index: -1;
        width: 15px; height: 10px;
        background: <?= $is_night ? '#4a5568' : '#2d6a4f' ?>;
        border-radius: 100% 0 100% 0;
        opacity: 0.5;
        animation: leafFall 10s linear infinite;
    }

    @keyframes leafFall {
        0% { transform: translateY(0) rotate(0deg); opacity: 0; }
        10% { opacity: 0.5; }
        100% { transform: translateY(110vh) rotate(720deg) translateX(100px); opacity: 0; }
    }
</style>
<div class="mt-5 text-white ms-5"><h3>Sayt Test rejimida ishlamoqda</h3></div>
<div id="leaf-field"></div>

<div class="dynamic-island">
    <div class="di-section-left">
        <div class="di-time" id="di-clock"><?= $time_str ?></div>
    </div>
    <div class="di-section-right">
        <div class="di-status-text">
            <span>Registrator:</span> <?= $is_working ? 'Ishda' : 'Damda' ?>
        </div>
        <div class="di-indicator"></div>
    </div>
</div>

<script>
    // 1. Soatni 24 soatlik rejimda yangilash
    function updateClock() {
        const d = new Date();
        const h = String(d.getHours()).padStart(2, '0');
        const m = String(d.getMinutes()).padStart(2, '0');
        const s = String(d.getSeconds()).padStart(2, '0');
        // Soniyali effektni faqat islandga hover qilganda yoki doimiy ko'rsatish mumkin
        document.getElementById('di-clock').innerText = h + ":" + m;
    }
    setInterval(updateClock, 1000);

    // 2. Barglarni yaratish
    function createLeaf() {
        const leaf = document.createElement('div');
        leaf.className = 'leaf-p';
        leaf.style.left = Math.random() * 100 + "vw";
        leaf.style.animationDuration = (Math.random() * 5 + 7) + "s";
        document.getElementById('leaf-field').appendChild(leaf);
        setTimeout(() => leaf.remove(), 12000);
    }
    setInterval(createLeaf, 900);

    // Eski elementlarni butunlay tozalash
    const oldElements = ['.status-capsule', '.sky-wrapper', '.registrator-indicator'];
    oldElements.forEach(selector => {
        document.querySelectorAll(selector).forEach(el => el.remove());
    });
</script>