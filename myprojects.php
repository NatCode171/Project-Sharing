<?php
require_once 'init.php';

if (!$myUser_id) {
    header('Location: /');
    exit();
}

if (isset($_POST['supr_project']) && isset($_POST['project_id'])) {
    // Supprimer le projet correspondant à l'ID passé dans le formulaire
    $project_id = (int)$_POST['project_id'];
    $sql_supr = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $sql_supr->execute([$project_id]);
    
    if ($sql_supr->rowCount() < 0) {
        echo "<div class='alert'><h3>Erreur lors de la suppression du projet.</h3></div>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Sharing - Mes projets</title>
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
            <div class="projetslistecontainer">
                <div class="accueil">
                    <h1>Mes projets :</h1>
                </div>
                <div class="gridbuttonsprojects">
                    <?php
                    $sql = $pdo->prepare("SELECT id, title, description, timestamp, likes, dislikes FROM projects WHERE user_id = ?");
                    $sql->execute([$myUser_id]);

                    if ($sql->rowCount() > 0) {
                        while ($ligne = $sql->fetch(PDO::FETCH_ASSOC)) {
                            $project_id = $ligne['id'];
                            $project_title = $ligne['title'];
                            $project_description = $ligne['description'];
                            $project_timestamp = $ligne['timestamp'];
                            $nb_project_like = $ligne['likes'];
                            $nb_project_dislike = $ligne['dislikes'];
                    
                            echo '<a class="buttonsprojects" href="projects?id=' . urlencode($project_id) . '">';
                            echo "<h3>$project_title</h3>
                                <p>$project_description</p>
                                <h4>Créer le : $project_timestamp</h4>
                                <div class='like'>
                                  <form method='POST' action='projects?gohome=2&id=" . urlencode($project_id) . "'>
                                    <input type='hidden' name='like' value='1'>
                                    <input type='hidden' name='project_id' value='$project_id'>
                                    <button type='submit' class='like-button'><img src='/img/like.png' alt='Like'>$nb_project_like</button>
                                  </form>
                                </div>
                                <div class='dislike'>
                                  <form method='POST' action='projects?gohome=2&id=" . urlencode($project_id) . "'>
                                    <input type='hidden' name='dislike' value='1'>
                                    <input type='hidden' name='project_id' value='$project_id'>
                                    <button type='submit' class='like-button'><img src='/img/dislike.png' alt='Dislike'>$nb_project_dislike</button>
                                  </form>
                                </div>
                                <form method='POST' action='/myprojects' enctype='multipart/form-data'>
                                    <input type='hidden' name='project_id' value='$project_id'>
                                    <input type='submit' name='supr_project' class='supr_project' value='Supprimer'>
                                </form>
                                </a>
                                <br>
                                <br>";
                        }
                    } else {
                        echo "<div class='alert'><p>Aucun projet trouvé.</p></div>
                            <a href='/newproject'><div class='ma_bio'><p>Créer un project</p></div></a>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
