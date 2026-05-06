</div> <!-- fermeture container principal -->

<footer class="bg-gray-900 text-gray-300 mt-16 border-t border-gray-800">

    <div class="max-w-7xl mx-auto px-6 py-10 grid grid-cols-1 md:grid-cols-3 gap-10">

        <!-- Bloc 1 : Application -->
        <div>
            <h3 class="text-white text-lg font-semibold mb-3 flex items-center gap-2">
                📦 Gestion de Stock
            </h3>
            <p class="text-sm leading-relaxed text-gray-400">
                Application interne permettant le suivi des produits, des entrées/sorties
                et la gestion des alertes en temps réel.
            </p>
        </div>

        <!-- Bloc 2 : Navigation -->
        <div>
            <h3 class="text-white text-lg font-semibold mb-3">Navigation rapide</h3>
            <ul class="space-y-2 text-sm">
                <li>
                    <a href="dashboard.php" class="hover:text-white transition">📊 Dashboard</a>
                </li>
                <li>
                    <a href="produits.php" class="hover:text-white transition">📋 Produits</a>
                </li>
                <li>
                    <a href="mouvements.php" class="hover:text-white transition">🔄 Stock</a>
                </li>
            </ul>
        </div>

        <!-- Bloc 3 : Infos -->
        <div>
            <h3 class="text-white text-lg font-semibold mb-3">Informations</h3>
            <ul class="text-sm space-y-2 text-gray-400">
                <li>Version : <span class="text-gray-200 font-medium">1.0</span></li>
                <li>Backend : PHP / MySQL</li>
                <li>UI : Tailwind CSS</li>
                <li class="pt-2"><?= date('Y') ?> © Restaurant</li>
            </ul>
        </div>

    </div>

    <!-- Barre bas -->
    <div class="bg-gray-800 text-center text-xs py-4 text-gray-400 flex flex-col md:flex-row justify-center items-center gap-2">
        <span>Projet BTS SIO SLAM</span>
        <span class="hidden md:inline">•</span>
        <span>Application de gestion de stock</span>
    </div>

</footer>

</body>
</html>