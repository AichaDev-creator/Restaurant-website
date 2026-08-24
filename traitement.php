<?php
// 1. Connexion à la base de données (remplace avec tes identifiants si besoin)
try {
    $pdo = new PDO('mysql:host=localhost;dbname=patisserie;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

// 2. Récupération des données du formulaire (avec vérification pour éviter les erreurs
//    "Undefined array key" si un champ n'est pas envoyé)
$typecommande = trim($_POST['typecommande'] ?? '');
$nom          = trim($_POST['nom'] ?? '');
$telephone    = trim($_POST['telephone'] ?? '');
$email        = trim($_POST['email'] ?? '');
$dates        = trim($_POST['dates'] ?? '');
$heure        = trim($_POST['heure'] ?? '');
$demande      = trim($_POST['demande'] ?? '');

// Le champ caché qui contient le résumé du panier
$contenu_panier = !empty($_POST['contenu_panier']) ? $_POST['contenu_panier'] : NULL;

// Gestion du nombre de personnes (NULL si commande à emporter)
$nbpersonne = !empty($_POST['nbpersonne']) ? $_POST['nbpersonne'] : NULL;

// Petite vérification des champs obligatoires avant d'aller plus loin
if ($typecommande === '' || $nom === '' || $telephone === '' || $dates === '' || $heure === '') {
    die('Erreur : un ou plusieurs champs obligatoires sont manquants.');
}

// 3. Insertion dans la base de données
// Vérifie que la table "reservations" existe bien avec EXACTEMENT ces colonnes :
// typecommande, nom, telephone, email, nbpersonne, dates, heure, demande, contenu_panier, date_creation
$sql = "INSERT INTO reservations (typecommande, nom, telephone, email, nbpersonne, dates, heure, demande, contenu_panier, date_creation) 
        VALUES (:typecommande, :nom, :telephone, :email, :nbpersonne, :dates, :heure, :demande, :contenu_panier, NOW())";

// IMPORTANT : le prepare()/execute() est maintenant DANS le try/catch,
// sinon une erreur SQL (table/colonne absente, etc.) provoque une page blanche
// au lieu d'un message d'erreur clair.
try {
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':typecommande'   => $typecommande,
        ':nom'            => $nom,
        ':telephone'      => $telephone,
        ':email'          => $email,
        ':nbpersonne'     => $nbpersonne,
        ':dates'          => $dates,
        ':heure'          => $heure,
        ':demande'        => $demande,
        ':contenu_panier' => $contenu_panier
    ]);

    // 4. Page de confirmation stylée
    $libelleType = ($typecommande === 'emporter') ? 'Commande à emporter' : 'Réservation sur place';
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirmation - Douceur Sucrée</title>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <script>
            (function() {
                if (localStorage.getItem('theme-douceur-sucree') === 'sombre') {
                    document.documentElement.setAttribute('data-theme', 'sombre');
                }
            })();
        </script>
    </head>
    <body>
        <div class="confirmation-wrapper">
            <div class="confirmation-carte">
                <div class="confirmation-icone">✓</div>
                <h1>Merci, <?= htmlspecialchars($nom) ?> !</h1>
                <p class="message">Votre demande a bien été enregistrée. Nous vous contacterons rapidement pour la confirmer.</p>
                <div class="confirmation-recap">
                    <p><span>Type</span><span><?= htmlspecialchars($libelleType) ?></span></p>
                    <?php if (!empty($nbpersonne)): ?>
                    <p><span>Personnes</span><span><?= htmlspecialchars($nbpersonne) ?></span></p>
                    <?php endif; ?>
                    <p><span>Date</span><span><?= htmlspecialchars($dates) ?></span></p>
                    <p><span>Heure</span><span><?= htmlspecialchars($heure) ?></span></p>
                    <p><span>Téléphone</span><span><?= htmlspecialchars($telephone) ?></span></p>
                </div>
                <a href="p2.html" class="btn">Retour à l'accueil</a>
            </div>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    // Affiche l'erreur SQL réelle (table manquante, colonne inconnue, etc.)
    // À enlever ou remplacer par un message générique une fois en production.
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Erreur - Douceur Sucrée</title>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="confirmation-wrapper">
            <div class="confirmation-carte confirmation-erreur">
                <div class="confirmation-icone">✕</div>
                <h1>Un problème est survenu</h1>
                <p class="message"><?= htmlspecialchars($e->getMessage()) ?></p>
                <a href="p2.html" class="btn">Retour au formulaire</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>