<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/db.php';

// Récupérer les 3 menus les plus populaires (exemple)
$menus = [
    ['nom' => 'Menu Découverte', 'prix' => 39.90, 'description' => 'Un voyage culinaire en 5 plats signature.'],
    ['nom' => 'Menu Végétarien', 'prix' => 34.90, 'description' => 'Des saveurs fraîches et créatives 100% végétales.'],
    ['nom' => 'Menu du Chef', 'prix' => 59.90, 'description' => 'L\'expérience ultime avec accords mets-vins.'],
];

// Récupérer les témoignages (exemple)
$temoignages = [
    ['nom' => 'Sophie M.', 'note' => 5, 'avis' => 'Une soirée inoubliable, service impeccable et plats divins !'],
    ['nom' => 'Jean L.', 'note' => 4, 'avis' => 'Cadre chaleureux et cuisine raffinée. À refaire sans hésiter.'],
    ['nom' => 'Émilie T.', 'note' => 5, 'avis' => 'Le menu végétarien est une pépite. Merci pour cette expérience !'],
];
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Le Comptoir des Saveurs | Réservations en Ligne</title>
    <meta name="description" content="Réservez votre table au Comptoir des Saveurs et découvrez une expérience gastronomique unique en centre-ville.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'playfair': ['"Playfair Display"', 'serif'],
                        'inter': ['"Inter"', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#8B4513', // Brun chocolat
                        'secondary': '#D4A574', // Beige doré
                        'accent': '#E8C4B8', // Rose pâle
                    },
                },
            },
        };
    </script>
    <style>
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2073&q=80');
            background-size: cover;
            background-position: center;
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .menu-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="font-inter text-gray-800">

    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero min-h-screen flex items-center justify-center text-white text-center px-4">
        <div class="max-w-3xl">
            <?php if (isset($_SESSION['username'])): ?>
                <h1 class="text-5xl font-playfair font-bold mb-4">Bon retour parmi nous, <?= htmlspecialchars($_SESSION['username']) ?></h1>
            <?php else: ?>
                <h1 class="text-5xl font-playfair font-bold mb-4">Bienvenue au Comptoir des Saveurs</h1>
                <p class="text-xl mb-6">Connectez-vous pour accéder à vos réservations et offres exclusives.</p>
                <div class="flex gap-4 justify-center mb-8">
                    <a href="login.php" class="bg-secondary text-primary px-6 py-3 rounded-full font-semibold hover:bg-accent hover:text-primary transition">Connexion</a>
                    <a href="register.php" class="border-2 border-white text-white px-6 py-3 rounded-full font-semibold hover:bg-white hover:text-primary transition">Inscription</a>
                </div>
            <?php endif; ?>
            <p class="text-2xl mb-8">Découvrez une expérience culinaire unique en réservant votre table en ligne.</p>
            <a href="reservation.php" class="bg-primary text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-opacity-90 transition inline-block">Réserver une table</a>
        </div>
    </section>

    <!-- Nos Menus -->
    <section class="py-16 bg-accent/30">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-playfair text-center mb-12 text-primary">Nos Menus</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($menus as $menu): ?>
                    <div class="menu-card bg-white rounded-xl overflow-hidden shadow-md">
                        <div class="p-6">
                            <h3 class="text-2xl font-playfair font-bold mb-2 text-primary"><?= $menu['nom'] ?></h3>
                            <p class="text-lg font-semibold mb-4"><?= number_format($menu['prix'], 2) ?> €</p>
                            <p class="text-gray-600 mb-6"><?= $menu['description'] ?></p>
                            <a href="menu.php?id=<?= urlencode($menu['nom']) ?>" class="block w-full bg-secondary text-primary py-2 rounded-full text-center font-medium hover:bg-primary hover:text-white transition">Voir le menu</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Témoignages -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-playfair text-center mb-12 text-primary">Ils nous font confiance</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($temoignages as $temoignage): ?>
                    <div class="bg-accent p-6 rounded-xl">
                        <div class="flex mb-4">
                            <?php for ($i = 0; $i < $temoignage['note']; $i++): ?>
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            <?php endfor; ?>
                        </div>
                        <p class="text-gray-700 italic mb-4">"<?= $temoignage['avis'] ?>"</p>
                        <p class="font-semibold text-primary">— <?= $temoignage['nom'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Galerie -->
    <section class="py-16 bg-accent/30">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-playfair text-center mb-12 text-primary">Notre Restaurant</h2>
            <div class="grid md:grid-cols-4 gap-4">
                <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80" alt="Salle du restaurant" class="rounded-xl object-cover h-64 w-full">
                <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80" alt="Plat signature" class="rounded-xl object-cover h-64 w-full">
                <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80" alt="Cuisine ouverte" class="rounded-xl object-cover h-64 w-full">
                <img src="https://images.unsplash.com/photo-1466978913421-dad2ebd01d17?auto=format&fit=crop&w=1200&q=80" alt="Terrasse" class="rounded-xl object-cover h-64 w-full">
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-16 bg-primary text-white text-center">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-playfair mb-4">Prêt à vivre une expérience unique ?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Réservez votre table dès maintenant et laissez-nous vous surprendre.</p>
            <a href="reservation.php" class="bg-white text-primary px-8 py-4 rounded-full font-semibold text-lg hover:bg-accent transition inline-block">Réserver maintenant</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-playfair font-bold mb-4">Le Comptoir des Saveurs</h3>
                    <p class="text-gray-400">Une expérience gastronomique au cœur de la ville, où chaque plat raconte une histoire.</p>
                </div>
                <div>
                    <h3 class="text-xl font-playfair font-bold mb-4">Liens utiles</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white transition">Accueil</a></li>
                        <li><a href="reservation.php" class="text-gray-400 hover:text-white transition">Réserver</a></li>
                        <li><a href="menu.php" class="text-gray-400 hover:text-white transition">Nos Menus</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-playfair font-bold mb-4">Contact</h3>
                    <address class="text-gray-400 not-italic">
                        12 Rue des Gourmets<br>
                        83000 Toulon<br>
                        <a href="tel:+33494123456" class="hover:text-white transition">04 94 12 34 56</a><br>
                        <a href="mailto:contact@comptoirdessaveurs.fr" class="hover:text-white transition">contact@comptoirdessaveurs.fr</a>
                    </address>
                </div>
                <div>
                    <h3 class="text-xl font-playfair font-bold mb-4">Horaires</h3>
                    <p class="text-gray-400">
                        <strong>Midi :</strong> 12h00 - 14h00<br>
                        <strong>Soir :</strong> 19h00 - 22h30<br>
                        <strong>Fermé :</strong> Lundi et Dimanche soir
                    </p>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 Le Comptoir des Saveurs — Projet BTS SIO SLAM par Alexis Verdiere</p>
            </div>
        </div>
    </footer>
</body>
</html>
