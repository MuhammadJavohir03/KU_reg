<?php
require "database.php";

// 1. Qidiruv uchun barcha talabalar ismlarini olish (lekin natijalarni emas)
$studentsStmt = $pdo->query("SELECT DISTINCT user_id, guruh FROM talabalar ORDER BY user_id ASC");
$students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Tanlangan talaba bormi?
$selected_user = $_GET['user_id'] ?? null;
$matrix = [];
$fanlar = [];

if ($selected_user) {
    // Tanlangan talaba uchun barcha fanlar va natijalarni olish
    $stmt = $pdo->prepare("
        SELECT t.*, f.nomi as fan_nomi 
        FROM talabalar t
        LEFT JOIN fanlar f ON t.fan_id = f.id
        WHERE t.user_id = ?
    ");
    $stmt->execute([$selected_user]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ma'lumotlarni Matrix ko'rinishiga o'tkazish
    foreach ($results as $res) {
        $fanlar[$res['fan_id']] = $res['fan_nomi'];
        $matrix[$res['user_id']]['results'][$res['fan_id']] = [
            'r' => $res['reyting'],
            'u' => $res['umumiy'],
            'd' => $res['davomat']
        ];
    }
}
?>

<?php require "Includes/header.php"; ?>

<body class="bg-[#F1F5F9] min-h-screen font-sans">
    <?php require "Includes/navbar.php"; ?>

    <div class="max-w-7xl mx-auto p-6">
        
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 mb-8 flex flex-col md:flex-row items-center gap-4">
            <div class="flex-1 relative w-full">
                <span class="absolute left-4 top-3.5 text-slate-400">🔍</span>
                <input type="text" id="mainSearch" placeholder="Talaba ismini yozing (masalan: ABDUFATTOYEVA...)" 
                       class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all text-sm">
                
                <div id="searchResults" class="absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 max-h-60 overflow-y-auto hidden custom-scrollbar">
                    <?php foreach ($students as $s): ?>
                        <a href="?user_id=<?= urlencode($s['user_id']) ?>" class="block px-5 py-3 hover:bg-blue-50 text-sm text-slate-700 border-b border-slate-50 last:border-0 transition-colors">
                            <span class="font-bold"><?= htmlspecialchars($s['user_id']) ?></span>
                            <span class="text-[10px] text-slate-400 ml-2 uppercase tracking-tighter"><?= htmlspecialchars($s['guruh']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if ($selected_user): ?>
                <a href="talabalar_bahosi.php" class="px-6 py-3 bg-slate-100 text-slate-500 rounded-2xl text-sm font-bold hover:bg-red-50 hover:text-red-600 transition-all">Tozalash ✕</a>
            <?php endif; ?>
        </div>

        <?php if ($selected_user && !empty($matrix)): ?>
            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-300">
                <div class="p-8 border-b border-slate-50 bg-white">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase italic"><?= htmlspecialchars($selected_user) ?></h2>
                    <p class="text-blue-600 font-bold text-xs mt-1 uppercase tracking-widest">Natijalar jadvali • <?= htmlspecialchars($results[0]['guruh']) ?></p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-blue-600 text-white">
                                <th class="p-5 text-left border-r border-blue-500 min-w-[200px]">FANLAR</th>
                                <?php foreach ($fanlar as $f_id => $f_nomi): ?>
                                    <th colspan="3" class="p-3 text-center border-r border-blue-500 text-[10px] uppercase font-black tracking-tighter bg-blue-700">
                                        <?= htmlspecialchars($f_nomi) ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                            <tr class="bg-blue-500 text-white text-[10px] font-bold">
                                <th class="p-2 border-r border-blue-400">Holat</th>
                                <?php foreach ($fanlar as $f): ?>
                                    <th class="p-2 border-r border-blue-400 text-center w-12">R</th>
                                    <th class="p-2 border-r border-blue-400 text-center w-12">U</th>
                                    <th class="p-2 border-r border-blue-400 text-center w-12">D</th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="hover:bg-slate-50">
                                <td class="p-5 font-black text-slate-400 text-[10px] border-r border-slate-100 bg-slate-50 italic">BALLAR / %</td>
                                <?php foreach ($fanlar as $f_id => $f_nomi): 
                                    $res = $matrix[$selected_user]['results'][$f_id] ?? null;
                                    $r = $res ? $res['r'] : '-';
                                    $u = $res ? $res['u'] : '-';
                                    $d = $res ? $res['d'] : '-';
                                ?>
                                    <td class="p-4 text-center border-r border-slate-100 font-bold <?= ($r != '-' && $r < 20) ? 'bg-red-50 text-red-500' : 'text-slate-600' ?>"><?= $r ?></td>
                                    <td class="p-4 text-center border-r border-slate-100 font-black <?= ($u != '-' && $u < 60) ? 'bg-red-500 text-white' : 'bg-emerald-500 text-white' ?>"><?= $u ?></td>
                                    <td class="p-4 text-center border-r border-slate-100 font-bold text-slate-400 <?= ($d != '-' && $d > 25) ? 'text-orange-500' : '' ?>"><?= $d ?>%</td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-between">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest italic">R: Reyting | U: Umumiy | D: Davomat</p>
                    <button onclick="window.print()" class="text-xs font-black text-blue-600 hover:underline uppercase">Hisobotni yuklash</button>
                </div>
            </div>

        <?php else: ?>
            <div class="mt-20 text-center">
                <div class="inline-block p-10 bg-white rounded-[3rem] shadow-sm border border-slate-200 mb-6">
                    <span class="text-7xl opacity-20">🔎</span>
                </div>
                <h3 class="text-2xl font-black text-slate-800 tracking-tighter">TIZIM TAYYOR</h3>
                <p class="text-slate-400 text-sm mt-2">Natijalarni ko'rish uchun yuqoridagi qidiruv maydoniga talaba ismini yozing.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const mainSearch = document.getElementById('mainSearch');
        const searchResults = document.getElementById('searchResults');
        const items = searchResults.querySelectorAll('a');

        mainSearch.addEventListener('focus', () => {
            if (mainSearch.value.length > 0) searchResults.classList.remove('hidden');
        });

        mainSearch.addEventListener('input', (e) => {
            let val = e.target.value.toLowerCase();
            if (val.length > 0) {
                searchResults.classList.remove('hidden');
                items.forEach(item => {
                    let text = item.textContent.toLowerCase();
                    item.style.display = text.includes(val) ? 'block' : 'none';
                });
            } else {
                searchResults.classList.add('hidden');
            }
        });

        // Tashqariga bosilganda yopish
        document.addEventListener('click', (e) => {
            if (!mainSearch.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
        @media print { .no-print, input, .bg-slate-100 { display: none !important; } }
    </style>
</body>