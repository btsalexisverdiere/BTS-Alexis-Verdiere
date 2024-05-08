var currentPlayer = 'green'; // Le joueur initial est le joueur vert
var gameEnded = false; // Indique si la partie est terminée
var scores = { 'green': 0, 'red': 0 }; // Tableau de points des joueurs
var colorCounter = 0; // Compteur de coups

// Fonction pour initialiser le tableau à partir du tableau HTML
function initTableau() {
    var cells = document.querySelectorAll('#tableau td');
    cells.forEach(function(cell) {
        cell.addEventListener('click', coloreCase);
    });
    colorCounter = 0; // Initialiser le compteur de coups à zéro
}

// Appeler la fonction d'initialisation une fois que le DOM est chargé
document.addEventListener('DOMContentLoaded', initTableau);

function coloreCase(event) {
    if (gameEnded) return; // Arrête la fonction si la partie est terminée
    var targetElement = event.target;
    if (targetElement.style.backgroundColor === '') {
        targetElement.style.backgroundColor = currentPlayer;
        colorCounter++; // Incrémenter le compteur de coups
        if (checkWinner()) {
            scores[currentPlayer]++;
            updateScores();
            // Afficher le gagnant
            alert('Le joueur ' + currentPlayer + ' a gagné !');
            gameEnded = true;

            // Envoyer le gagnant au serveur
            sendWinner(currentPlayer);

            // Recharger la page
            location.reload();

            return; // Arrêter la fonction ici pour empêcher la rotation des joueurs
        } else if (colorCounter === 9) {
            // Si la grille est remplie et personne n'a gagné, c'est un match nul
            alert('Match nul !');
            gameEnded = true;

            // Recharger la page
            location.reload();

            return;
        }
        currentPlayer = (currentPlayer === 'green') ? 'red' : 'green';
    }
}

function checkWinner() {
    var cells = document.querySelectorAll('#tableau td');
    var combinations = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8], // lignes horizontales
        [0, 3, 6], [1, 4, 7], [2, 5, 8], // lignes verticales
        [0, 4, 8], [2, 4, 6] // diagonales
    ];
    return combinations.some(function(combination) {
        var [a, b, c] = combination;
        return cells[a].style.backgroundColor &&
               cells[a].style.backgroundColor === cells[b].style.backgroundColor &&
               cells[a].style.backgroundColor === cells[c].style.backgroundColor;
    });
}

function resetTableau() {
    var cells = document.querySelectorAll('#tableau td');
    cells.forEach(function(cell) {
        cell.style.backgroundColor = ''; // Réinitialise la couleur de chaque cellule
    });
    colorCounter = 0; // Réinitialise le compteur de cases remplies
}

// Fonction pour mettre à jour les scores affichés dans le tableau HTML
function updateScores() {
    document.getElementById('scoreGreen').textContent = scores['green'];
    document.getElementById('scoreRed').textContent = scores['red'];
}

// Ajouter un événement de clic au bouton "Nouvelle partie"
document.getElementById('nouvellePartie').addEventListener('click', nouvellePartie);

function nouvellePartie() {
    resetGame();
}

// Fonction pour réinitialiser le jeu pour une nouvelle partie
function resetGame() {
    var cells = document.querySelectorAll('#tableau td');
    cells.forEach(function(cell) {
        cell.style.backgroundColor = ''; // Réinitialiser la couleur de chaque cellule
    });
    gameEnded = false;
    colorCounter = 0;
}

// Fonction pour envoyer le gagnant au serveur
function sendWinner(winner) {
    if (gameEnded) { // Vérifier si la partie est terminée
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'morpion.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('winner=' + winner);
    }
}
