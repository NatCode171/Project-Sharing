<?php
require_once 'init.php';
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset="utf-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Project Sharing - Mes abonnements</title>
    <link rel='icon' href='/img/Logo_Project-Sharing.png' type='image/png'>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <main>
        <?php
        require_once 'nav.php';
        ?>
        <div class="main">
            <div class="accueil">
                <h1>Vos abonnements :</h1>
            </div>
            <br>
            <?php
            allusers(false, true); // mysubscribe and subscribe
            if (!empty($abonnesIds)) {
                echo "<div class='projetslistecontainer'>
                    <h2>Les projets :</h2>
                    <div class='gridbuttonsprojects'>";

                // Préparer la requête pour récupérer les projets des abonnés
                $placeholders = implode(',', array_fill(0, count($abonnesIds), '?'));
                $requete = "SELECT projects.id AS project_id, projects.title, projects.description, projects.user_id, users.pseudo 
                            FROM projects 
                            INNER JOIN users ON projects.user_id = users.id 
                            WHERE projects.user_id IN ($placeholders)";
                $sql = $pdo->prepare($requete);
                $sql->execute($abonnesIds);

                $projects = $sql->fetchAll(PDO::FETCH_ASSOC);

                if ($projects) {
                    foreach ($projects as $ligne) {
                        $project_id = (int)$ligne['project_id'];
                        $title = htmlspecialchars($ligne['title']);
                        $description = htmlspecialchars($ligne['description']);
                        $pseudo = htmlspecialchars($ligne['pseudo']);
                        $targetUser_id = (int)$ligne['user_id'];

                        echo '<a class="buttonsprojects" href="projects?id=' . urlencode($project_id) . '">';
                        echo "<h1>Projet de $pseudo</h1>
                            <h3>$title</h3>
                            <p>$description</p>";

                        // Vérifier si l'utilisateur peut supprimer le projet
                        if ($myStatutInt === $statutAdmin) {
                            echo '<form method="POST" action="/">
                                    <input type="hidden" name="project_id" value="' . $project_id . '">
                                    <input type="submit" name="supr_project" class="supr_project" value="Supprimer">
                                </form>';
                            }
                        echo "</a>";
                    }
                } else {
                    echo "<div class='alert'><p>Aucun projet trouvé pour vos abonnés.</p></div>";
                }
            }

            // Gestion de la suppression d'un projet
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supr_project'], $_POST['project_id'])) {
                $project_id = (int)$_POST['project_id'];

                if ($myStatutInt === $statutAdmin) {
                    $sql = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                    $sql->execute([$project_id]);

                    if ($sql->rowCount() > 0) {
                        header("Location: $url");
                        exit();
                    } else {
                        echo "<div class='alert'><h3>Erreur lors de la suppression du projet.</h3></div>";
                        exit();
                    }
                } else {
                    echo "<div class='alert'><h3>Vous n'êtes pas autorisé à supprimer ce projet.</h3></div>";
                    exit();
                }
            }
            ?>
        </div>
    </main>
</body>
</html>
