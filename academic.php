<?php $title = "Bosh Sahifa"; ?>
<?php require "Includes/header.php"; ?>

<body>
    <?php require "Includes/navbar.php"; ?>

    <div class="container position-relative">
        <button onclick="topFunction()" id="topBtn" class=" btn btn-outline-danger">
            <h1>↑</h1>
        </button>
        <script>
            function topFunction() {
                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            }
        </script>
    </div>

    <div class="container">

        <nav id="navbar-example2" class="navbar bg-body-tertiary px-3 mb-3">
            <a class="navbar-brand text-danger" href="#">
                <h3>Academic Policy Kokand University</h3>
            </a>
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading1">QO'LLANISH SOHASI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading2">QISTQARMALAR VA SHARTLI BELGILAR</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading3">AKADEMIK SIYOSATNING MAQSADI VA PRINSIPLARI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading4">UNIVERSITETGA O’QISHGA QABUL QILISH SIYOSATI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading5">SIFATLI TA’LIM SIYOSATI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading6">AKADEMIK HALOLLIK SIYOSATI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading7">TA’LIM DASTURLARINI ISHLAB CHIQISH VA TASDIQLASH SIYOSATI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading8">TA’LIM JARAYONINI REJALASHTIRISH VA TASHKIL ETISH SIYOSATI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading9">TALABALARNING BAHOLASH SIYOSATI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading10">TALABALARNI O’QISHINI KO’CHIRISH, TIKLASH, CHETLASHTIRISH VA AKADEMIK TA’TIL BERISH SIYOSATI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading11">BITIRUVCHILARNING KASBIY YO‘NALISH, BANDLIK VA KARYERA (MANSAB) O‘SISH SIYOSATI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading12">OCHIQLIK VA OSHKORALIK SIYOSATI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading13">AKADEMIK MOBILLIK VA HALQARO ALOQALAR SIYOSATI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading14">DUAL TA’LIM ELEMENTLARINI AMALGA OSHIRISH SIYOSATI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#scrollspyHeading15">PROFESSOR- O’QITUVCHILARNING MALAKA OSHIRISH SIYOSATI</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Sahifalar</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#scrollspyHeading1">QO'LLANISH SOHASI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading2">QISTQARMALAR VA SHARTLI BELGILAR</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading3">AKADEMIK SIYOSATNING MAQSADI VA PRINSIPLARI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading4">UNIVERSITETGA O’QISHGA QABUL QILISH SIYOSATI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading5">SIFATLI TA’LIM SIYOSATI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading6">AKADEMIK HALOLLIK SIYOSATI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading7">TA’LIM DASTURLARINI ISHLAB CHIQISH VA TASDIQLASH SIYOSATI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading8">TA’LIM JARAYONINI REJALASHTIRISH VA TASHKIL ETISH SIYOSATI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading9">TALABALARNING BAHOLASH SIYOSATI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading10">TALABALARNI O’QISHINI KO’CHIRISH, TIKLASH, CHETLASHTIRISH VA AKADEMIK TA’TIL BERISH SIYOSATI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading11">BITIRUVCHILARNING KASBIY YO‘NALISH, BANDLIK VA KARYERA (MANSAB) O‘SISH SIYOSATI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading12">OCHIQLIK VA OSHKORALIK SIYOSATI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading13">AKADEMIK MOBILLIK VA HALQARO ALOQALAR SIYOSATI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading14">DUAL TA’LIM ELEMENTLARINI AMALGA OSHIRISH SIYOSATI</a></li>
                        <li><a class="dropdown-item" href="#scrollspyHeading15">PROFESSOR- O’QITUVCHILARNING MALAKA OSHIRISH SIYOSATI</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#scrollspyHeading5">Fifth</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div data-bs-spy="scroll" data-bs-target="#navbar-example2" data-bs-root-margin="0px 0px -40%" data-bs-smooth-scroll="true" class="scrollspy-example bg-body-tertiary p-3 rounded-2" tabindex="0">
            <h4 id="scrollspyHeading1">QO'LLANISH SOHASI</h4>
            <p>Akademik siyosat - ta’lim jarayonini tashkil etish samaradorligini, ta’lim sifatini oshirishga, talabalarning shaxsiy rivojlanishi uchun qulay shart-sharoitlarni yaratishga yordam berishi kerak bo‘lgan qoidalar va tartiblar ro‘yxati.<br>
                <br>
                1. O’zbekiston Respublikasining ta’lim to’g’risidagi qonuni. Qonunchilik palatasi tomonidan 2020-yil 19-mayda qabul qilingan Senat tomonidan 2020-yil 7-avgustda ma’qullangan. <br>
                <br>
                2. O‘zbekiston Respublikasi Oliy va o‘rta maxsus ta’lim vazirining buyrug‘i. Oliy ta’lim muassasalarida talabalar bilimini nazorat qilish va baholash tizimi to‘g‘risidagi nizomni tasdiqlash haqida
                [O‘zbekiston Respublikasi Adliya vazirligi tomonidan 2018-yil 26-sentabrda ro‘yxatdan o‘tkazildi, ro‘yxat raqami 3069]
                <br><br>
                3. O‘zbekiston Respublikasi Vazirlar Mahkamasining qarori. O‘zbekiston Respublikasi oliy ta’lim muassasalari talabalariga akademik ta’til berish to‘g‘risidagi nizomni tasdiqlash haqida 2021-yil 3-iyuldagi 344-son.
                <br><br>
                4. O’zbekiston Respublikasi vazirlar mahkamasining qarori. Oliy ta’lim muassasalarida o’quv jarayoniga kredit-modul tizimini joriy etish tartibi to’g’risida 2020-yil 31-dekabrdagi 824-sonli.
                <br><br>
                5. O’zbekiston Respublikasi Prezidenti qarori Davlat oliy ta’lim muassasalarining akademik va tashkiliy-boshqaruv mustaqilligini taminlash bo’yicha qo’shimcha chora-tadbirlar to’g’risida 2021-yil 24-dekabr, 60-son.
                <br><br>
                6. O’zbekiston Respublikasi Prezidenti qarori Davlat oliy ta’lim muassasalariga moliyaviy mustaqillik berish chora-tadbirlar to’g’risida 2021-yil 24-dekabr, 61-son.
                <br><br>
                7. O’zbekiston Respublikasi Oliy ta’lim, fan va innovatsiyalar vazirining buyrug’i Oliy ta’lim tashkilotlarida xavf-xatar tahliliga asoslangan ta’lim sifati monitoringini joriy etish to’g’risida. 2023-yil 14-sentabr 415-son.
                <br><br>
                8. O‘zbekiston Respublikasi Oliy va o‘rta maxsus ta’lim vazirining buyrug‘i. O’zbekiston Respublikasi oliy ta’lim muassasalari bitiruvchilarining yakuniy davlat attestatsiyasi to’g’risidagi nizomni tasdiqlash haqida. 2009-yil 22-may 160-son.
                <br><br>
                9. O’zbekiston Respublikasi vazirlar mahkamasining qarori. Oliy ta’lim muassasasida sirtqi (maxsus sirtqi) ta’limni tashkil etish tartibi to’g’risida nizom 2017-yil 21-noyabrdagi 930-son.
                <br><br>
                10. O’zbekiston Respublikasi vazirlar mahkamasining qarori. O’zbekiston Respublikasi Oliy ta’lim muassasasida 2 chi va undan keying oliy ta’limni olish tartibi to’g’risidagi nizomni tasdiqlash haqida 2021-yil 24-sentabrdagi 606-son.
            </p>
            <br>
            <h4 id="scrollspyHeading2">QISTQARMALAR VA SHARTLI BELGILAR</h4>
            <p>1. OTM-Oliy ta’lim muassasi
                <br>
                2. OTF va IV-Oliy ta’lim, fan va innovatsiyalar vazirligi
                <br>
                3. QDU-Qarshi davlat universiteti
                <br>
                4. ARM-Axborot resurs markazi
                <br>
                5. RTTM-Raqamli ta’lim texnologiyalari markazi
                <br>
                6. TTJ-Talabalar turar joyi
                <br>
                7. YaDAS-Yakuniy davlat attestatsiya sinovlari
                <br>
                8. GPA- O’rtacha ball
                <br>
                9. MD-Magistrlik dissertatsiyasi
                <br>
                10. KI-Kurs ishi
                <br>
                11. KL-Kurs loyixasi
                <br>
                12. BMI-Bitiruv malakaviy ish
                <br>
                13. YaN- Yakuniy nazorat
                <br>
                14. ON- Oraliq nazorat
                <br>
                15. JN-Joriy nazorat
                <br>
                16. O'D-O‘quv dasturi
                <br>
                17. MT-Malaka talablari
                <br>
                18. MA-Malakaviy amaliyot
                <br>
                19. MT-Mustaqil ta'lim
                <br>
                20. OTDTS-Oliy ta’limning Davlat ta’lim standarti <br>
            </p>
            <h4 id="scrollspyHeading3">AKADEMIK SIYOSATNING MAQSADI VA PRINSIPLARI</h4>
            <p>Akademik siyosat quyidagi yo‘nalishlarni o‘z ichiga oladi:
                <br>
                1. universitetga o’qishga qabul qilish siyosati;
                <br>
                2. sifatli ta’lim siyosati;
                <br>
                3. akademik halollik siyosati;
                <br>
                4. ta’lim dasturlarini ishlab chiqish va tasdiqlash siyosati;
                <br>
                5. ta’lim jarayonini rejalashtirish va tashkil etish siyosati;
                <br>
                6. talabalarning baholash siyosati;
                <br>
                7. talabalarni o‘qishini ko’chirish, tiklash, chetlashtirish va akademik ta’til berish siyosati;
                <br>
                8. bitiruvchilarning kasbiy yo‘nalish, bandlik va karyerada o‘sish siyosati;
                <br>
                9. ochiqlik va shaffoflik siyosati;
                <br>
                10. akademik mobillik va halqaro aloqalar siyosati;
                <br>
                11. dual ta’lim elementlarini amalga oshirish siyosati.
                <br><br>
                3.2 Akademik siyosatning maqsadi - o‘quv jarayonini turli bilim sohalaridagi murakkab masalalarni hal qila oladigan raqobatbardosh bitiruvchilarni tayyorlashga yo‘naltirish va shu orqali mehnat bozori, shaxs ehtiyojlariga javob beradigan va mos keladigan oliy ta‘limda sifatining yuqori darajasiga erishishdir.
                <br>
                3.3 Universitet barcha talabalarga yuqori sifatli va arzon ta’lim olish uchun teng imkoniyatlar yaratadi.
                <br><br>
                3.4 Ta‘lim dasturlarini ishlab chiqishda ishtirok etuvchi barcha manfaatdor tomonlarning fikr-mulohazalarini inobatga olgan holda, ta‘lim dasturlarini takomillashtirish bo‘yicha ishlar tizimli ravishda olib boriladi va universitet bitiruvchilarining rivojlangan vakolatlari va mazmunini muhokama qiladi.
                <br><br>
                3.5 O‘quv jarayoni yuqori malakali professor-o‘qituvchilar tomonidan amalga oshiriladi. Bir qator fanlar bo'yicha ma'ruzalar o'qish uchun universitetlar va ilmiy tashkilotlardan mahalliy va xorijiy olimlar taklif etiladi.
                <br><br>
                3.6 Universitet ta’lim va ilmiy faoliyatni uyg‘unlashtirish, ustuvor ilmiy yo‘nalishlarni rivojlantirish va ijodkor yoshlarni qo‘llab-quvvatlashga yo‘naltirilganligi sababli barcha zarur sharoitlar yaratilgan – axborot manbalaridan foydalanish ta’minlangan, tashkiliy-huquqiy yordam ko‘rsatilgan, moddiy-texnika resurslari ajratilgan. Ilmiy-tadqiqot ishlariga deyarli barcha o‘qituvchilar, magistrantlar va doktorantlar jalb etilgan.
                <br><br>
                3.7 Universitetning xalqaro aloqalarini rivojlantirish xalqaro hamkorlikni tashkil etish qoidalariga muvofiq amalga oshiriladi va quyidagilarni nazarda tutadi:
                <br><br>
                3.7.1 Xalqaro ta‘lim dasturlarida ishtirok etish;
                <br><br>
                3.7.2 Shartnomalar va hamkorlik shartnomalari asosida ta’lim va ilmiy muassasalar bilan ilmiy va akademik hamkorlikni rivojlantirish.
                <br><br>
                3.8 Universitetning xalqaro faoliyati universitetni jahon taʼlim makonida teng huquqli hamkor sifatida toʻliq integratsiyalash, taʼlim sifatini oshirish va kadrlar ilmiy tayyorgarligini oshirish maqsadida taʼlimni xalqarolashtirishga qaratilgan. Universitet tomonidan Erasmus+ dasturi loyihalarini amalga oshirish ta’limni xalqarolashtirishga katta yordam beradi.
                <br><br>
                3.9 Universitet hayotida sport-sog'lomlashtirish ishlarini tashkil etish va ommaviy sportni rivojlantirishga katta e'tibor berilmoqda, buning uchun barcha zarur sharoitlar mavjud. Talabalar ixtiyorida 4 ta sport zali, 6 ta stadion, 5 ta sport majmualari mavjud. Universitetning “Lochin” futbol jamoasi tashkil etilgan.
                <br><br>
                3.10 Universitetda ma’naviy va ma’rifiy ishlarni tashkil etish bo‘limi tomonidan quyidagi ishlar amalga oshiriladi:
                <br><br>
                Talaba-yoshlarni milliy va umuminsoniy qadriyatlarga hurmat, ona Vatanga sadoqat ruhida tarbiyalashga qaratilgan keng qamrovli chora-tadbirlarni amalga oshirish;
                <br><br>
                Talaba-yoshlarni madaniyat, san’at, jismoniy tarbiya va sportga keng jalb etish, axborot texnologiyalaridan foydalanish ko‘nikmalarini rivojlantirish, ular o‘rtasida kitobxonlikni keng targ‘ib qilish, ijtimoiy himoyaga muhtoj, nogironligi bor talabalar, mehribonlik uylarida tarbiyalangan, yetim, boquvchisini yo‘qotgan va ota-ona qaramog‘idan mahrum bo‘lgan talabalarni qo‘llab-quvvatlash;
                <br><br>
                Talaba-qizlarning imkoniyatlarini kengaytirish, ularning jamiyatdagi mavqeini yanada oshirish;
                <br>
                Talabalarning ijtimoiy faolligini oshirish, yoshlar jamoat tashkilotlari va volontyorlik harakatlarini qo‘llab-quvvatlash.
            </p>
            <h4 id="scrollspyHeading4">UNIVERSITETGA O’QISHGA QABUL QILISH SIYOSATI</h4>
            <p>Universitetda qabul jarayonlari boshlansa fuqarolarga ma’lumotlar olishlari va ortiqcha ovoragarchiliklarni oldini olish maqsadida rasmiy veb saytda barcha ma’lumotlar berib boriladi.Universitetning rasmiy veb sayti qarshidu.uz, telegram kanali qardu.uz, facebookda qarshi.du elektron manzillar orqali ma’lumotlar olish mumkin. Call- markaz ham ma’lumot berish uchun faoliyat olib boradi. Qabulga oid ma’lumotlarni olish uchun rasmiy veb saytda quyidagi ma’lumotlar berib boriladi:
                <br><br>
                1. Qo‘shma ta’lim dasturlari;
                <br><br>
                2. Xorijiy talabalar qabuli;
                <br><br>
                3. Kasbiy (ijodiy) imtihonlar dasturi va baholash mezonlari;
                <br><br>
                4. Kasbiy (ijodiy) imtihonlar dasturi va baholash mezonlari (ko‘zi ojizlar uchun);
                <br><br>
                5. Kasbiy (ijodiy) imtihonlarning sanasi, vaqti va manzili;
                <br><br>
                6. Kasbiy (ijodiy) imtihon natijalari;
                <br><br>
                7. Ikkinchi mutaxassislikka qabuli;
                <br><br>
                8. 2024-2025-o‘quv yili uchun qabul parametrlarining tillar kesimidagi taqsimoti;
                <br><br>
                9. Texnikumni muvaffaqiyatli tamomlagan bitiruvchilar uchun;
                <br><br>
                10. Qo‘shma ta’lim dasturlari shartnoma miqdori;
                <br><br>
                11. Xorijiy va nodavlat OTMlardan o‘qishni ko‘chirish bo‘yicha o‘tkaziladigan kasbiy (ijodiy) imtihonlar jadvali;
                <br><br>
                12. Texnikum bitiruvchilarining suhbat natijalari;
                <br><br>
                13. Xorijiy va nodavlat OTMlardan o‘qishni ko‘chirish bo‘yicha o‘tkaziladigan kasbiy (ijodiy) imtihonlar natijalari;
                <br><br>
                14. Magistratura natijalari 2024;
                <br><br>
                15. O‘qishni ko‘chirish, qayta tiklash bo‘yicha mas’ul xodimlar ma’lumoti.
            </p>
            <br>
            <h4 id="scrollspyHeading5">SIFATLI TA’LIM SIYOSATI</h4>
            <p>5. SIFATLI TA’LIM SIYOSATI

                Qarshi davlat universitetida universitet faoliyatining barcha yo‘nalishlarini qamrab olgan sifat siyosati ishlab chiqilgan. Sifat siyosati ta'lim sifati nafaqat ta'lim sifati ekanligini belgilaydi. Ta’lim sifati universitet faoliyatining o‘quv, o‘quv-uslubiy, ilmiy-tadqiqot va boshqa qismlarini qamrab oluvchi yaxlit tizimdir. Shuningdek, sifat ta’lim sifatini ta’minlaydigan shart-sharoitlarga, ya’ni zarur moddiy-texnika bazasi, zamonaviy adabiyotlar, shuningdek, pedagogik kadrlar bilan ta’minlanishiga bog‘liq ekani ham aniqlangan.
                <br><br>
                Ichki ta’lim sifatini ta’minlash bo‘yicha universitetda barcha tashkiliy va huquqiy ishlar amalga oshirilgan. Universitetda barcha o‘quv meyoriy hujjatlar, xususan, Davlat ta’lim standartlari, мalaka talablari, o‘quv rejalar, fan dasturlari kadrlar buyurtmachilari va iste’molchilari bilan hamkorlikda ishlab chiqilgan. Kadrlar buyurtmachilari va iste’molchilari tomonidan bo‘lajak mutaxassislar bilishi lozim bo‘lgan talablar belgilanib, ushbu talablar o‘quv meyoriy hujjatlarda aks etgan va tasdiqlangan. Talabalar yaxshi ta’lim olishlari uchun universitetda 9 ta o‘quv binosi, o‘quv auditoriyalar soni 272 ta, o‘quv laboratoriyalari 25 ta, kompyuter xonalari 25 tani tashkil etadi. Hozirgi vaqtda o‘quv binolar, auditoriyalar, laboratoriya xonalari barchasi zamonaviy zarur jihozlar ta’minlangan. Universitetda ta’lim sifatini oshirish va o‘quv mashg‘ulotlarni sifatini doimiy nazorat qilish maqsadida barcha auditoriyalar kuzatuv kameralari bilan jihozlangan. Bu kuzatuv kameralari orqali darslar sifati tahlil qilib boriladi. Axborot-resurs markazida foydalanuvchilarga tezkor va sifatli axborot-kutubxona xizmatini ko‘rsatish uchun “IRBIS” avtomatlashtirilgan tizimi yo‘lga qo‘yilgan, dastur orqali kutubxonaga ID karta orqali obuna tashkil qilish, kitob olish va berish jarayonlari elektron shaklda olib boriladi. Talabalar bosma nashrlardan tashqari elektron kitoblar va audio shakldagi adabiyotlardan foydalanish imkoniga ega. Buning uchun universitetning turli hududlarida, bino zallarida, talabalar gavjum joylarda QR-kodli kitoblar doskalari tashkil qilingan.
            </p>
            <h4 id="scrollspyHeading6">AKADEMIK HALOLLIK SIYOSATI</h4>
            <p>O‘quv jarayonida talabalarning akademik yaxlitligining asosiy tamoyillari quyidagilardan iborat:
                <br><br>
                - vijdonlilik - o‘quvchilar tomonidan baholanadigan va baholanmaydigan ishni halol, munosib bajarish;
                <br><br>
                - ilmiy asar muallifi va uning vorislari huquqlariga rioya qilish va himoya qilish, intellektual mulk sohasidagi boshqa huquqlar – asar muallifligini e’tirof etish va himoya qilish, ya’ni smeta asari to‘g‘risidagi ma’lumotlar;
                <br><br>
                - talabalar, professor-o‘qituvchilar va universitet xodimlari o‘rtasida ochiqlik-oshkoralik, o‘zaro ishonch, o‘quv va ilmiy axborot va g‘oyalarning ochiq almashinuvi;
                <br><br>
                - talabalarning huquq va erkinliklarini hurmat qilish - talabalarning o‘z qarashlari va g‘oyalarini erkin ifoda etish huquqi;
                <br><br>
                - tenglik - har bir talaba, professor-o‘qituvchilar va ma‘muriy-boshqaruv xodimlarining akademik halollik qoidalariga rioya qilish burchi va ularni buzganlik uchun javobgarlik.
                <br><br>
                O‘zbekiston respublikasi oliy va o‘rta maxsus ta’lim vazirining, O‘quv adabiyotlarini tayyorlash tartibi hamda o'quv adabiyotlarini yaratishga va ulardan oliy ta‘lim muassasalarida foydalanishga qo‘yiladigan talablarni belgilash to‘g‘risidagi 2022-yil 22-avgustdagi 284-sonli, O‘zbekiston respublikasi oliy ta’lim, fan va innovatsiyalar vazirining, Ilmiy noshirlik faoliyati hamda dissertatsiyalarda ko‘chirmachilikni aniqlash tizimini yanada takomillashtirish to‘g‘risidagi 2024-yil 31-yanvardagi 24-sonli buyrug‘iga asosan universitetda barcha ishlar amalga oshirib kelinmoqda. Universitetda 2 ta ilmiy jurnal, 8 ta Ilmiy kengash faoliyat olib bormoqda. Yuqorida ko‘rsatilgan buyruqlar asosida ishlar tashkil qilingan.
                <br><br>
                Qarshi davlat universiteti moliyaviy mustaqil bo‘lganligi sababli, professor-o‘qituvchilar tomonidan tayyorlangan darslik, o‘quv qo‘llanmalarni nashr etishlaridan oldin maxsus antiplagiat dasturidan o‘tkaziladi va orginallik 60 % dan oshsa nashrga ruxsat etiladi. Ilmiy noshirlik faoliyati hamda magistrlik dissertatsiyalarda ko‘chirmachilikni aniqlash tizimini yanada takomillashtirish maqsadida Polshaning plagiat.pl kompaniyasi bilan hamkorlik qilib, strikeplagiarism.com plagiat dasturidan foydalanilmoqda.
                <br><br>
                Qarshi davlat universiteti jismoniy va yuridik shaxslarning murojaatlari bilan ishlash nazorati va monitoringi bo‘limi o‘z faoliyatini tartibga soluvchi normativ-huquqiy hujjatlarga muvofiq amalga oshiradi.
                <br><br>
                Bo’lim o‘z faoliyatini universitet qoshidagi akademik litsey, fakultetlar, kafedralar va bo‘limlar bilan hamkorlikda olib boradi.
                <br><br>
                Murojaatlarni ko‘rib chiqish uchun universitet rektorining jismoniy va yuridik shaxslarning murojaatlarini ko‘rib chiqadigan doimiy faoliyat bo’lim muayyan vaziyatlarda universitet rektorining ko‘rsatmasi bilan ko‘rib chiqilayotgan masala bo‘yicha vakolatli mansabdor shaxslar orasidan maxsus ishchi guruhlar tuziladi. Ko‘rib chiqish natijalari asosan universitet ma‘muriyatiga murojaat qilgan shaxs nomiga xat shaklida tuziladi. Ayrim hollarda murojaatni ko‘rib chiqish to‘g‘risida dalolatnoma tuziladi.
            </p>
            <br>
            <h4 id="scrollspyHeading7">TA’LIM DASTURLARINI ISHLAB CHIQISH VA TASDIQLASH SIYOSATI</h4>
            <p>Oʻzbekiston Respublikasi Prezidentining 2022-yil 15-iyundagi “Davlat oliy ta’lim muassasalariga o‘qishga qabul qilish jarayonlarini tashkil etish to‘g‘risida” PQ-279-son qarorida belgilangan vazifalar ijrosi yuzasidan Qarshi davlat universiteti karyera markazi xodimlari davlat va nodavlat tashkilot va korxonalardan kelib tushgan ehtiyojlaridan kelib chiqqan holda universitet qabuliga oid takliflar va kadrlarga boʻlgan ehtiyojlar toʻgʻrisidagi ma’lumotlarni Oliy ta’lim, fan va innovatsiyalar vazirligiga taqdim etib boradi.</p>
            <h4 id="scrollspyHeading8">TA’LIM JARAYONINI REJALASHTIRISH VA TASHKIL ETISH SIYOSATI</h4>
            <p>8.1 O‘quv jarayonini rejalashtirish va tashkil etishning umumiy qoidalari:
                <br><br>
                8.1.1 Universitetda o’quv jarayonini tashkil etish, rejalashtirish va amalga oshirish O‘zbekiston Respublikasi oliy va o‘rta maxsus ta’lim vazirining 2021-yil 19-oktabrdagi “Олий таълимнинг давлат таълим стандарти. Asosiy qoidalar” O‘zbekiston Respublikasining davlat standartini tasdiqlash to‘g‘risidagi 35-2021-son buyrug’i, O‘zbekiston Respublikasi Oliy ta’lim, fan va innovatsiyalar vazirining 2023-yil 9-iyundagi 259-son “Oliy ta’limning meyoriy-uslubiy hujjatlarini ishlab chiqish jarayonini takomillashtirish to‘g‘risida” gi buyrug‘i, O‘zbekiston Respublikasi Vazirlar Mahkamasining 2017-yil 20-iyundagi 393-sonli “Oliy ta’lim muassasalari talabalari o‘qishini ko‘chirish, qayta tiklash va o‘qishdan chetlashtirish tartibi to‘g‘risida”gi qarori, 2020-yil 31-dekabrdagi 824-sonli “Oliy ta’lim muassasalarida o‘quv jarayoniga kredit-modul tizimini joriy etish tartibi to‘g‘risida”gi qarori, 2021-yil 3-iyundagi 344-sonli “O‘zbekiston Respublikasi oliy ta’lim muassasalari talabalariga akademik ta’til berish to‘g‘risida”gi qarori, 2018 yil 9-avgustdagi 19-2018-son “Oliy ta’lim muassasalarida talabalar bilimini nazorag qilish va baholash tizimi to‘g‘risida”gi buyrug‘i(3069)ga muvofiq amalga oshiriladi.
                <br><br>
                8.1.2 Universitetda o‘quv yilining davomiyligi Ilmiy kengash qarori bilan tasdiqlangan o‘quv rejalarda belgilanadi. O‘quv taqvimida o‘quv yili davomidagi o‘quv mashg‘ulotlari, oraliq va yakuniy attestatsiyalar, malakaviy amaliyot va boshqa turdagi o‘quv ishlarini o‘tkazish muddatlari, ta’til aks ettiriladi.
                <br><br>
                8.1.3 Har kuni akademik faoliyat va registrator boshqarmasi xodimlari, Ta’lim sifatini nazorat qilish bo’limi mutaxassislari va Yoshlar bilan ishlash bo’limi tyutorlari darslar davomatini tekshiradi. Talaba semestr davomida 74 soat va undan ortiq vaqt davomida darslarga (davomat monitoringi natijalariga ko‘ra) sababsiz qatnashmasa, fakultet dekani talabalar safidan chetlashtirish to‘g‘risida Universitet rektoriga bildirishnoma qiladi, Rektor buyrug’i chiqariladi.
                <br><br>
                8.1.4 Har bir akademik davr talabalarni oraliq attestatsiyadan o‘tkazish davri bilan tugaydi.
                <br><br>
                8.1.5 Talabaning o‘quv yuklamasini aniqlashda o‘quv yili akademik davrlardan iboratligini hisobga olish kerak, ularning shakli semestr – 15 hafta belgilanadi. Universitet tomonidan mustaqil ravishda, oraliq attestatsiya davrlari, stajirovkalar, ta'tillar, yakuniy attestatsiya davri (oxirgi yilda). Oraliq attestatsiya davri imtihon sessiyasi deb ataladi. Qishki va bahorgi imtihon sessiyalari, bahorgi sessiya esa ko‘chirish sessiyasi bo‘lib, uning natijalariga ko‘ra kursdan kursga o‘tkazish to‘g‘risida Rektor buyrug‘i chiqariladi.
                <br><br>
                8.1.6 Ta’til talabalarga o‘quv yili davomida kamida 2 marta beriladi, ularning umumiy davomiyligi kamida 10 hafta bo‘lishi kerak, yakuniy yil bundan mustasno.
                <br><br>
                8.1.7 Malakaviy amaliyot talaba uchun majburiy tarbiyaviy ish turi hisoblanadi. Malakaviy amaliyotning asosiy turlari - o‘quv-tanishuv, pedagogik, ishlab chiqarishdan iborat. O‘quv jarayonini tashkil etishda malakaviy amaliyotni ham akademik davrdan alohida, ham akademik davr bilan parallel ravishda joriy etishga ruxsat beriladi. Oraliq attestatsiya natijalarini jamlashda malakaviy amaliyot natijalari hisobga olinadi. Amaliyotning davomiyligi talabaning hafta davomida amaliyotda ishlagan me‘yoriy vaqti asosida haftalar bilan belgilanadi, 30 soatga teng (5 kunlik ish haftasi bilan kuniga 6 soat).
                <br><br>
                8.1.8 O‘quv ishlari hajmini rejalashtirishda bitta akademik kredit barcha turlar uchun 30 akademik soatga teng deb hisoblanadi. O‘quv ishlarining barcha turlari uchun 1 akademik soat 40 daqiqaga teng. Bir O‘zbekiston akademik kreditining murakkabligi (30 akademik soat) 1 ECTS kreditiga (25-30 akademik soat) to‘g‘ri keladi.
                <br><br>
                8.1.9 Uch tilli ta’lim dasturini amalga oshirishda ta’lim faoliyatini rejalashtirish va tashkil etish uchta tilda: o‘qitish tili, ikkinchi til va ingliz tilida amalga oshiriladi. Ta’lim tilida ikkinchi va ingliz tilida o‘qitiladigan fanlar ulushi mos ravishda 50, 30, 20 ni tashkil qiladi.
                <br><br>
                8.1.10 Kredit-modul ta’lim tizimiga ko‘ra talabalarning mustaqil ishi ikki qismga bo‘linadi: o‘qituvchi rahbarligida bajariladigan mustaqil ish (SROP) va to'liq mustaqil bajariladigan qism (SROS - SRO o’zi). SRL ning butun doirasi talabaning kundalik mustaqil ishlashini talab qiladigan vazifalar bilan tasdiqlanadi. Ta'lim faoliyatining barcha turlari uchun talabaning o'qituvchi va SRO bilan aloqasi o‘rtasidagi vaqt nisbati Universitet tomonidan mustaqil ravishda belgilanadi. Bunda auditoriya ishlarining hajmi har bir fan hajmining kamida 40-50% ni tashkil qiladi.
                <br><br>
                8.1.11 Dual o‘qitish tizimining elementlarini joriy etishda o‘quv faoliyatini rejalashtirish va tashkil etish nazariy tayyorgarlikni ishlab chiqarishdagi amaliy mashg‘ulotlar bilan uyg‘unlashtirish asosida amalga oshiriladi. Bunda fan bo‘yicha o‘quv materialining kamida 30% bevosita ishlab chiqarishda (texnologik jarayon, ijodiy faoliyat jarayoni, moliyaviy-iqtisodiy jarayonlar, psixologik-pedagogik jarayon) o‘zlashtirish zarur.
                <br><br>
                8.1.12 Nazariy tayyorgarlikni rejalashtirish va oraliq attestatsiya kreditlarning yagona hajmida amalga oshiriladi, ya’ni. har bir fan bo‘yicha umumiy kreditlar soni uni o‘rganish, tayyorlash va ushbu fan bo‘yicha oraliq attestatsiya shakllarini o‘z ichiga oladi.
                <br><br>
                8.1.13 O‘qituvchilar tarkibining o‘quv yuklamasini rejalashtirish akademik soatlar bo‘yicha amalga oshiriladi. Shu bilan birga, auditoriya mashg‘ulotlaridagi oʻquv yuklamasi 1 akademik soat 40 daqiqaga teng boʻlgan normadan kelib chiqqan holda hisoblanadi.
                <br><br>
                8.1.14 Bir o‘quv yilining to‘liq o‘quv yuki kamida 60 akademik kredit yoki 1800 akademik soatni tashkil qiladi.
                <br><br>
                8.1.15 Bir semestr davomida bajarilgan kreditlar soni har bir fan uchun alohida belgilanadi.
                <br><br>
                8.1.16 Bakalavriat taʼlim yoʻnalishlari boʻyicha oʻqishni yakunlashning asosiy mezoni talabaning butun oʻqish davrida, shu jumladan, talabaning oʻquv faoliyatining barcha turlarida 240 akademik kreditni o’zlashtirishi hisoblanadi.
                <br><br>
                8.1.17 Texnikum va kasb-hunar taʼlimi negizida yoki oʻrta yoki oliy taʼlimdan soʻng qisqartirilgan oʻqish muddati bilan taʼlim dasturlari talabasi:
                <br>
                1) oʻzining shaxsiy o‘quv dasturini erishilgan ta‘lim natijalariga, oldingi ta’lim darajasida oʻzlashtirilgan shartlarga qarab shakllantiradi, ular majburiy ravishda qayta oʻqiladi va transkriptga kiritiladi;
                <br>
                2) amaldagi ta’lim dasturi asosida Universitet tomonidan mustaqil ravishda belgilanadigan individual o‘qish muddatlari va ta’lim dasturi hajmiga ega.
                <br><br>
                8.1.18 Magistratura dasturlarida o‘qishni yakunlashning asosiy mezoni talabaning quyidagilarni o‘zlashtirishi hisoblanadi:
                <br>
                1) ilmiy-pedagogik magistraturada butun ta’lim davri uchun kamida 120 akademik kredit, shu jumladan magistrantning o‘quv va ilmiy faoliyatining barcha turlari;
                <br>
                2) ixtisoslashtirilgan magistratura yo‘nalishida o‘qish muddati 1 yil bo‘lgan 60 akademik kredit.
                <br><br>
                8.1.19 Oʻquv jadvali akademik davr uchun tuziladi va akademik davr boshlanishidan kamida 10 kun oldin joylashtiriladi. O‘quv mashg‘ulotlari ikki smenada olib boriladi. Mashg‘ulotlarning boshlanish va tugash vaqti 8:30 dan 22:00 gacha belgilangan. Har bir akademik soatdan keyin 10 daqiqalik tanaffus beriladi. Navbatli jadvalga ko‘ra, talabalar uchun tushlik tanaffusi 30 daqiqa davom etadi.
                <br><br>
                8.1.20 Talabalarning ta'lim tartibining asosiy qoidalari “Ichki qoidalar”da belgilangan.<br>
            </p>

            <h4 id="scrollspyHeading9">TALABALARNING BAHOLASH SIYOSATI</h4>
            <p>9.1 Universitetda talabalar bilimini nazorat qilish O‘zbekiston Respublikasi Oliy ta’lim, fan va innovatsiyalar vazirligining 2018 yil 9 avgustdagi 19-2018-son buyrug‘i bilan tasdiqlangan va Adliya vazirligida 2018 yil 26 sentyabrdagi 3069-son raqam bilan davlat ro‘yxatidan o‘tkazilgan “Oliy taʼlim muassasalari talabalari bilimini nazorat qilish va baholashning reyting tizimi to‘g‘risidagi nizom”ga va 2020 yil 31 dekabrdagi O‘zbekiston Respublikasi Vazirlar Mahkamasining “Oliy taʼlim muassasalarida taʼlim jarayonini tashkil etish bilan bog‘liq tizimni takomillashtirish chora-tadbirlari to‘g‘risida” 824-sonli qaroriga muvofiq tartibga solinadi hamda Oliy va o‘rta maxsus taʼlim vazirligining 2018 yil 7 noyabrdagi 26-2018-son buyrug‘i bilan tasdiqlangan “O‘zbekiston Respublikasi oliy taʼlim muassasalari bitiruvchilarining yakuniy davlat attestatsiyasi to‘g‘risidagi nizom”ga asosan bitiruvchilarning yakuniy attestatsiyasi bilan tugallanadi. Baholash tizimidagi baholash bosqichlarini o‘tkazish muddatlari universitetning 2023-2024 o‘quv yili uchun o‘quv jarayoni jadvaliga kiritilgan. Baholash natijalarini tahlil qilishni soddalashtirish va kompyuterlashtirish hamda davomat va o‘quv samaradorligini oshirish ishlari olib borilmoqda. Mazkur masala yuzasidan tushuntirish ishlari olib borilib, har bir talabaga “Talabalar bilimini baholash tizimi bo‘yicha” (o‘zbek, rus va ingliz tillarida) maʼlumot berildi.
                <br><br>
                9.2 “Oraliq nazorat” yozma ish, og‘zaki, kompyuter nazorati, yozma nazorat shaklida o‘tkazilib kelinadi. Oraliq nazoratlari o’quv yilining ma’lum muddatida HEMIS platformasida tashkil etiladi va talabalar HEMIS platformasida to’g’ridan to’g’ri baholanadi. Kafedra reytingi natijalari to‘planib, natijalar kafedra yigilishida muhokama qilindi. Kafedralarni baholash natijalari keyinchalik dekanatlarda tahlil qilinadi.
                <br><br>
                9.3 Fakultetlarda dekan, dekan muovinlari va kafedra mudirlaridan iborat yakuniy baholashni o‘tkazish va nazorat qilish bo‘yicha komissiya tarkibi tuziladi va tegishli jadvallar tasdiqlangan holda jarayonga yo’naltiriladi. Yakuniy nazoratlar kuzatuv kameralari orqali kuzatilib, shaffof va adolatli o’tishi uchun appellatsiya komissiyalari tuziladi. Natijalar fakultet ilmiy kengashida va umumlashtirgan holda universitet Kengashlarida muntazam ravishda tahlil qilinadi. O‘qituvchilarning baholash tizimini tashkil etish, o‘tkazish va baholash tartibi kafedra mudiri tomonidan nazorat qilinadi. Fakultet miqyosida esa fakultet dekani va uning o‘rinbosarlari rahbarligida muntazam ravishda o‘tkaziladi.
                <br><br>
                9.4 Universitetning Akademik faoliyat va registrator boshqarmasi va Ta’lim sifati nazorati bo’limi yo’nalishning talabalari bilimini baholash tizimini to‘liq nizom talablariga muvofiq o‘tkazilishi muntazam o‘rganilib nazorat qilib boriladi. Avvalo, baholashni o‘z vaqtida va sifatli o‘tkazish maqsadida ularni amalga oshirish muddatlari yo’nalishning o‘quv jarayoni jadvaliga kiritiladi. Yo’nalish talabalarining natijalari haqida Universitetning mahalliy tarmog‘i orqali to‘plangan baholash turlari bo‘yicha maʼlumotlar taʼlim boshqarmasi tomonidan umumlashtirilib, natijalar fakultet va universitet kengashi tomonidan tahlil qilinib, malaka oshirish chora-tadbirlari ishlab chiqiladi.
            </p>
            <br>
            <h4 id="scrollspyHeading10">TALABALARNI O’QISHINI KO’CHIRISH, TIKLASH, CHETLASHTIRISH VA AKADEMIK TA’TIL BERISH SIYOSATI</h4>
            <p>1. Talabalarni o’qishini ko’chirish va tiklash
                <br><br>
                Oliy ta’lim muassasalari talabalari o‘qishini ko‘chirish va qayta tiklashda (keyingi o‘rinlarda talabalar o‘qishini ko‘chirish va qayta tiklash deb ataladi) ta’lim yo‘nalishlarining (mutaxassisliklarining) nomlanishi bir xil bo‘lganda bunday ta’lim yo‘nalishlari (mutaxassisliklari) mos deb hisoblanadi.
                <br><br>
                Oliy ta’limning turdosh ta’lim yo‘nalishlari ro‘yxati O‘zbekiston Respublikasi Oliy va o‘rta maxsus ta’lim vazirligi tomonidan ishlab chiqiladi va O‘zbekiston Respublikasi ta’lim muassasalariga o‘qishga qabul qilish bo‘yicha Davlat komissiyasi tomonidan tasdiqlanadi.
                <br><br>
                Talabalar o‘qishini ko‘chirish yoki qayta tiklash:
                <br><br>
                Oliy ta’lim muassasalari;
                <br><br>
                Tizimida oliy ta’lim muassasalari bo‘lgan vazirliklar va idoralar;
                <br><br>
                O‘zbekiston Respublikasi ta’lim muassasalariga qabul qilish bo‘yicha Davlat komissiyasi (keyingi o‘rinlarda Davlat komissiyasi deb ataladi) qarorlari asosida amalga oshiriladi. Quyidagi hollarda talabalar o‘qishini ko‘chirish va qayta tiklash talabani qabul qilayotgan oliy ta’lim muassasasi bo‘ysunadigan vazirlik (idora) qarori asosida amalga oshiriladi:
                <br><br>
                Oliy ta’limning mos va turdosh yo‘nalishlari hamda mos mutaxassisliklari bo‘yicha bir vazirlik (idora) doirasida boshqa oliy ta’lim muassasasiga;
                <br><br>
                oliy ta’limning mos va turdosh yo‘nalishlari hamda mos mutaxassisliklari bo‘yicha turli vazirlik (idora) doirasida bir oliy ta’lim muassasasidan boshqa oliy ta’lim muassasasiga.
                <br><br>
                Quyidagi hollarda talabalar o‘qishini ko‘chirish va qayta tiklash Davlat komissiyasi qarori bilan amalga oshiriladi:
                <br><br>
                Xorijiy davlatlarning akkreditatsiyaga ega bo‘lgan oliy ta’lim muassasalaridan O‘zbekiston Respublikasi oliy ta’lim muassasalariga;
                <br><br>
                bir oliy ta’lim muassasasi doirasida yoki bir vazirlik yoki idora yoxud turli vazirliklar va idoralar tizimidagi oliy ta’lim muassasalariga oliy ta’limning turdosh bo‘lmagan ta’lim yo‘nalishlariga va istisno tariqasida mos bo‘lmagan mutaxassisliklariga.
                <br><br>
                2. Talaba oliy ta’lim muassasasidan quyidagi hollarda chetlashtirilishi mumkin:
                <br><br>
                a) o‘z xohishiga binoan;
                <br><br>
                b) o‘qishning boshqa ta’lim muassasasiga ko‘chirilishi munosabati bilan;
                <br><br>
                g) o‘quv intizomini va oliy ta’lim muassasasining ichki tartib-qoidalari hamda odob-axloq qoidalarini buzganligi uchun;
                <br><br>
                d) bir semestr davomida darslarni uzrli sabablarsiz 74 soatdan ortiq qoldirganligi sababli;
                <br><br>
                e) o‘qish uchun belgilangan to‘lov o‘z vaqtida amalga oshirilmaganligi sababli (to‘lov-kontrakt bo‘yicha tahsil olayotganlar uchun);
                <br><br>
                j) talaba sud tomonidan ozodlikdan mahrum etilganligi munosabati bilan;
                <br><br>
                z) sud qaroriga ko‘ra kirish imtihonlarida belgilangan tartibni buzganligi aniqlanganda (ushbu holatda talabalar safidan chetlashtirilganlar talabalar safiga qayta tiklanmaydi);
                <br><br>
                i) vafot etganligi sababli.
                <br><br>
                Harbiy xizmatni o‘tash, salomatligini tiklash, homiladorlik va tug‘ish, shuningdek, bolalarni parvarish qilish ta’tillari davrida hamda oilasining betob a’zosini (otasi, onasi yoki ularning o‘rnini bosuvchi shaxslar, turmush o‘rtog‘i, farzandi) parvarish qilish uchun talabaga Vazirlar Mahkamasi tomonidan belgilangan tartibda akademik ta’til berilishi mumkin.
                <br><br>
                3. Akademik ta’til berish
                <br><br>
                1. Mazkur Nizom O‘zbekiston Respublikasi oliy ta’lim muassasalari talabalariga akademik ta’til berish va akademik ta’tildan so‘ng ularning o‘qishini davom ettirish tartibini belgilaydi.
                <br><br>
                2. O‘zbekiston Respublikasi oliy ta’lim muassasalari talabalariga akademik ta’til quyidagi hollarda berilishi mumkin:
                <br><br>
                harbiy xizmatni o‘tash uchun;
                <br><br>
                salomatligini tiklash uchun;
                <br><br>
                homiladorlik va tug‘ish uchun;
                <br><br>
                bolalarni parvarish qilish uchun;
                <br><br>
                oilasining betob a’zosini (otasi, onasi yoki ularning o‘rnini bosuvchi shaxslar, turmush o‘rtog‘i, farzandi) parvarish qilish uchun.
                <br><br>
                3. Akademik ta’til o‘quv yilining har qanday qismida, quyidagi muddatlarga beriladi:
                <br><br>
                harbiy xizmatni o‘tash uchun - qonunchilik hujjatlarida belgilangan harbiy xizmatni o‘tash muddatiga;
                <br><br>
                homiladorlik va tug‘ish, shuningdek, bolalarni parvarish qilish uchun - qonunchilik hujjatlarida belgilangan homiladorlik va tug‘ish, shuningdek, bolalarni parvarish qilish ta’tillari muddatiga;
                <br><br>
                salomatligini tiklash hamda oilasining betob a’zosini parvarish qilish uchun - keyingi yilning talaba tomonidan to‘liq o‘zlashtirilmagan semestri boshlanguniga qadar bo‘lgan muddatga (agar mazkur Nizomning 11-bandiga muvofiq talaba akademik ta’til muddatiga ta’limning sirtqi yoki masofaviy yoki eksternat ta’lim shakliga o‘tkazilsa, navbatdagi semestrning boshlanguniga qadar bo‘lgan muddatga). Bunda, qayd etilgan muddatlardan biri talabaning arizasiga muvofiq oliy ta’lim muassasasi rektori (direktori) tomonidan belgilanadi.
                <br><br>
                4. Talabaga akademik ta’til berilishi uni oliy ta’lim muassasasidan chetlashtirish deb hisoblanmaydi.
                <br><br>
                5. Talabalarga akademik ta’til davrida stipendiyalar to‘lanmaydi.
                <br>
            </p>
            <h4 id="scrollspyHeading11">BITIRUVCHILARNING KASBIY YO‘NALISH, BANDLIK VA KARYERA (MANSAB) O‘SISH SIYOSATI</h4>
            <p>Qarshi davlat universitetida 2023-2024- o‘quv yilida 7740 nafar bitiruvchi bo‘lib talabalarni qo‘llab-quvvatlash, ularning bandligiga ko‘maklashish, bo‘sh vaqtlarini samarali tashkil etish, kadrlar tayyorlash tizimini yangi bosqichga olib chiqish, ularda tadbirkorlik g‘oya va tashabbuslarini rag‘batlantirish, ular munosib daromad olishi uchun qo‘shimcha sharoitlar yaratish, bu borada olib borilayotgan ishlarni yangi bosqichga olib chiqish, Yangi O‘zbekistonning taraqqiyot strategiyasi va uni amalga oshirishga oid davlat dasturi ijrosini ta’minlash maqsadida joriy yilning 15-aprel kuni viloyat kambag‘allikni qisqartirish va bandlik bosh boshqarmasi bilan Qarshi davlat universiteti hamkorlikda “Karyera kuni” tashkil etildi. Tadbirga davlat va nodavlat tashkilot rahbarlari hamda direktorlari universitetga tashrif buyurdi, bitiruchi yoshlarga bo’sh ish o’rinlarini taklif qildi va shartnomalar tuzdi.
                <br><br>
                “Karyera kuni” universitetda doimiy tashkil etilib boriladi.
                <br><br>
                Ushu elektron manzillar oraqali bitiruvchilarni bandligi to’g’risida ma’lumot olish mumkin.
            </p>
            <h4 id="scrollspyHeading12">OCHIQLIK VA OSHKORALIK SIYOSATI</h4>
            <p>Universitetda korrupsiyaga qarshi kurashish komplaens-nazorat tizimini boshqarish bo’limi, jismoniy va yuridik shaxslarning murojaatlari bilan ishlash, nazorat va monitoring bo‘limlari mavjud. Bu bo‘limlar o‘zlarini nizomlari va ish rejalari asosida faoliyat yuritib kelmoqdalar.
                <br><br>
                Qarshi davlat universitetining odob-axloq kodeksi ishlab chiqilgan. Qarshi davlat universiteti Kengashining 2023- yil 6-sentabrdagi 1-sonli yig‘ilish Qarori bilan tasdiqlangan. Odob-axloq kodeksi 10-bob, 35-moddadan iborat.
                <br><br>
                Korrupsiyaga qarshi kurashish komplaens-nazorat tizimini boshqarish bo’limi tomonidan huquqbuzarlik holatlarini huquqiy tartibga solish va rasmiy hujjatlashtirish mexanizmi ishlab chiqildi.
                <br><br>
                Akademik halollik tamoyillarini amalga oshirish mexanizmidagi muhim vosita universitet faoliyatining ochiqligi, qulayligi uchun shart-sharoit yaratish hisoblanadi. Universitet rektorining maxsus Telegram-bot-kanali va korrupsiyaga qarshi kurash bo‘yicha “ishonch telefoni” va @QarDU_Antikor_bot ishga tushirilgan. Bu ochiqlik siyosatining huquqiy asoslari keyinchalik – O‘zbekiston Respublikasi Prezidentining 2021-yil 16-iyundagi 6247-son qarori bilan belgilandi. Shunday qilib, fuqarolarning, birinchi navbatda, universitet talabalari va uning kasb-hunar ta’limi muassasalari (akademik litsey va texnikumlar) o'quvchilarining to'siqsiz aylanishi uchun imkoniyat yaratildi.
            </p>
            <h4 id="scrollspyHeading13">AKADEMIK MOBILLIK VA HALQARO ALOQALAR SIYOSATI</h4>
            <p>Akademik mobillik va xalqaro aloqalar siyosati – bu universitetlar va oliy ta’lim muassasalarining global miqyosda o‘zaro hamkorlik qilish, talaba va o‘qituvchilarning xalqaro tajriba almashinuvi jarayonlarini tashkil etish siyosatini ifodalaydi. Bu siyosat oliy ta’lim sifatini oshirish, ilm-fan sohasida hamkorlikni rivojlantirish, madaniy va ilmiy tajriba almashinuvini kuchaytirishga qaratilgan.
                <br><br>
                Asosiy maqsadlari quyidagilardan iborat:
                <br><br>
                Talabalar mobilligi: Talabalar xalqaro universitetlar bilan almashinuv dasturlari orqali qisqa yoki uzoq muddatli o‘qish imkoniyatiga ega bo‘ladilar. Bu Erasmus+, DAAD, NSP, KOICA, Fulbright kabi xalqaro dasturlar orqali amalga oshiriladi.
                <br><br>
                O‘qituvchilar va tadqiqotchilar mobilligi: Xalqaro ilmiy anjumanlar, seminarlarda qatnashish, xorijiy universitetlarda qisqa muddatli dars berish yoki tadqiqot o‘tkazish imkoniyatlarini yaratish.
                <br><br>
                Hamkorlik dasturlari: Xalqaro universitetlar bilan qo‘shma ilmiy tadqiqotlar olib borish, qo‘shma ilmiy dasturlar yaratish va ilmiy loyihalar ustida ishlash.
                <br><br>
                Ilmiy-tadqiqot almashinuvi: Universitetlar o‘z tadqiqotchilari va professor-o‘qituvchilari orqali xalqaro hamkorlikda ilmiy loyihalar olib boradi va tajriba almashadi.
                <br><br>
                Madaniy almashinuv: Talaba va o‘qituvchilarga xorijiy davlatlarda ta’lim olish jarayonida boshqa xalqlarning madaniyati bilan tanishish, o‘z xalqining madaniyatini tanitish imkoniyati beriladi.
                <br><br>
                Akademik mobillik va xalqaro aloqalar siyosati ta’lim sifatini yaxshilash, universitetlararo ilmiy hamkorlikni kengaytirish va global darajada kadrlar tayyorlashni qo‘llab-quvvatlaydi.
                <br><br>
                Qarshi davlat universiteti talabalari va o‘qituvchilari akademik va kredit mobilligi doirasida Ispaniyaning Xaen universiteti (Jaen University), Valladolid universiteti (Valladolid University), Slovakiyaning Jilina universiteti (Zilina University), Konstantin faylasuf universiteti (Constantin the Philosopher University), Bratislava shahridagi Slovak texnologiya universiteti (Slovak University of Technology in Bratislava), Matej Bel universitet (Matej Bel University in Banská Bystrica)lari bilan hamkorlik doirasida mobillik almashinuvlarini amalga oshirgan
            </p>
            <h4 id="scrollspyHeading14">DUAL TA’LIM ELEMENTLARINI AMALGA OSHIRISH SIYOSATI</h4>
            <p>2024-2025 oʻquv yilidan boshlab Turizm (faoliyat yoʻnaishlari boʻyicha), Mehmonxona xoʻjaligini tashkil etish va boshqarish hamda Muqobil energetika yoʻnalishilari talabalarini Dual ta’lim asosida o‘qishini tashkil etish amalga oshirilmoqda. Qashqadaryo viloyatidagi shahar va tuman hududlarida joylashgan Mehmonxonalar, mehmon uylari, umumiy ovqatlantirish korxonalari va boshqa sohaga taalluqli xizmat koʻrsatish korxonalari bilan shartnomalar imzolanmoqda.
                <br><br>
                2024-2025 oʻquv yili davomida talabalarni Dual ta’lim asosida haftaning 3 kuni ta’lim muassasasida nazariy bilimlarni egallash, 2 kuni esa xizmat koʻrsatish korxona va tashkilotlarida haq toʻlanadigan amaliy ko‘nikmalarni egallashi nazarda tutilgan.
                <br><br>
                Dual o‘qitish tizimining elementlarini joriy etishda o‘quv faoliyatini rejalashtirish va tashkil etish nazariy tayyorgarlikni ishlab chiqarishdagi amaliy mashg'ulotlar bilan uyg'unlashtirish asosida amalga oshiriladi. Bunda fan bo‘yicha o‘quv materialining kamida 30% bevosita ishlab chiqarishda (texnologik jarayon, ijodiy faoliyat jarayoni, moliyaviy-iqtisodiy jarayonlar, psixologik-pedagogik jarayon) o‘zlashtirish zarur.
            </p>
            <h4 id="scrollspyHeading15">PROFESSOR- O’QITUVCHILARNING MALAKA OSHIRISH SIYOSATI</h4>
            <p>Oliy ta’lim muassasalari rahbar va pedagog kadrlarining uzluksiz malakasini oshirish jarayonlarini tashkil etish tartibi to‘g‘risida
                <br><br>
                O‘zbekiston Respublikasi Vazirlar Mahkamasining 2024 yil 11 iyuldagi 415-sonli qarori tahririda-Qonunchilik ma’lumotlari milliy bazasi, 12.07.2024 y 09/24/415/0501-son)
                <br><br>
                Qayta tayyorlash va malaka oshirish kurslaridan o‘tish tartibi.
                <br><br>
                O‘qish tugallanadigan yilda oliy ta’lim muassasalariga pedagog lavozimlariga qabul qilingan magistratura bitiruvchilari pedagogik faoliyatning uchinchi yilida qayta tayyorlash va malaka oshirish kurslaridan o‘tadilar.
                <br><br>
                Davlat oliy ta’lim muassasalarining rahbar va pedagog kadrlari, idoraviy mansubligidan qat’iy nazar, uch yilda kamida bir marta doimiy asosda qayta tayyorlash va malaka oshirishning tegishli yo‘nalishlari bo‘yicha qayta tayyorlanishi va malakasini oshirishi shart.
                <br><br>
                Qayta tayyorlash va malaka oshirish kurslariga masofadan o‘qitish shakllari.
                <br><br>
                Oliy ta’lim muassasalari rahbar va pedagog kadrlari tarkibidagi uch yoshga to‘lmagan yosh bolasi bo‘lgan ayollar, pensiya yoshidagilar hamda nogironligi bo‘lgan shaxslar oliy ta’lim muassasasi Kengashining tavsiyasiga ko‘ra, O‘zbekiston Respublikasi Oliy ta’lim, fan va innovatsiyalar vazirligi bilan kelishgan holda, masofadan o‘qitish shakllari orqali amalga oshiriladigan malaka oshirish kurslarining reja-jadvaliga kiritiladi.
                <br><br>
                Muqobil malaka oshirish shakllari quyidagilarni o‘z ichiga oladi.
                <br><br>
                O‘zbekiston Fanlar akademiyasi, O‘zbekiston Badiiy akademiyasi akademigi ilmiy unvoniga ega bo‘lish, so‘nggi 3 yilda xorijiy mamlakatlarda to‘g‘ridan-to‘g‘ri shaklda umumiy davomiyligi 4 hafta yoki 144 soatdan kam bo‘lmagan malaka oshirish yoki stajirovka o‘tash, so‘nggi 3 yilda falsafa doktori (PhD) yoki mutaxassislik bo‘yicha fan doktori (DSc) darajasini olish uchun dissertatsiya himoya qilish.
                <br><br>
                Qayta tayyorlash va malaka oshirish kurslarida o‘qish yakunlari bo‘yicha attestatsiyadan o‘tmaganlar.
                <br><br>
                Qayta tayyorlash va malaka oshirish kurslarida o‘qish yakunlari bo‘yicha attestatsiyadan o‘tmagan oliy ta’lim muassasalarining rahbar va pedagog kadrlari bir yil mobaynida pulli asosda o‘qishning tegishli yoki turdosh yo‘nalishi (mutaxassisligi) bo‘yicha keyingi qayta tayyorlash va malaka oshirish kursi yakunida attestatsiyadan o‘tishi shart.
                <br><br>
                Oliy ta’lim muassasalari rahbar va pedagog kadrlarini qayta tayyorlash va malaka oshirish kurslarini takroran o‘zlashtirishi asosiy ishdan ajralmagan holda kurslarning o‘quv dasturlarini mustaqil ravishda masofadan o‘qitish usullari orqali o‘zlashtirish asosida amalga oshiriladi.
                <br><br>
                Qayta tayyorlash va malaka oshirish kurslarida o‘qish yakunlari bo‘yicha attestatsiyadan takroran o‘tmagan yoki asosiy ish joyi bo‘yicha belgilangan muddatda qayta tayyorlash va malaka oshirish kurslaridan takroran o‘tmagan taqdirda, ushbu xodimlar bilan tuzilgan mehnat shartnomasining amal qilish muddatidan hamda bo‘sh turgan pedagog lavozimini egallash tanlovi davrining tugagan muddatidan qat’iy nazar, mehnat shartnomasi belgilangan tartibda ikki oy mobaynida bekor qilinadi.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

<?php require "Includes/footer.php"; ?>