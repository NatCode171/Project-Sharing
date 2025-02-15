<?php
require_once 'init.php';
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset="utf-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Project Sharing - Acceuil</title>
    <link rel='icon' href='/img/Logo_Project-Sharing.png' type='image/png'>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <?php
    require_once 'nav.php';
    ?>
    <main>
        <div class="login">
            <div class='settingsvalidate'>
                <?php
                $targetPseudo = isset($_GET['pseudo']) ? $_GET['pseudo'] : '';

                if ($targetPseudo != '') {
                    echo "<h1>Le compte de $targetPseudo est en cour de supression...</h1>
                          <p>Il sera supprimé de manière définitive dans un délai minimum de 30 jours.<br>Dans 30 jours, dès qu'une personne se rendra sur le site web,<br>le compte sera supprimé.</p>
                          <p>Pour récupérer le compte, il suffira à l'utilisateur de se connecter et il ne sera plus supprimé !</p>";
                } else {
                    logout(false, false);
                    echo "<h1>Ton compte est en cour de supression...</h1>
                          <p>Il sera supprimé de manière définitive dans un délai minimum de 30 jours.<br>Dans 30 jours, dès qu'une personne se rendra sur le site web,<br>ton compte sera supprimé.</p>
                          <p>Pour récupérer ton compte, il te suffit de te connecter et il ne sera plus supprimé !</p>";
                }
                echo "<a href='/'><button class='settingsvalidate-button'>Accueil</button></a>";
                ?>
            </div>
        </div>
    </main>
</html>
