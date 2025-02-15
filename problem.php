<?php
require_once 'init.php';

// Vérification des permissions
if ($myStatutInt !== $statutAdmin && $myStatutInt !== $statutModo) {
    header('Location: /');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Supprimer le problème correspondant à l'ID passé dans le formulaire
    
    $sql_supr = $pdo->prepare("DELETE FROM problem WHERE id = ?");
    $sql_supr->execute([$_POST['id']]);
    
    if ($sql_supr->rowCount() > 0) {
        header("Location: $url");
        exit();
    } else {
        echo "<div class='alert'><p>Erreur lors de la suppression du problème.</p></div>";
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = htmlspecialchars($_POST['title'] ?? '');
    $description = htmlspecialchars($_POST['description'] ?? '');

    if (!empty($title) && !empty($description)) {
        $stmt = $pdo->prepare('INSERT INTO problem (title, description, date_creation, user_id) VALUES (:title, :description, NOW(), :user_id)');
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':user_id' => $myUser_id,
        ]);
        header("Location: $url");
    } else {
        $message = "<div class='alert'><p>Veuillez remplir tous les champs.</p></div>";
    }
}

// Récupération des problèmes
$problems = $pdo->query('SELECT * FROM problem ORDER BY date_creation DESC')->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Sharing - Problèmes</title>
    <link rel="icon" href="/img/Logo_Project-Sharing.png" type="image/png">
    <link rel="stylesheet" href="/styles.css">
    <style>
        .main {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            width: 100%;
        }
    </style>
</head>
<body>
    <main>
        <?php
        require_once 'nav.php';
        ?>
        <div class="main">
            <div class="conditionsutilisation">
                <h1>Ajouter un problème :</h1>
                <form method="POST" action="/problem">
                    <label for="title">Titre :</label><br>
                    <input type="text" id="title" name="title" required><br><br>

                    <label for="description">Description :</label><br>
                    <textarea id="description" name="description" rows="5" required></textarea><br><br>

                    <button type="submit">Ajouter</button>
                </form>

                <h1>Problèmes du site :</h1>
                <?php
                if (empty($problems)) {
                    echo "<p>Aucun problème enregistré pour le moment.</p>";
                } else {
                    foreach ($problems as $problem) {
                        $title = $problem['title'];
                        $description = $problem['description'];
                        $date_creation = $problem['date_creation'];
                        $id = (int)$problem['id'];
                        $targetUser_id = (int)$problem['user_id'];
                        
                        echo "<h2>$title</h2>
                            <p>$description</p>
                            <small>Ajouté le : $date_creation</small>";

                        if ($targetUser_id === $myUser_id || $myStatutInt === $statutAdmin) {
                            echo "<form method='POST' action='$url' enctype='multipart/form-data'>
                                    <input type='hidden' name='id' value='$id'>
                                    <input type='submit' name='supr_project' class='supr_project' value='Supprimer'>
                                </form>";
                        }
                    }
                }
                ?>
            </div>
        </div>
    </main>
</body>
</html>
