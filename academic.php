<?php session_start(); ?>
<?php $title = "Akademik Siyosat | Kokand University"; ?>
<?php require "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>

<style>
    :root {
        --primary-red: #b91c1c;
        --dark-blue: #0f172a;
        --slate-500: #64748b;
        --bg-gray: #f1f5f9;
    }

    body {
        background-color: var(--bg-gray);
        color: #334155;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        line-height: 1.6;
        overflow-x: hidden;
    }

    /* Mobile First Sidebar */
    .sticky-nav {
        position: sticky;
        top: 20px;
        max-height: 70vh;
        overflow-y: auto;
        padding: 15px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }

    .nav-link {
        color: var(--slate-500);
        font-size: 0.8rem;
        font-weight: 500;
        padding: 8px 10px;
        border-radius: 6px;
        margin-bottom: 2px;
        transition: 0.2s;
    }

    .nav-link:hover { background: #fee2e2; color: var(--primary-red); }
    .nav-link.active { background: var(--primary-red) !important; color: white !important; }

    /* Content Area */
    .policy-paper {
        background: white;
        padding: 25px; /* Mobile padding */
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.02);
    }

    @media (min-width: 992px) {
        .policy-paper { padding: 50px; }
        .sticky-nav { top: 100px; max-height: 85vh; }
    }

    .section-title {
        color: var(--primary-red);
        font-weight: 800;
        text-transform: uppercase;
        margin-top: 45px;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        border-bottom: 2px solid #fee2e2;
        padding-bottom: 8px;
    }

    .policy-text { text-align: justify; margin-bottom: 1.2rem; font-size: 0.95rem; }
    
    .abbr-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 8px;
        background: #f8fafc;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .abbr-box b { color: var(--primary-red); min-width: 50px; display: inline-block; }

    .highlight-box {
        background: #fff5f5;
        border-left: 4px solid var(--primary-red);
        padding: 15px;
        margin: 15px 0;
        border-radius: 4px;
    }

    #topBtn {
        position: fixed; bottom: 20px; right: 20px;
        width: 40px; height: 40px; border-radius: 50%;
        background: var(--primary-red); color: white; border: none;
        display: none; z-index: 1000;
        opacity: 50%;
    }
</style>

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php require "Includes/navbar.php"; ?>

    <div class="container-fluid container-lg py-4">
        <div class="row">
            <div class="col-lg-3">
                <div class="sticky-nav" id="policyMenu">
                    <h6 class="fw-bold mb-3 px-2 text-dark border-bottom pb-2">BO'LIMLAR</h6>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="#sec1">1. Qo'llanish sohasi</a>
                        <a class="nav-link" href="#sec2">2. Qisqartmalar</a>
                        <a class="nav-link" href="#sec3">3. Maqsad va prinsiplar</a>
                        <a class="nav-link" href="#sec4">4. Qabul siyosati</a>
                        <a class="nav-link" href="#sec5">5. Sifatli ta’lim</a>
                        <a class="nav-link" href="#sec6">6. Akademik halollik</a>
                        <a class="nav-link" href="#sec7">7. Dasturlar ishlab chiqish</a>
                        <a class="nav-link" href="#sec8">8. O'quv jarayoni</a>
                        <a class="nav-link" href="#sec9">9. Baholash siyosati</a>
                        <a class="nav-link" href="#sec10">10. Ko'chirish va tiklash</a>
                        <a class="nav-link" href="#sec11">11. Bandlik va karyera</a>
                        <a class="nav-link" href="#sec12">12. Ochiqlik siyosati</a>
                        <a class="nav-link" href="#sec13">13. Mobillik va aloqalar</a>
                        <a class="nav-link" href="#sec14">14. Dual ta’lim</a>
                        <a class="nav-link" href="#sec15">15. Malaka oshirish</a>
                    </nav>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="policy-paper" data-bs-spy="scroll" data-bs-target="#policyMenu" data-bs-offset="150">
                    
                    <header class="text-center mb-5">
                        <h1 class="h2 fw-bold text-dark">ACADEMIC POLICY</h1>
                        <p class="text-danger fw-bold mb-0">KOKAND UNIVERSITY</p>
                        <div style="height:3px; width:50px; background:var(--primary-red); margin: 15px auto;"></div>
                    </header>

                    <section id="sec1">
                        <h4 class="section-title">1. QO'LLANISH SOHASI</h4>
                        <p class="policy-text">Akademik siyosat - ta’lim jarayonini tashkil etish samaradorligini oshirishga va talabalar rivojlanishi uchun sharoit yaratishga yo‘naltirilgan qoidalar majmuidir.</p>
                        <ul class="small text-muted ps-3">
                            <li>O’zbekiston Respublikasining "Ta’lim to’g’risida"gi qonuni (2020-yil).</li>
                            <li>Kredit-modul tizimi tartibi (VM-824).</li>
                            <li>Talabalar bilimini baholash tizimi (№3069).</li>
                        </ul>
                    </section>

                    <section id="sec2">
                        <h4 class="section-title">2. QISQARTMALAR</h4>
                        <div class="abbr-grid small">
                            <div class="abbr-box"><b>OTM:</b> Oliy ta’lim muassasasi</div>
                            <div class="abbr-box"><b>GPA:</b> O’rtacha ball</div>
                            <div class="abbr-box"><b>ARM:</b> Axborot resurs markazi</div>
                            <div class="abbr-box"><b>YaN:</b> Yakuniy nazorat</div>
                            <div class="abbr-box"><b>BMI:</b> Bitiruv malakaviy ishi</div>
                            <div class="abbr-box"><b>YaDAS:</b> Yakuniy davlat attestatsiyasi</div>
                        </div>
                    </section>

                    <section id="sec3">
                        <h4 class="section-title">3. MAQSAD VA PRINSIPLAR</h4>
                        <p class="policy-text">Asosiy maqsad — mehnat bozori ehtiyojlariga javob beradigan raqobatbardosh kadrlar tayyorlash. Universitet barcha talabalarga yuqori sifatli ta’lim olish uchun teng imkoniyatlar kafolatlaydi.</p>
                    </section>

                    <section id="sec6">
                        <h4 class="section-title">6. AKADEMIK HALOLLIK</h4>
                        <div class="highlight-box small">
                            <b>Antiplagiat:</b> Universitet <i>strikeplagiarism.com</i> tizimidan foydalanadi. BMI va dissertatsiyalarda originallik darajasi kamida <b>60%</b> bo'lishi shart.
                        </div>
                    </section>

                    <section id="sec8">
                        <h4 class="section-title">8. TA’LIM JARAYONINI TASHKIL ETISH</h4>
                        <div class="table-responsive small">
                            <table class="table table-bordered bg-white">
                                <tr class="bg-light"><th>Ko'rsatkich</th><th>Me'yor</th></tr>
                                <tr><td>1 Kredit yuklamasi</td><td>30 akademik soat</td></tr>
                                <tr><td>Dars davomiyligi</td><td>40 daqiqa</td></tr>
                                <tr><td>Yillik yuklama</td><td>60 kredit</td></tr>
                                <tr><td>Dars qoldirish limiti</td><td>74 soat (sababsiz)</td></tr>
                            </table>
                        </div>
                        <p class="small text-danger"><b>Diqqat:</b> Semestrda 74 soat sababsiz dars qoldirgan talaba o'qishdan chetlashtiriladi.</p>
                    </section>

                    <section id="sec9">
                        <h4 class="section-title">9. BAHOLASH SIYOSATI</h4>
                        <p class="policy-text">Talabalar bilimi JN (joriy), ON (oraliq) va YaN (yakuniy) nazoratlar orqali 100 ballik tizimda baholanadi.</p>
                    </section>

                    <section id="sec10">
                        <h4 class="section-title">10. KO'CHIRISH VA TIKLASH</h4>
                        <p class="policy-text">O'qishni ko'chirish va qayta tiklash VM-393 sonli qaror asosida, semestrlar oralig'idagi ta'til vaqtida amalga oshiriladi.</p>
                    </section>

                    <section id="sec14">
                        <h4 class="section-title">14. DUAL TA’LIM</h4>
                        <p class="policy-text">Nazariy bilimlarni bevosita ishlab chiqarish bilan bog'lash. Fan hajmining kamida 30% qismi korxonalarda o'tilishi rejalashtiriladi.</p>
                    </section>

                    <section id="sec15" class="mb-5">
                        <h4 class="section-title">15. MALAKA OSHIRISH</h4>
                        <p class="policy-text">Professor-o'qituvchilar har 3 yilda kamida bir marta o'z malakalarini oshirishlari va yangi texnologiyalarni o'zlashtirishlari shart.</p>
                    </section>

                </div>
            </div>
        </div>
    </div>

    <button onclick="topFunction()" id="topBtn"><i class="bi opacity-75 bi-arrow-up-short h4">Up</i></button>

    <script>
        // Scroll logic for Top Button
        window.onscroll = function() {
            let btn = document.getElementById("topBtn");
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                btn.style.display = "flex";
                btn.style.alignItems = "center";
                btn.style.justifyContent = "center";
            } else {
                btn.style.display = "none";
            }
        };

        function topFunction() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // Mobile-friendly ScrollSpy initialization
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#policyMenu',
            offset: 160
        });
    </script>

    <?php require "Includes/footer.php"; ?>
</body>