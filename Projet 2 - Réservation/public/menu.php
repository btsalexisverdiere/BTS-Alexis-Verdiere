<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Récupérer les menus et leurs plats (exemple avec données statiques)
// À remplacer par des requêtes MySQL dans une vraie application
$menus = [
    [
        'id' => 1,
        'nom' => 'Menu Découverte',
        'description' => 'Un voyage culinaire en 5 plats signature.',
        'prix' => 39.90,
        'image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80',
        'plats' => [
            [
                'nom' => 'Tartare de saumon',
                'description' => 'Saumon frais, avocat, citron et toast de pain noir.',
                'prix' => 12.90,
                'categorie' => 'Entrée',
                'tags' => ['poisson', 'sans gluten'],
                'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80'
            ],
            [
                'nom' => 'Magret de canard',
                'description' => 'Cuisson rosée, sauce aux cerises et purée de patate douce.',
                'prix' => 24.90,
                'categorie' => 'Plat principal',
                'tags' => ['viande'],
                'image' => 'https://images.unsplash.com/photo-1582391123232-6130296f1fcd?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
            ],
            [
                'nom' => 'Tiramisu maison',
                'description' => 'Classique italien revisité avec une touche de café local.',
                'prix' => 8.90,
                'categorie' => 'Dessert',
                'tags' => ['végétarien'],
                'image' => 'https://images.unsplash.com/photo-1506354666786-959d6d497f1a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80'
            ]
        ]
    ],
    [
        'id' => 2,
        'nom' => 'Menu Végétarien',
        'description' => 'Des saveurs fraîches et créatives 100% végétales.',
        'prix' => 34.90,
        'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80',
        'plats' => [
            [
                'nom' => 'Velouté de courgette',
                'description' => 'Courgette, menthe et crème de coco, servi avec des graines de courge.',
                'prix' => 9.90,
                'categorie' => 'Entrée',
                'tags' => ['végétarien', 'végétalien', 'sans gluten'],
                'image' => 'https://plus.unsplash.com/premium_photo-1700162640200-14e383b7f790?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mjl8fFZlbG91dCVDMyVBOSUyMGRlJTIwY291cmdldHRlfGVufDB8fDB8fHww'
            ],
            [
                'nom' => 'Risotto aux champignons',
                'description' => 'Riz Arborio crémeux, champignons sauvages et parmesan.',
                'prix' => 18.90,
                'categorie' => 'Plat principal',
                'tags' => ['végétarien'],
                'image' => 'https://images.unsplash.com/photo-1633964913295-ceb43826e7c9?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8cmlzb3R0byUyMGF1eCUyMGNoYW1waWdub25zfGVufDB8MHwwfHx8MA%3D%3D'
            ],
            [
                'nom' => 'Panna cotta aux fruits rouges',
                'description' => 'Crème vanillée et coulis de fruits rouges.',
                'prix' => 7.90,
                'categorie' => 'Dessert',
                'tags' => ['végétarien', 'sans gluten'],
                'image' => 'https://images.unsplash.com/photo-1559564121-d12c8f29e8cd?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8Q3IlQzMlQThtZSUyMHZhbmlsbCVDMyVBOWUlMjBldCUyMGNvdWxpcyUyMGRlJTIwZnJ1aXRzJTIwcm91Z2V8ZW58MHwwfDB8fHww'
            ]
        ]
    ],
    [
        'id' => 3,
        'nom' => 'Menu du Chef',
        'description' => 'L’expérience ultime avec accords mets-vins.',
        'prix' => 59.90,
        'image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80',
        'plats' => [
            [
                'nom' => 'Carpaccio de bœuf',
                'description' => 'Bœuf vieilli, copeaux de parmesan et huile de truffe.',
                'prix' => 16.90,
                'categorie' => 'Entrée',
                'tags' => ['viande', 'sans gluten'],
                'image' => 'https://images.unsplash.com/photo-1607305387299-a3d9611cd469?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80'
            ],
            [
                'nom' => 'Homard rôti',
                'description' => 'Homard bleu de Bretagne, beurre blanc et légumes de saison.',
                'prix' => 32.90,
                'categorie' => 'Plat principal',
                'tags' => ['poisson'],
                'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80'
            ],
            [
                'nom' => 'Soufflé au chocolat',
                'description' => 'Soufflé chaud au cœur coulant, servi avec une boule de glace vanille.',
                'prix' => 10.90,
                'categorie' => 'Dessert',
                'tags' => ['végétarien'],
                'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80'
            ]
        ]
    ]
];

// Récupérer les tags uniques pour les filtres
$allTags = [];
foreach ($menus as $menu) {
    foreach ($menu['plats'] as $plat) {
        $allTags = array_merge($allTags, $plat['tags']);
    }
}
$allTags = array_unique($allTags);
sort($allTags);

// Filtres appliqués (par défaut : aucun)
$selectedTags = isset($_GET['tags']) ? explode(',', $_GET['tags']) : [];
$selectedMenu = isset($_GET['menu']) ? (int)$_GET['menu'] : null;
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Menus - Le Comptoir des Saveurs</title>
    <meta name="description" content="Découvrez nos menus gastronomiques au Comptoir des Saveurs. Réservez votre table pour une expérience culinaire unique.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'playfair': ['"Playfair Display"', 'serif'],
                        'inter': ['"Inter"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        amber: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    },
                },
            },
        };
    </script>
    <style>
        .menu-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .tag {
            @apply inline-block px-2 py-1 text-xs font-medium rounded-full;
        }
        .tag-viande { @apply bg-red-100 text-red-800; }
        .tag-poisson { @apply bg-blue-100 text-blue-800; }
        .tag-végétarien { @apply bg-green-100 text-green-800; }
        .tag-végétalien { @apply bg-emerald-100 text-emerald-800; }
        .tag-sans-gluten { @apply bg-yellow-100 text-yellow-800; }
        .modal {
            display: none;
            animation: fadeIn 0.3s ease-out;
        }
        .modal.active {
            display: flex;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body class="font-inter text-gray-800 bg-gray-50">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="relative bg-gray-900 text-white py-20">
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2073&q=80')] bg-cover bg-center opacity-30"></div>
        <div class="relative container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-playfair font-bold mb-4">Nos Menus</h1>
            <p class="text-xl max-w-2xl mx-auto mb-8">Découvrez nos créations culinaires, élaborées avec des produits frais et de saison.</p>
            <a href="reservation.php" class="inline-block bg-amber-600 hover:bg-amber-700 text-white px-8 py-3 rounded-full font-medium transition-colors">Réserver une table</a>
        </div>
    </section>

    <!-- Filtres -->
    <section class="container mx-auto px-4 py-12">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div class="flex flex-wrap gap-2">
                <a href="menu.php" class="tag <?= empty($selectedTags) && !$selectedMenu ? 'bg-amber-500 text-white' : 'bg-gray-200' ?>">Tous les plats</a>
                <?php foreach ($allTags as $tag): ?>
                    <a href="?tags=<?= urlencode(implode(',', array_merge($selectedTags, [$tag]))) ?>" class="tag tag-<?= strtolower($tag) ?> <?= in_array($tag, $selectedTags) ? 'ring-2 ring-amber-500' : '' ?>">
                        <?= ucfirst($tag) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Onglets des Menus -->
        <div class="mb-8 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                <li class="mr-2" role="presentation">
                    <a href="?menu=0" class="inline-block p-4 rounded-t-lg border-b-2 <?= !$selectedMenu ? 'border-amber-500 text-amber-600' : 'border-transparent hover:border-gray-300 hover:text-gray-600' ?>">Tous les menus</a>
                </li>
                <?php foreach ($menus as $index => $menu): ?>
                    <li class="mr-2" role="presentation">
                        <a href="?menu=<?= $menu['id'] ?>" class="inline-block p-4 rounded-t-lg border-b-2 <?= $selectedMenu == $menu['id'] ? 'border-amber-500 text-amber-600' : 'border-transparent hover:border-gray-300 hover:text-gray-600' ?>"><?= $menu['nom'] ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Liste des Menus et Plats -->
        <div class="grid grid-cols-1 gap-8">
            <?php if (!$selectedMenu): ?>
                <!-- Afficher tous les menus -->
                <?php foreach ($menus as $menu): ?>
                    <div class="menu-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-all">
                        <div class="md:flex">
                            <div class="md:w-1/3 bg-cover bg-center h-64 md:h-auto" style="background-image: url('<?= $menu['image'] ?>')"></div>
                            <div class="md:w-2/3 p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h2 class="text-2xl font-playfair font-bold text-gray-900"><?= $menu['nom'] ?></h2>
                                        <p class="text-gray-600 mt-1"><?= $menu['description'] ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-bold text-amber-600"><?= number_format($menu['prix'], 2) ?> €</p>
                                        <button onclick="openMenuModal(<?= $menu['id'] ?>)" class="mt-2 text-sm text-amber-600 hover:text-amber-800 font-medium">Voir les plats →</button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach (array_slice($menu['plats'], 0, 3) as $plat): ?>
                                        <div class="flex gap-3 p-3 bg-gray-50 rounded-lg">
                                            <img src="<?= $plat['image'] ?>" alt="<?= $plat['nom'] ?>" class="w-20 h-20 object-cover rounded-md">
                                            <div>
                                                <h3 class="font-medium text-gray-900"><?= $plat['nom'] ?></h3>
                                                <p class="text-sm text-gray-500 mt-1"><?= $plat['prix'] ?> €</p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else:
                // Afficher un menu spécifique
                $menu = null;
                foreach ($menus as $m) {
                    if ($m['id'] == $selectedMenu) {
                        $menu = $m;
                        break;
                    }
                }
                if ($menu): ?>
                    <div class="menu-card bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row justify-between items-start mb-6">
                                <div>
                                    <h2 class="text-3xl font-playfair font-bold text-gray-900 mb-2"><?= $menu['nom'] ?></h2>
                                    <p class="text-gray-600 text-lg"><?= $menu['description'] ?></p>
                                </div>
                                <div class="text-right mt-4 md:mt-0">
                                    <p class="text-3xl font-bold text-amber-600"><?= number_format($menu['prix'], 2) ?> €</p>
                                    <a href="reservation.php?menu=<?= $menu['id'] ?>" class="inline-block mt-2 bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-full text-sm font-medium transition-colors">Réserver ce menu</a>
                                </div>
                            </div>

                            <!-- Plats du menu -->
                            <?php foreach ($menu['plats'] as $plat):
                                // Vérifier si le plat correspond aux tags sélectionnés
                                $showPlat = empty($selectedTags) || count(array_intersect($plat['tags'], $selectedTags)) > 0;
                                if ($showPlat): ?>
                                    <div class="border-t border-gray-200 py-6">
                                        <div class="flex flex-col md:flex-row gap-6">
                                            <div class="md:w-1/3">
                                                <img src="<?= $plat['image'] ?>" alt="<?= $plat['nom'] ?>" class="w-full h-48 object-cover rounded-lg">
                                            </div>
                                            <div class="md:w-2/3">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h3 class="text-xl font-playfair font-semibold text-gray-900"><?= $plat['nom'] ?></h3>
                                                        <p class="text-gray-600 mt-2"><?= $plat['description'] ?></p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-lg font-bold text-amber-600"><?= number_format($plat['prix'], 2) ?> €</p>
                                                        <button onclick="openPlatModal(<?= $menu['id'] ?>, '<?= addslashes($plat['nom']) ?>')" class="mt-2 text-sm text-amber-600 hover:text-amber-800 font-medium">Détails →</button>
                                                    </div>
                                                </div>
                                                <div class="flex flex-wrap gap-2 mt-4">
                                                    <?php foreach ($plat['tags'] as $tag): ?>
                                                        <span class="tag tag-<?= strtolower($tag) ?>"><?= ucfirst($tag) ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-amber-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-playfair font-bold mb-4">Prêt à vivre une expérience culinaire unique ?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Réservez votre table dès maintenant et laissez-nous vous surprendre.</p>
            <a href="reservation.php" class="inline-block bg-white text-amber-600 hover:bg-amber-50 px-8 py-3 rounded-full font-medium transition-colors">Réserver une table</a>
        </div>
    </section>

    <!-- Modal pour un menu complet -->
    <div id="menuModal" class="modal fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white pt-6 px-6 pb-4">
                <div class="flex justify-between items-center">
                    <h2 id="menuModalTitle" class="text-2xl font-playfair font-bold text-gray-900"></h2>
                    <button onclick="closeMenuModal()" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div id="menuModalContent" class="p-6">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>

    <!-- Modal pour un plat -->
    <div id="platModal" class="modal fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="platModalTitle" class="text-2xl font-playfair font-bold text-gray-900"></h2>
                    <button onclick="closePlatModal()" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="platModalContent" class="space-y-4">
                    <!-- Contenu chargé dynamiquement -->
                </div>
            </div>
        </div>
    </div>

    <!-- Script pour les modales -->
    <script>
        // Données des menus (pour les modales)
        const menusData = <?= json_encode($menus) ?>;

        function openMenuModal(menuId) {
            const menu = menusData.find(m => m.id === menuId);
            if (!menu) return;

            const modalTitle = document.getElementById('menuModalTitle');
            const modalContent = document.getElementById('menuModalContent');

            modalTitle.textContent = menu.nom;
            modalContent.innerHTML = `
                <div class="space-y-6">
                    <img src="${menu.image}" alt="${menu.nom}" class="w-full h-64 object-cover rounded-lg mb-4">

                    <div class="prose max-w-none">
                        <p class="text-lg text-gray-700">${menu.description}</p>
                        <p class="text-2xl font-bold text-amber-600 mt-4">${menu.prix.toFixed(2)} €</p>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-xl font-playfair font-semibold text-gray-900 mb-4">Plats inclus</h3>
                        <div class="space-y-6">
                            ${menu.plats.map(plat => `
                                <div class="flex gap-4">
                                    <img src="${plat.image}" alt="${plat.nom}" class="w-24 h-24 object-cover rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">${plat.nom}</h4>
                                        <p class="text-gray-600 text-sm mt-1">${plat.description}</p>
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            ${plat.tags.map(tag => `
                                                <span class="tag tag-${tag.toLowerCase()}">${tag}</span>
                                            `).join('')}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-amber-600">${plat.prix.toFixed(2)} €</p>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <a href="reservation.php?menu=${menu.id}" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-full font-medium transition-colors">Réserver ce menu</a>
                    </div>
                </div>
            `;

            document.getElementById('menuModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMenuModal() {
            document.getElementById('menuModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function openPlatModal(menuId, platNom) {
            const menu = menusData.find(m => m.id === menuId);
            if (!menu) return;

            const plat = menu.plats.find(p => p.nom === platNom);
            if (!plat) return;

            const modalTitle = document.getElementById('platModalTitle');
            const modalContent = document.getElementById('platModalContent');

            modalTitle.textContent = plat.nom;
            modalContent.innerHTML = `
                <img src="${plat.image}" alt="${plat.nom}" class="w-full h-64 object-cover rounded-lg mb-4">

                <div class="prose max-w-none">
                    <p class="text-gray-700 mb-4">${plat.description}</p>

                    <div class="mb-4">
                        <p class="text-lg font-medium text-gray-900">Prix: <span class="text-amber-600">${plat.prix.toFixed(2)} €</span></p>
                    </div>

                    <div class="mb-4">
                        <h4 class="font-medium text-gray-900 mb-2">Catégorie</h4>
                        <p class="text-gray-600">${plat.categorie}</p>
                    </div>

                    <div class="mb-4">
                        <h4 class="font-medium text-gray-900 mb-2">Informations supplémentaires</h4>
                        <div class="flex flex-wrap gap-2">
                            ${plat.tags.map(tag => `
                                <span class="tag tag-${tag.toLowerCase()}">${tag}</span>
                            `).join('')}
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 mt-6">
                    <button onclick="closePlatModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Fermer</button>
                    <a href="reservation.php?menu=${menu.id}" class="px-4 py-2 bg-amber-600 text-white rounded-md text-sm font-medium hover:bg-amber-700">Réserver</a>
                </div>
            `;

            document.getElementById('platModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closePlatModal() {
            document.getElementById('platModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Fermer les modales si clic à l'extérieur
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                closeMenuModal();
                closePlatModal();
            }
        });
    </script>
</body>
</html>