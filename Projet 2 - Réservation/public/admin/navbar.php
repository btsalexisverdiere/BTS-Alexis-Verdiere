<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simuler une notification pour l'admin (exemple)
$adminNotifications = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' ? 3 : 0;
?>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<nav class="sticky top-0 z-50 bg-gray-900/95 backdrop-blur-sm border-b border-gray-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <!-- Logo et menu mobile -->
      <div class="flex items-center">
        <div class="flex-shrink-0 flex items-center gap-2">
          <span class="text-xl text-amber-400">🍽️</span>
          <a href="../index.php" class="text-white font-semibold text-lg hover:text-amber-300 transition-colors">
            Le Comptoir des Saveurs
          </a>
        </div>

        <!-- Menu principal (desktop) -->
        <div class="hidden md:block ml-10">
          <div class="flex items-baseline space-x-4">
            <a href="../index.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Accueil</a>
            <a href="../reservation.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Réserver</a>
            <a href="../menu.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Menus</a>
          </div>
        </div>
      </div>

      <!-- Menu utilisateur et admin -->
      <div class="flex items-center gap-4">
        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- Menu utilisateur connecté -->
          <div class="relative group">
            <button id="user-menu-button" class="flex items-center gap-2 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
              <span class="sr-only">Ouvrir le menu utilisateur</span>
              <span class="hidden sm:inline text-white font-medium"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></span>
              <svg class="h-8 w-8 rounded-full bg-amber-500 text-white p-1" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
              </svg>
            </button>

            <!-- Dropdown utilisateur -->
            <div id="user-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 border border-gray-200 hidden group-hover:block">
              <a href="./dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-900 transition-colors">Mon compte</a>
              <a href="./reservations.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-900 transition-colors">Mes réservations</a>
              <div class="border-t border-gray-200 my-1"></div>
              <a href="../../includes/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors">Déconnexion</a>
            </div>
          </div>

          <!-- Menu admin (si admin) -->
          <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <div class="relative group ml-3">
              <button id="admin-menu-button" class="flex items-center gap-1 bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors relative">
                <span>Admin</span>
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
                <?php if ($adminNotifications > 0): ?>
                  <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?= $adminNotifications ?></span>
                <?php endif; ?>
              </button>

              <!-- Dropdown admin -->
              <div id="admin-dropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 border border-gray-200 hidden group-hover:block">
                <a href="./dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-900 transition-colors flex justify-between items-center">
                  Tableau de bord
                  <?php if ($adminNotifications > 0): ?>
                    <span class="bg-red-100 text-red-800 text-xs px-2 rounded-full"><?= $adminNotifications ?></span>
                  <?php endif; ?>
                </a>
                <a href="./reservations.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-900 transition-colors">Gérer les réservations</a>
                <a href="https://192.168.1.2:2208/public/" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-900 transition-colors">Gérer le stock</a>
                <div class="border-t border-gray-200 my-1"></div>
                <a href="../../includes/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">Déconnexion</a>
              </div>
            </div>
          <?php endif; ?>

        <?php else: ?>
          <!-- Boutons Connexion/Inscription (mobile + desktop) -->
          <div class="hidden md:flex items-center gap-3">
            <a href="/public/login.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Connexion</a>
            <a href="../register.php" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Inscription</a>
          </div>
        <?php endif; ?>

        <!-- Bouton menu mobile -->
        <div class="-mr-2 flex md:hidden">
          <button id="mobile-menu-button" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
            <span class="sr-only">Ouvrir le menu principal</span>
            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Menu mobile (caché par défaut) -->
  <div id="mobile-menu" class="hidden md:hidden">
    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
      <a href="./index.php" class="text-gray-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Accueil</a>
      <a href="./reservation.php" class="text-gray-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Réserver</a>
      <a href="./menu.php" class="text-gray-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Menus</a>
      <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="px-4 py-2 space-y-2">
          <a href="/public/login.php" class="w-full flex items-center justify-center bg-gray-800 text-white px-4 py-2 rounded-md text-base font-medium hover:bg-gray-700 transition-colors">Connexion</a>
          <a href="./register.php" class="w-full flex items-center justify-center bg-amber-600 text-white px-4 py-2 rounded-md text-base font-medium hover:bg-amber-700 transition-colors">Inscription</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Script pour gérer les menus déroulants et mobile -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Menu utilisateur (hover)
  const userMenuButton = document.getElementById('user-menu-button');
  const userDropdown = document.getElementById('user-dropdown');
  if (userMenuButton && userDropdown) {
    userMenuButton.addEventListener('click', function(e) {
      e.stopPropagation();
      userDropdown.classList.toggle('hidden');
    });
  }

  // Menu admin (hover)
  const adminMenuButton = document.getElementById('admin-menu-button');
  const adminDropdown = document.getElementById('admin-dropdown');
  if (adminMenuButton && adminDropdown) {
    adminMenuButton.addEventListener('click', function(e) {
      e.stopPropagation();
      adminDropdown.classList.toggle('hidden');
    });
  }

  // Menu mobile
  const mobileMenuButton = document.getElementById('mobile-menu-button');
  const mobileMenu = document.getElementById('mobile-menu');
  if (mobileMenuButton && mobileMenu) {
    mobileMenuButton.addEventListener('click', function() {
      mobileMenu.classList.toggle('hidden');
    });
  }

  // Fermer les dropdowns si clic à l'extérieur
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#user-menu-button') && userDropdown && !userDropdown.classList.contains('hidden')) {
      userDropdown.classList.add('hidden');
    }
    if (!e.target.closest('#admin-menu-button') && adminDropdown && !adminDropdown.classList.contains('hidden')) {
      adminDropdown.classList.add('hidden');
    }
  });
});
</script>
