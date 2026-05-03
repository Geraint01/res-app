<?php
function getDB() {
    $db = new SQLite3(__DIR__ . '/resep.db');
    $db->exec("PRAGMA journal_mode=WAL;");
    return $db;
}

function initDB() {
    $db = getDB();
    
    $db->exec("CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        icon TEXT NOT NULL,
        color TEXT NOT NULL
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS recipes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT,
        category_id INTEGER,
        prep_time INTEGER,
        cook_time INTEGER,
        servings INTEGER,
        difficulty TEXT,
        image_emoji TEXT,
        ingredients TEXT,
        steps TEXT,
        tags TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(category_id) REFERENCES categories(id)
    )");

    $count = $db->querySingle("SELECT COUNT(*) FROM categories");
    if ($count == 0) {
        seedData($db);
    }
    
    return $db;
}

function seedData($db) {
    $categories = [
        ['Sarapan', 'Sunrise', '#FF6B35'],
        ['Makan Siang', 'Sun', '#F7C59F'],
        ['Makan Malam', 'Moon', '#6B4EFF'],
        ['Camilan', 'Cookie', '#FF4D6D'],
        ['Minuman', 'Coffee', '#2EC4B6'],
        ['Dessert', 'Cake', '#E63946'],
    ];

    $catStmt = $db->prepare("INSERT INTO categories (name, icon, color) VALUES (:name, :icon, :color)");
    foreach ($categories as $cat) {
        $catStmt->bindValue(':name', $cat[0]);
        $catStmt->bindValue(':icon', $cat[1]);
        $catStmt->bindValue(':color', $cat[2]);
        $catStmt->execute();
    }

    $recipes = [
        [
            'title' => 'Nasi Goreng Spesial',
            'description' => 'Nasi goreng lezat dengan bumbu rempah khas Indonesia yang kaya cita rasa, dilengkapi telur mata sapi dan kerupuk renyah.',
            'category_id' => 2,
            'prep_time' => 10,
            'cook_time' => 15,
            'servings' => 2,
            'difficulty' => 'Mudah',
            'image_emoji' => '🍳',
            'ingredients' => json_encode([
                '2 piring nasi putih (dingin)',
                '2 butir telur',
                '3 siung bawang putih, cincang',
                '5 siung bawang merah, iris',
                '2 cabai merah, iris',
                '2 sdm kecap manis',
                '1 sdm saus tiram',
                'Garam dan merica secukupnya',
                'Minyak goreng',
                'Daun bawang untuk taburan',
            ]),
            'steps' => json_encode([
                'Panaskan minyak, tumis bawang putih dan bawang merah hingga harum keemasan.',
                'Masukkan cabai merah, aduk rata selama 1 menit.',
                'Masukkan telur, orak-arik hingga setengah matang.',
                'Tambahkan nasi, aduk rata dengan api besar.',
                'Tuangkan kecap manis dan saus tiram, aduk hingga merata dan nasi sedikit kering.',
                'Bumbui dengan garam dan merica, koreksi rasa.',
                'Sajikan dengan taburan daun bawang dan kerupuk.',
            ]),
            'tags' => 'nasi,goreng,indonesia,cepat',
        ],
        [
            'title' => 'Ayam Bakar Madu',
            'description' => 'Ayam bakar empuk dengan marinade madu dan rempah pilihan, dimasak hingga karamelisasi sempurna dengan aroma yang menggugah selera.',
            'category_id' => 3,
            'prep_time' => 30,
            'cook_time' => 45,
            'servings' => 4,
            'difficulty' => 'Sedang',
            'image_emoji' => '🍗',
            'ingredients' => json_encode([
                '1 ekor ayam, potong 8 bagian',
                '4 sdm madu',
                '3 sdm kecap manis',
                '2 sdm saus tiram',
                '1 sdm air jeruk nipis',
                '4 siung bawang putih, haluskan',
                '1 sdt jahe, parut',
                '1 sdt kunyit bubuk',
                'Garam dan merica secukupnya',
            ]),
            'steps' => json_encode([
                'Campurkan madu, kecap manis, saus tiram, jeruk nipis, bawang putih, jahe, kunyit, garam, dan merica.',
                'Lumuri ayam dengan bumbu marinasi, diamkan minimal 30 menit di kulkas.',
                'Panggang ayam di atas bara api atau oven 180°C selama 20 menit.',
                'Balik ayam dan olesi lagi dengan sisa bumbu marinasi.',
                'Panggang kembali 15-20 menit hingga matang dan karamelisasi.',
                'Sajikan hangat dengan nasi dan lalapan segar.',
            ]),
            'tags' => 'ayam,bakar,madu,panggang',
        ],
        [
            'title' => 'Soto Ayam Bening',
            'description' => 'Soto ayam dengan kuah bening segar dan rempah pilihan, disajikan dengan soun, telur rebus, dan perasan jeruk nipis.',
            'category_id' => 2,
            'prep_time' => 20,
            'cook_time' => 60,
            'servings' => 6,
            'difficulty' => 'Sedang',
            'image_emoji' => '🍜',
            'ingredients' => json_encode([
                '1 ekor ayam kampung',
                '2 liter air',
                '3 lembar daun salam',
                '2 batang serai, memarkan',
                '3 cm lengkuas, memarkan',
                '5 siung bawang putih',
                '8 siung bawang merah',
                '1 sdt kunyit bubuk',
                'Soun secukupnya, rendam',
                '3 butir telur rebus',
                'Tauge, daun bawang, seledri',
                'Bawang goreng untuk pelengkap',
            ]),
            'steps' => json_encode([
                'Rebus ayam dalam air dingin hingga mendidih, buang buih yang muncul.',
                'Haluskan bawang putih, bawang merah, dan kunyit.',
                'Tumis bumbu halus hingga harum, masukkan ke dalam rebusan ayam.',
                'Tambahkan daun salam, serai, dan lengkuas.',
                'Masak dengan api kecil selama 45 menit hingga ayam empuk.',
                'Angkat ayam, suwir-suwir dagingnya.',
                'Sajikan kuah dengan soun, ayam suwir, tauge, telur, dan taburan bawang goreng.',
            ]),
            'tags' => 'soto,ayam,kuah,bening,tradisional',
        ],
        [
            'title' => 'Pisang Goreng Crispy',
            'description' => 'Pisang goreng renyah dengan balutan tepung crispy yang golden brown sempurna, cocok untuk camilan sore hari bersama keluarga.',
            'category_id' => 4,
            'prep_time' => 10,
            'cook_time' => 20,
            'servings' => 4,
            'difficulty' => 'Mudah',
            'image_emoji' => '🍌',
            'ingredients' => json_encode([
                '6 buah pisang raja, kupas',
                '150 gr tepung terigu',
                '50 gr tepung beras',
                '1 sdt baking powder',
                '1/2 sdt garam',
                '200 ml air dingin',
                'Minyak goreng untuk menggoreng',
            ]),
            'steps' => json_encode([
                'Campurkan tepung terigu, tepung beras, baking powder, dan garam.',
                'Tuang air dingin sedikit demi sedikit sambil diaduk hingga adonan kental.',
                'Belah pisang menjadi dua secara memanjang.',
                'Celupkan pisang ke dalam adonan tepung hingga rata.',
                'Goreng dalam minyak panas dengan api sedang hingga golden brown.',
                'Tiriskan dan sajikan selagi hangat.',
            ]),
            'tags' => 'pisang,goreng,crispy,camilan',
        ],
        [
            'title' => 'Es Teh Manis Susu',
            'description' => 'Minuman segar perpaduan teh hitam pekat dengan susu kental manis dan es batu, kesegaran terbaik untuk hari yang panas.',
            'category_id' => 5,
            'prep_time' => 5,
            'cook_time' => 10,
            'servings' => 2,
            'difficulty' => 'Mudah',
            'image_emoji' => '🧋',
            'ingredients' => json_encode([
                '2 kantong teh hitam',
                '400 ml air mendidih',
                '4 sdm susu kental manis',
                'Es batu secukupnya',
                '2 sdm gula pasir (opsional)',
            ]),
            'steps' => json_encode([
                'Seduh kantong teh dengan air mendidih selama 3-5 menit.',
                'Angkat kantong teh, jangan diperas.',
                'Tambahkan gula jika suka lebih manis, aduk.',
                'Dinginkan sebentar di suhu ruang.',
                'Siapkan gelas dengan es batu.',
                'Tuang teh ke dalam gelas berisi es.',
                'Tambahkan susu kental manis, aduk atau biarkan bergradasi cantik.',
            ]),
            'tags' => 'teh,susu,es,minuman,segar',
        ],
        [
            'title' => 'Bubur Ayam Jakarta',
            'description' => 'Bubur nasi lembut dengan topping ayam suwir berbumbu, cakwe, bawang goreng, dan kuah kaldu gurih khas Jakarta.',
            'category_id' => 1,
            'prep_time' => 15,
            'cook_time' => 50,
            'servings' => 4,
            'difficulty' => 'Sedang',
            'image_emoji' => '🥣',
            'ingredients' => json_encode([
                '200 gr beras, cuci bersih',
                '1.5 liter kaldu ayam',
                '200 gr dada ayam, rebus suwir',
                '3 siung bawang putih, goreng crispy',
                '2 batang daun bawang, iris',
                '2 sdm kecap asin',
                '1 sdt minyak wijen',
                'Jahe 2 cm, memarkan',
                'Cakwe untuk pelengkap',
                'Bawang goreng',
            ]),
            'steps' => json_encode([
                'Didihkan kaldu ayam, masukkan beras dan jahe.',
                'Masak dengan api kecil sambil terus diaduk hingga beras hancur menjadi bubur (40 menit).',
                'Bumbu ayam: tumis bawang putih, masukkan ayam suwir, kecap asin, garam merica.',
                'Koreksi kekentalan bubur, tambah air panas jika terlalu kental.',
                'Tambahkan minyak wijen ke dalam bubur, aduk.',
                'Sajikan bubur dengan topping ayam suwir, cakwe, daun bawang, dan bawang goreng.',
            ]),
            'tags' => 'bubur,ayam,jakarta,sarapan',
        ],
        [
            'title' => 'Klepon Pandan',
            'description' => 'Kue klepon tradisional berwarna hijau dari pandan dengan isian gula merah yang meleleh di mulut dan balutan kelapa parut segar.',
            'category_id' => 6,
            'prep_time' => 20,
            'cook_time' => 15,
            'servings' => 20,
            'difficulty' => 'Sedang',
            'image_emoji' => '🟢',
            'ingredients' => json_encode([
                '200 gr tepung ketan',
                '150 ml air daun pandan (blender pandan + air)',
                '100 gr gula merah, potong kecil',
                '1/4 sdt garam',
                '150 gr kelapa parut, kukus dengan sedikit garam',
            ]),
            'steps' => json_encode([
                'Campur tepung ketan, garam, dan air pandan sedikit demi sedikit hingga adonan bisa dibentuk.',
                'Ambil adonan sebesar kelereng, pipihkan, isi dengan potongan gula merah.',
                'Bulatkan kembali dengan rapat agar gula tidak bocor.',
                'Rebus dalam air mendidih hingga klepon mengapung (sekitar 5 menit).',
                'Angkat, tiriskan sebentar.',
                'Gulingkan dalam kelapa parut kukus selagi masih hangat.',
                'Sajikan segera.',
            ]),
            'tags' => 'klepon,pandan,tradisional,dessert,kue',
        ],
        [
            'title' => 'Rendang Daging Sapi',
            'description' => 'Rendang daging sapi empuk bercita rasa kaya dengan 40+ rempah pilihan, dimasak perlahan hingga kering dan berwarna coklat pekat yang khas.',
            'category_id' => 3,
            'prep_time' => 30,
            'cook_time' => 180,
            'servings' => 8,
            'difficulty' => 'Sulit',
            'image_emoji' => '🥩',
            'ingredients' => json_encode([
                '1 kg daging sapi, potong dadu',
                '800 ml santan kental',
                '400 ml santan encer',
                '5 lembar daun jeruk',
                '3 lembar daun salam',
                '2 batang serai, memarkan',
                '2 cm lengkuas, memarkan',
                'Bumbu halus: 15 cabai merah, 10 bawang merah, 6 bawang putih, 3 cm jahe, 3 cm kunyit, 2 cm lengkuas',
            ]),
            'steps' => json_encode([
                'Haluskan semua bumbu halus hingga lembut.',
                'Campurkan santan kental dan encer dalam wajan besar.',
                'Masukkan bumbu halus, daun jeruk, salam, serai, lengkuas.',
                'Masukkan daging sapi, aduk rata.',
                'Masak dengan api sedang sambil terus diaduk hingga mendidih.',
                'Kecilkan api, masak 2-3 jam sambil sesekali diaduk hingga kuah menyusut dan daging berwarna coklat gelap.',
                'Rendang siap saat minyak keluar dan daging sudah kering berwarna coklat pekat.',
            ]),
            'tags' => 'rendang,daging,padang,tradisional,rempah',
        ],
    ];

    $stmt = $db->prepare("INSERT INTO recipes (title, description, category_id, prep_time, cook_time, servings, difficulty, image_emoji, ingredients, steps, tags) VALUES (:title, :desc, :cat, :prep, :cook, :serv, :diff, :emoji, :ing, :steps, :tags)");
    
    foreach ($recipes as $r) {
        $stmt->bindValue(':title', $r['title']);
        $stmt->bindValue(':desc', $r['description']);
        $stmt->bindValue(':cat', $r['category_id']);
        $stmt->bindValue(':prep', $r['prep_time']);
        $stmt->bindValue(':cook', $r['cook_time']);
        $stmt->bindValue(':serv', $r['servings']);
        $stmt->bindValue(':diff', $r['difficulty']);
        $stmt->bindValue(':emoji', $r['image_emoji']);
        $stmt->bindValue(':ing', $r['ingredients']);
        $stmt->bindValue(':steps', $r['steps']);
        $stmt->bindValue(':tags', $r['tags']);
        $stmt->execute();
    }
}
