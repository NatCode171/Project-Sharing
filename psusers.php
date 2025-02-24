<?php
require_once 'init.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Sharing - Utilisateurs</title>
    <link rel="icon" href="/img/Logo_Project-Sharing.png" type="image/png">
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <main>
        <?php
        require_once 'nav.php';
        ?>
        <div class="main">
            <div class="accueil">
                <h1>Utilisateurs du site :</h1>
            </div>
            
            <?php
            allusers(false, false); // mysubscribe and subscribe
            ?>
        </div>
    </main>
</body>
</html>
