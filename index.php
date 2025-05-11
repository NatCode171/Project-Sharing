<?php
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['disable_popup_discord'])) {
    $stmt = $pdo->prepare("UPDATE users SET popup_discord = 0 WHERE id = ?");
    $stmt->execute([$myUser_id]);
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
    <title>Project Sharing - Accueil</title>
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
                <h1>Bienvenue sur le site de partage de projets informatiques !!!<br>Vous pouvez partager vos projets et voir ceux des autres.</h1>
            </div>

            <div class="pub_in_project" id="pub_in_project">
                <button class="close-btn-2" onclick='closepopuppub_in_project()'>✖</button>
                <img src="/img/Logo_Weeble.png" alt="Weeble">
                <div class="txt_pub">
                    <h2>Weeble</h2>
                    <p>Rejoignez Weeble, un nouveau moteur de recherche sécurisé.</p>
                    <p><a href="https://weeble.fr/" target="_blank">Weeble.fr</a></p>
                </div>
            </div>
            <br>
            
            <?php
            // popup pub ou info + discord
            if ($myUser_id) {
                if ($myPopupDiscord) {
                    $showDiscord = true;
                }
                if ($my_info_ou_pub != $info_ou_pub) {
                    if ($info_ou_pub == 1) {
                        $showPub = true;
                    } elseif ($info_ou_pub == 2) {
                        $showInfo = true;
                    }
                }
                $sql = $pdo->prepare("UPDATE users SET info_ou_pub = ? WHERE id = ?");
                $sql->execute([$info_ou_pub, $myUser_id]);
            } else {
                if ($popupDiscord) {
                    $showDiscord = true;
                }
                if ($info_ou_pub === 1) {
                    $showPub = true;
                } elseif ($info_ou_pub === 2) {
                    $showInfo = true;
                }
            }

            // popup discord
            if ($showDiscord) {
                echo "<div class='popup' id='popupdiscord'>
                        <p>Qu'attends-tu pour rejoindre le serveur Discord de Project Sharing ?
                        <a href='https://discord.gg/zzSkXwGXqT' target='_blank'>Rejoindre Discord</a>";
                if ($myUser_id) {
                    echo "<button class='close-btn' onclick='nevershowagaindiscord()'>(Ne plus afficher)</button>";
                }
                echo "</p>
                    <button class='close-btn' onclick='closepopupdiscord()'>✖</button>
                    </div>";
            }

            // popup pub ou info
            if ($showPub) {
                /*
                echo "<div class='popupinfo' id='popuppub'>
                        <img class='round-logo' src='/img/Logo_TERRIA.png' alt='TERRIA'>
                        <h2>TERRIA</h2>
                        <p>Rejoinez Terria, un serveur Minecraft INCROYABLE !!!</p>
                        <p><a href='https://terria.eu/' target='_blank'>terria.eu</a></p>
                        <button class='close-btn' onclick='closepub()'>✖ Fermer</button>
                    </div>";*/
                // Pour la pub de Weeble
                /*
                echo "<div class='popupinfo' id='popuppub'>
                        <img class='round-logo' src='/img/Logo_Weeble.png' alt='Weeble'>
                        <h2>Weeble</h2>
                        <p>Rejoinez Weeble, un nouveau moteur de recherche sécuriser.</p>
                        <p><a href='https://weeble.fr/' target='_blank'>Weeble.fr</a></p>
                        <button class='close-btn' onclick='closepub()'>✖ Fermer</button>
                    </div>";
                */
            } 
            if ($showInfo) {
                echo "<div class='popupinfo' id='popupinfo'>
                        <img src='/img/Logo_Project-Sharing.png' alt='Logo Project Sharing'>
                        <h2>Bienvenue sur la $vwebsite de Project Sharing !</h2>
                        <p>Nous avons ajouté de nouvelles fonctionnalités. Découvrez-les maintenant !</p>
                        <p><a href='https://project-sharing.fr.to/infos' target='_blank'>Voir les informations</a></p>
                        <button class='close-btn' onclick='closeinfo()'>✖ Fermer</button>
                    </div>";
            }


            $searchTerm = trim(htmlspecialchars(isset($_GET['search']))) ? trim(htmlspecialchars($_GET['search'])) : '';
            ?>

            <div class="projetslistecontainer">
                <form method="GET" action="/" class="search-bar">
                    <input type="text" name="search" placeholder="Rechercher un projet..." value="<?php echo $searchTerm; ?>" />
                    <button type="submit">Rechercher</button>
                </form>
            
                <div class="gridbuttonsprojects">                    
                    <?php

                    $like_rand_score = mt_rand(55, 70) / 100;
                    $date_rand_score = 1 - $like_rand_score;


                    $sqlQuery = "SELECT id, title, description, user_id, likes, dislikes, timestamp,
                        (likes * :like_rand_score + (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(timestamp)) * -0.000001 * :date_rand_score) AS score FROM projects";
                        
                    if ($searchTerm) {
                        $sqlQuery .= " WHERE timestamp LIKE :searchTerm OR title LIKE :searchTerm OR description LIKE :searchTerm";
                        $params = [
                            ':searchTerm' => '%' . $searchTerm . '%',
                            ':like_rand_score' => $like_rand_score,
                            ':date_rand_score' => $date_rand_score
                        ];
                    } else {
                        $params = [
                            ':like_rand_score' => $like_rand_score,
                            ':date_rand_score' => $date_rand_score
                        ];
                    }
                
                    // On trie par score décroissant
                    $sqlQuery .= " ORDER BY score DESC";
                    
                    $stmt = $pdo->prepare($sqlQuery);
                    $stmt->execute($params);

            
                    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {

                        $showedProject = true;

                        $project_id = $ligne['id'];
                        $nb_project_like = (int)$ligne['likes'];
                        $nb_project_dislike = (int)$ligne['dislikes'];
                        $project_title = $ligne['title'];
                        $project_description = $ligne['description'];
                        $targetUser_id = $ligne['user_id'];
                        $project_timestamp = $ligne['timestamp'];
            
                        $sql_project = $pdo->prepare("SELECT pseudo FROM users WHERE id = ?");
                        $sql_project->execute([$targetUser_id]);
                        $result_project = $sql_project->fetch(PDO::FETCH_ASSOC);
            
                        $targetPseudo = $result_project ? htmlspecialchars($result_project['pseudo']) : "Utilisateur inconnu";
            
                        echo "<div class='buttonsprojects'>
                                <a href='projects?id=" . urlencode($project_id) . "'>
                                <h1>Projet de $targetPseudo</h1>
                                <h3>$project_title</h3>
                                <p>$project_description</p>
                                <h4>Créer le : $project_timestamp</h4>
                                <div class='like'>
                                  <form method='POST' action='projects?gohome=1&id=" . urlencode($project_id) . "'>
                                    <input type='hidden' name='like' value='1'>
                                    <input type='hidden' name='project_id' value='$project_id'>
                                    <button type='submit' class='like-button'><img src='/img/like.png' alt='Like'>$nb_project_like</button>
                                  </form>
                                </div>
                                <div class='dislike'>
                                  <form method='POST' action='projects?gohome=1&id=" . urlencode($project_id) . "'>
                                    <input type='hidden' name='dislike' value='1'>
                                    <input type='hidden' name='project_id' value='$project_id'>
                                    <button type='submit' class='like-button'><img src='/img/dislike.png' alt='Dislike'>$nb_project_dislike</button>
                                  </form>
                                </div>";
            
                        if ($myStatutInt === $statutAdmin || $targetUser_id === $myUser_id) {
                            echo "<br>
                                  <form method='POST' action='/' enctype='multipart/form-data'>
                                    <input type='hidden' name='project_id' value='$project_id'>
                                    <input type='submit' name='supr_project' class='supr_project' value='Supprimer'>
                                  </form>";
                        }
                        echo "</a></div><br><br>";   
                    }

                    if (!$showedProject) {
                        echo "<div class='alert'>Aucun projets trouvé avec la recherche '$searchTerm'.";
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>
    <script>
        function closepopuppub_in_project() {
            var popupdiscord = document.getElementById('pub_in_project');
            popupdiscord.classList.add('hide');
            setTimeout(function() {
                popupdiscord.style.display = 'none';
            });
        }

        function closepopupdiscord() {
            var popupdiscord = document.getElementById('popupdiscord');
            popupdiscord.classList.add('hide');
            setTimeout(function() {
                popupdiscord.style.display = 'none';
            }, 500);
        }

        function closeinfo() {
            var closeinfo = document.getElementById('popupinfo');
            closeinfo.classList.add('hide');
            setTimeout(function() {
                closeinfo.style.display = 'none';
            }, 1);
        }

        function closepub() {
            var popuppub = document.getElementById('popuppub');
            popuppub.classList.add('hide');
            setTimeout(function() {
                popuppub.style.display = 'none';
            }, 1);
        }

        function nevershowagaindiscord() {
            closepopupdiscord();
        
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('disable_popup_discord=1');
        }
    </script>
</body>
</html>
