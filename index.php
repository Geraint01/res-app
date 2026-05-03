<?php
require_once 'database.php';
$db = initDB();

// Get all categories
$categories = [];
$catResult = $db->query("SELECT * FROM categories");
while ($row = $catResult->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row;
}

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$catFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$diffFilter = isset($_GET['difficulty']) ? trim($_GET['difficulty']) : '';

// Build query
$sql = "SELECT r.*, c.name as category_name, c.color as category_color FROM recipes r LEFT JOIN categories c ON r.category_id = c.id WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (r.title LIKE :search OR r.description LIKE :search OR r.tags LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if ($catFilter) {
    $sql .= " AND r.category_id = :cat";
    $params[':cat'] = $catFilter;
}
if ($diffFilter) {
    $sql .= " AND r.difficulty = :diff";
    $params[':diff'] = $diffFilter;
}
$sql .= " ORDER BY r.created_at DESC";

$stmt = $db->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();

$recipes = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $recipes[] = $row;
}

// Stats
$totalRecipes = $db->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
$totalCats = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();

$diffColors = ['Mudah' => 'bg-emerald-100 text-emerald-700', 'Sedang' => 'bg-amber-100 text-amber-700', 'Sulit' => 'bg-rose-100 text-rose-700'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DapurKita - Resep Masakan Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FF6B35;
            --secondary: #FF4D6D;
            --accent: #FFD23F;
            --teal: #2EC4B6;
            --bg: #FFFAF5;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
        }

        h1,
        h2,
        .font-display {
            font-family: 'Playfair Display', serif;
        }

        .hero-pattern {
            background-color: #FF6B35;
            background-image: radial-gradient(circle at 20% 50%, #FF4D6D33 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, #FFD23F44 0%, transparent 40%),
                radial-gradient(circle at 60% 80%, #2EC4B633 0%, transparent 40%);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(255, 107, 53, 0.15);
        }

        .emoji-bg {
            background: linear-gradient(135deg, #FFF3E0, #FFE0B2);
            border-radius: 20px;
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
        }

        .filter-pill.active {
            background: var(--primary);
            color: white;
        }

        .floating-btn {
            background: linear-gradient(135deg, #FF6B35, #FF4D6D);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
            transition: all 0.3s;
        }

        .floating-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 35px rgba(255, 107, 53, 0.5);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .recipe-card {
            animation: fadeIn 0.4s ease forwards;
        }

        .recipe-card:nth-child(2) {
            animation-delay: 0.05s;
        }

        .recipe-card:nth-child(3) {
            animation-delay: 0.1s;
        }

        .recipe-card:nth-child(4) {
            animation-delay: 0.15s;
        }

        .recipe-card:nth-child(5) {
            animation-delay: 0.2s;
        }

        .recipe-card:nth-child(6) {
            animation-delay: 0.25s;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .scrollbar-thin::-webkit-scrollbar {
            height: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #FF6B35;
            border-radius: 4px;
        }
    </style>
</head>

<body class="min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm sticky top-0 z-50 border-b border-orange-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="index.php" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-sm font-bold" style="background: linear-gradient(135deg, #FF6B35, #FF4D6D);">🍳</div>
                    <span class="text-xl font-bold" style="font-family:'Playfair Display',serif; color:#FF6B35;">DapurKita</span>
                </a>
                <div class="flex items-center gap-3">
                    <a href="add.php" class="floating-btn text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Tambah Resep
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-pattern text-white py-14 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="text-5xl mb-4">🍽️</div>
            <h1 class="text-4xl md:text-5xl font-bold mb-3" style="font-family:'Playfair Display',serif;">
                Temukan Resep Favoritmu
            </h1>
            <p class="text-orange-100 text-lg mb-8">
                Koleksi <?= $totalRecipes ?> resep masakan lezat dari dapur Indonesia
            </p>
            <!-- Search -->
            <form method="GET" class="relative max-w-xl mx-auto">
                <input type="hidden" name="category" value="<?= $catFilter ?>">
                <input type="hidden" name="difficulty" value="<?= htmlspecialchars($diffFilter) ?>">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        placeholder="Cari resep, bahan, atau kategori..."
                        class="search-input w-full pl-12 pr-4 py-4 rounded-2xl text-gray-800 text-base bg-white shadow-lg">
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 bg-orange-500 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-orange-600 transition">
                        Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="bg-white border-b border-orange-100">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center gap-6 overflow-x-auto scrollbar-thin pb-1">
                <!-- All filter -->
                <a href="index.php" class="filter-pill flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition <?= (!$catFilter && !$diffFilter && !$search) ? 'active' : 'bg-orange-50 text-orange-600 hover:bg-orange-100' ?>">
                    <i data-lucide="grid-3x3" class="w-4 h-4"></i>
                    Semua
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="?category=<?= $cat['id'] ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                        class="filter-pill flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition <?= $catFilter == $cat['id'] ? 'active' : 'bg-orange-50 text-orange-600 hover:bg-orange-100' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
                <div class="h-5 w-px bg-orange-200 mx-1"></div>
                <?php foreach (['Mudah', 'Sedang', 'Sulit'] as $d): ?>
                    <a href="?difficulty=<?= $d ?><?= $catFilter ? '&category=' . $catFilter : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                        class="filter-pill flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition <?= $diffFilter == $d ? 'active' : 'bg-orange-50 text-orange-600 hover:bg-orange-100' ?>">
                        <?= $d ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-10">
        <!-- Result info -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    <?php if ($search || $catFilter || $diffFilter): ?>
                        <?= count($recipes) ?> Resep Ditemukan
                    <?php else: ?>
                        Resep Terbaru
                    <?php endif; ?>
                </h2>
                <?php if ($search): ?>
                    <p class="text-sm text-gray-500 mt-1">Hasil pencarian untuk "<strong><?= htmlspecialchars($search) ?></strong>"</p>
                <?php endif; ?>
            </div>
            <?php if ($search || $catFilter || $diffFilter): ?>
                <a href="index.php" class="text-sm text-orange-500 hover:text-orange-600 flex items-center gap-1 font-medium">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    Reset Filter
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($recipes)): ?>
            <!-- Empty state -->
            <div class="text-center py-20">
                <div class="text-6xl mb-4">🍽️</div>
                <h3 class="text-xl font-bold text-gray-700 mb-2">Resep Tidak Ditemukan</h3>
                <p class="text-gray-500 mb-6">Coba kata kunci lain atau tambahkan resep baru</p>
                <a href="add.php" class="floating-btn text-white px-6 py-3 rounded-xl font-semibold inline-flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah Resep Baru
                </a>
            </div>
        <?php else: ?>
            <!-- Recipe Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                <?php foreach ($recipes as $recipe): ?>
                    <a href="detail.php?id=<?= $recipe['id'] ?>" class="recipe-card card-hover bg-white rounded-2xl overflow-hidden shadow-sm border border-orange-100 block">
                        <!-- Image area -->
                        <div class="emoji-bg h-40 flex items-center justify-center relative">
                            <span class="text-6xl"><?= $recipe['image_emoji'] ?></span>
                            <?php if ($recipe['category_name']): ?>
                                <span class="absolute top-3 left-3 text-xs font-semibold px-3 py-1 rounded-full text-white" style="background-color: <?= $recipe['category_color'] ?>88; backdrop-filter: blur(4px);">
                                    <?= htmlspecialchars($recipe['category_name']) ?>
                                </span>
                            <?php endif; ?>
                            <span class="absolute top-3 right-3 text-xs font-semibold px-3 py-1 rounded-full <?= $diffColors[$recipe['difficulty']] ?? 'bg-gray-100 text-gray-600' ?>">
                                <?= htmlspecialchars($recipe['difficulty']) ?>
                            </span>
                        </div>
                        <!-- Card body -->
                        <div class="p-4">
                            <h3 class="font-bold text-gray-900 text-base mb-1 line-clamp-1"><?= htmlspecialchars($recipe['title']) ?></h3>
                            <p class="text-gray-500 text-sm line-clamp-2 mb-3"><?= htmlspecialchars($recipe['description']) ?></p>
                            <!-- Meta -->
                            <div class="flex items-center gap-3 text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-orange-400"></i>
                                    <?= ($recipe['prep_time'] + $recipe['cook_time']) ?> mnt
                                </span>
                                <span class="flex items-center gap-1">
                                    <i data-lucide="users" class="w-3.5 h-3.5 text-orange-400"></i>
                                    <?= $recipe['servings'] ?> porsi
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- FAB -->
    <a href="add.php" class="floating-btn fixed bottom-6 right-6 w-14 h-14 rounded-full flex items-center justify-center text-white z-40 shadow-xl">
        <i data-lucide="plus" class="w-6 h-6"></i>
    </a>

    <!-- Footer -->
    <footer class="bg-white border-t border-orange-100 mt-16 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="text-2xl mb-2">🍳</div>
            <p class="text-gray-500 text-sm">© 2025 DapurKita · Resep Masakan Nusantara</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>