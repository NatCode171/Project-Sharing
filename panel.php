<?php
    require_once 'init.php';

    if ($myStatutInt === $statutAdmin) {
        $jesuisunadmin = true;
    } elseif ($myStatutInt === $statutModo) {
        $jesuisunmodo = true;
    } else {
        location("/");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
        // met le message dans la db
        $message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
        $stmt_message = $pdo->prepare("INSERT INTO moderation (user_id, message) VALUES (?, ?)");
        if (!$stmt_message->execute([$myUser_id, $message])) {
            echo "<div class='alert'>Erreur lors de l'insertion.</div>";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'supr_message') {
        $id_message = $_POST['id_message'];
        $user_id_message = $_POST['user_id_message'];

        if ($user_id_message === $myUser_id || $jesuisunadmin) {
            $stmt = $pdo->prepare("DELETE FROM moderation WHERE id = :id");
            $stmt->bindValue(":id", $id_message);
            $stmt->execute();
                
            if (!$stmt->rowCount() > 0) {
                echo "<div class='alert'>Erreur lors de la suppression du message.</div>";
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {
        if ($jesuisunadmin && !$jesuisunmodo) {

            $targetUser_id = (int)$_POST['user_id'];
            
            $pdo->beginTransaction();
            
            try {

                $log = insertLog(6, $myUser_id, $targetUser_id);
                
                // Suppression de l'utilisateur
                $sql_delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $sql_delete->execute([$targetUser_id]);
    
                // Suppression de l'enregistrement de delete_account
                $sql_delete_deleted = $pdo->prepare("DELETE FROM delete_account WHERE user_id = ?");
                $sql_delete_deleted->execute([$targetUser_id]);
                                    
                // Suppression des abonnements
                $sql_delete_abo = $pdo->prepare("DELETE FROM abonnements WHERE user_id = ?");
                $sql_delete_abo->execute([$targetUser_id]);
                $sql_delete_abo2 = $pdo->prepare("DELETE FROM abonnements WHERE user_id_who_abo = ?");
                $sql_delete_abo2->execute([$targetUser_id]);
                
                // Suppression des rapports liés à l'utilisateur
                $delete_reports_query = $pdo->prepare("DELETE FROM user_reports WHERE user_id_who_reported = ?");
                $delete_reports_query->execute([$targetUser_id]);
                
                // Suppression des fichiers et du répertoire utilisateur
                $userDir = $_SERVER['DOCUMENT_ROOT'] . "/users/$targetUser_id";
                if (is_dir($userDir)) {
                    $files = glob("$userDir/*");
                    foreach ($files as $file) {
                        if (is_file($file) && !unlink($file)) {
                            throw new Exception('Erreur lors de la suppression du fichier : ' . $file);
                        }
                    }
                    if (!rmdir($userDir)) {
                        throw new Exception('Erreur lors de la suppression du dossier : ' . $userDir);
                    }
                }
                
                // Validation de la transaction
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e; // Relance l'exception pour un traitement global
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'disable_account') {
        $targetUser_id = $_POST['user_id'];
        $current_date = new DateTime();
        $disable_account_query = $pdo->prepare("INSERT INTO delete_account (user_id, date) VALUES (?, ?)");
        $disable_account_query->execute([$targetUser_id, $current_date->format('Y-m-d H:i:s')]);

        $log = insertLog(4, $myUser_id, $targetUser_id);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restore_account') {

        $log = insertLog(7, $myUser_id, $targetUser_id);

        $targetUser_id = $_POST['user_id'];
        $delete_deleted_query = $pdo->prepare("DELETE FROM delete_account WHERE user_id = ?");
        $delete_deleted_query->execute([$targetUser_id]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unban_account') {

        $log = insertLog(8, $myUser_id, $targetUser_id);

        $targetUser_id = $_POST['user_id'];
        $unban_account_query = $pdo->prepare("UPDATE users SET ban = 0, ban_reason = '' WHERE id = ?");
        $unban_account_query->execute([$targetUser_id]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'settingswebsite') {

        $log = insertLog(9, $myUser_id, null);

        $new_stopsite = isset($_POST['stopsite']) ? (int)$_POST['stopsite'] : (int)$stopsite;
        $new_info_ou_pub = isset($_POST['info_ou_pub']) ? (int)$_POST['info_ou_pub'] : (int)$info_ou_pub;
        $new_is_public_de_base = isset($_POST['is_public_de_base']) ? (int)$_POST['is_public_de_base'] : (int)$is_public_de_base;
        $new_vwebsite = isset($_POST['vwebsite']) ? trim($_POST['vwebsite']) : $vwebsite;
        $new_vconditionsutilisation = isset($_POST['vconditionsutilisation']) ? trim($_POST['vconditionsutilisation']) : $vconditionsutilisation;
        $new_maintenance_end = isset($_POST['maintenance_end']) && !empty($_POST['maintenance_end']) ? $_POST['maintenance_end'] : null;
    
        try {
            $sql = $pdo->prepare("UPDATE administration SET stopsite = ?, info_ou_pub = ?, is_public_de_base = ?, vwebsite = ?, vconditionsutilisation = ?, stopsite_maintenance_end = ? WHERE id = 1");
            $success = $sql->execute([
                $new_stopsite, 
                $new_info_ou_pub, 
                $new_is_public_de_base, 
                $new_vwebsite, 
                $new_vconditionsutilisation, 
                $new_maintenance_end
            ]);
    
            if (!$success) {
                echo "<div class='alert'>Une erreur s'est produite lors de la mise à jour des paramètres d'administration.</div>";
                exit();
            }

            if ($new_info_ou_pub == 0) {
                $stmt = $pdo->prepare("UPDATE users SET info_ou_pub = 0");
                if (!$stmt->execute()) {
                    $errorInfo = $stmt->errorInfo();
                    echo "<div class='alert'>Erreur lors de la réinitialisation : " . $errorInfo[2] . "</div>";
                    exit();
                }
            }
            
            header("Location: $url");
            exit();
        } catch (PDOException $e) {
            echo "Erreur de base de données : " . $e->getMessage();
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset="utf-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Project Sharing - Panel</title>
    <link rel='icon' href='/img/Logo_Project-Sharing.png' type='image/png'>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <main>
        <?php
        require_once 'nav.php';
        ?>
        <div class="main">
            <h1>Modifier les informations du site :</h1>
            <div class='formulairemaispassupr'>
                <form method="post" action='panel'>
                    <input type='hidden' name='action' value='settingswebsite'>
                    <fieldset>
                        <legend>Paramètres du site</legend>
                        <br>
                        <label for="stopsite">Statut du site :</label>
                        <select name="stopsite" id="stopsite">
                            <option value="1" <?php echo ($stopsite === 1) ? 'selected' : ''; ?>>Arrêt</option>
                            <option value="0" <?php echo ($stopsite === 0) ? 'selected' : ''; ?>>Marche</option>
                        </select>
                        <br>
                        <label for="maintenance_end">Date de fin de maintenance (ne mettez rien si pas de fin):</label>
                        <input type="datetime-local" id="maintenance_end" name="maintenance_end" value="<?php echo isset($maintenance_end) ? $maintenance_end : null; ?>">
                        <br>
                        <label for="info_ou_pub">Popup info ou pub :</label>
                        <select name="info_ou_pub" id="info_ou_pub">
                            <option value="0" <?php echo ($info_ou_pub === 0) ? 'selected' : ''; ?>>Rien</option>
                            <option value="1" <?php echo ($info_ou_pub === 1) ? 'selected' : ''; ?>>Pub</option>
                            <option value="2" <?php echo ($info_ou_pub === 2) ? 'selected' : ''; ?>>Info</option>
                        </select>
                        <br>
                        <label for="is_public_de_base">Is public de base :</label>
                        <select name="is_public_de_base" id="is_public_de_base">
                            <option value="1" <?php echo ($is_public_de_base === 1) ? 'selected' : ''; ?>>Public</option>
                            <option value="0" <?php echo ($is_public_de_base === 0) ? 'selected' : ''; ?>>Privé</option>
                        </select>
                    </fieldset>

                    <label for="vwebsite">Site Web :</label>
                    <input type="text" id="vwebsite" name="vwebsite" value="<?php echo $vwebsite; ?>">

                    <label for="vconditionsutilisation">Conditions d'utilisation :</label>
                    <textarea id="vconditionsutilisation" name="vconditionsutilisation"><?php echo $vconditionsutilisation; ?></textarea>

                    <br>
                    <button type="submit">Mettre à jour</button>
                </form>
            </div>
            <br>
            <h1>Comptes en cours de suppression :</h1>
            <?php
                function afficherUtilisateur($targetUser_id, $targetPseudo, $targetStatut, $logoDirectory, $actions = false, $ban = false, $ban_reason = false) {

                    global $jesuisunadmin, $jesuisunmodo;

                    $user_profile_url = '/profile?pseudo=' . urlencode($targetPseudo);
                    echo '<div class="user-profile">';
                    echo "<a href='$user_profile_url' title='Voir le profil de $targetPseudo'>
                            <img class='logo' src='$logoDirectory' alt='Logo'>
                        </a>";
                    echo "<a href='$user_profile_url' title='Voir le profil de $targetPseudo'>
                            <p><strong>$targetPseudo</strong><br>($targetStatut)<br></p>";
                        echo "</a>";

                    if ($actions) {
                        if ($jesuisunadmin && !$jesuisunmodo) {
                            echo "<div class='delete_account'>
                                <form method='POST' action='panel' onsubmit=\"return confirm('Êtes-vous sûr de vouloir supprimer le compte ?');\">
                                    <input type='hidden' name='action' value='delete_account'>
                                    <input type='hidden' name='user_id' value='$targetUser_id'>
                                    <button type='submit'>Supprimer le compte</button>
                                </form>
                            </div>";
                        }
                        echo "<div class='restore_account'>
                                <form method='POST' action='panel' onsubmit=\"return confirm('Êtes-vous sûr de vouloir récupérer le compte ?');\">
                                    <input type='hidden' name='action' value='restore_account'>
                                    <input type='hidden' name='user_id' value='$targetUser_id'>
                                    <button type='submit'>Récupérer le compte</button>
                                </form>
                            </div>";
                    } else {
                        if ($ban) {
                            if ($ban_reason) {
                                echo "<p class='ban-raison'>Raison :<br><strong>$ban_reason</strong></p>";
                            }
                            echo "<div class='delete_account'>
                                <form method='POST' action='panel' onsubmit=\"return confirm('Êtes-vous sûr de vouloir supprimer le compte ?');\">
                                    <input type='hidden' name='action' value='disable_account'>
                                    <input type='hidden' name='user_id' value='$targetUser_id'>
                                    <button type='submit'>Supprimer le compte</button>
                                </form>
                            </div>
                            <div class='unban_account'>
                                    <form method='POST' action='panel' onsubmit=\"return confirm('Êtes-vous sûr de vouloir débannir le compte ?');\">
                                        <input type='hidden' name='action' value='unban_account'>
                                        <input type='hidden' name='user_id' value='$targetUser_id'>
                                        <button type='submit'>Débannir</button>
                                    </form>
                                </div>";
                        }
                    }
                    echo '</div>';
                }

                // Comptes en cours de suppression
                $requete = "SELECT u.id, u.pseudo, u.statut, u.ban, u.ban_reason, d.id AS delete_id 
                            FROM users u 
                            JOIN delete_account d ON u.id = d.user_id";
                $stmt = $pdo->query($requete);

                if ($stmt->rowCount() > 0) {
                    echo '<div class="users-container">';
                    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $targetUser_id = (int)$ligne['id'];
                        $targetPseudo = htmlspecialchars($ligne['pseudo']);
                        $ban = (int)$ligne['ban'];
                        $ban_reason = $ligne['ban_reason'];
                        $targetStatutInt = (int)$ligne['statut'];
                        $targetStatut = getStatutInt($targetStatutInt);
                        $logoDirectory = getLogo($targetUser_id);
                        afficherUtilisateur($targetUser_id, $targetPseudo, $targetStatut, $logoDirectory, true, $ban, $ban_reason);
                    }
                    echo '</div>';
                } else {
                    echo "Aucun utilisateur en cours de suppression.";
                }

                echo "<h1>Comptes bannis :</h1>";
                $requete = "SELECT id, pseudo, statut, ban, ban_reason FROM users WHERE ban = 1";
                $stmt = $pdo->query($requete);

                if ($stmt->rowCount() > 0) {
                    echo '<div class="users-container">';
                    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $targetUser_id = (int)$ligne['id'];
                        $targetPseudo = htmlspecialchars($ligne['pseudo']);
                        $ban = (int)$ligne['ban'];
                        $ban_reason = $ligne['ban_reason'];
                        $targetStatutInt = (int)$ligne['statut'];
                        $targetStatut = getStatutInt($targetStatutInt);
                        $logoDirectory = getLogo($targetUser_id);
                        afficherUtilisateur($targetUser_id, $targetPseudo, $targetStatut, $logoDirectory, false, $ban, $ban_reason);
                    }
                    echo '</div>';
                } else {
                    echo "Aucun utilisateur banni.";
                }
                echo "<h1>Messages pour les modérateurs :</h1>";
                
                $requete = "SELECT user_id, message, id FROM moderation";
                $stmt = $pdo->query($requete);

                if ($stmt) {
                    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $user_id_message = (int)$ligne['user_id'];
                        $message = $ligne['message'];
                        $id_message = (int)$ligne['id'];

                        $requete2 = "SELECT pseudo FROM users WHERE id = ?";
                        $stmt2 = $pdo->prepare($requete2);

                        if ($stmt2->execute([$user_id_message])) {
                            $ligne2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                            if ($ligne2) {
                                $pseudo = $ligne2['pseudo'];

                                echo "<div class='ma_bio'>";
                                echo "<h3>Message de $pseudo :</h3>";
                                echo "<div style='white-space: pre-wrap;'><p>$message</p></div>";
                                if ($user_id_message == $myUser_id || $jesuisunadmin == 1) {
                                    echo "<form action='panel' method='POST'>";
                                    echo "<input type='hidden' name='id_message' value='$id_message'>";
                                    echo "<input type='hidden' name='user_id_message' value='$user_id_message'>";
                                    echo '<button type="submit" name="action" value="supr_message" class="supr_project">Supprimer</button>';
                                    echo "</form>";
                                }
                                echo "</div>";
                            }
                        }
                    }
                } else {
                    echo "Erreur dans la requête principale.";
                }

                ?>
                <h2>Voici les logs :</h2>
                <?php
                $requete = "SELECT * FROM logs";
                $sql = $pdo->query($requete);

                if ($sql) {
                    echo "<div class='ma_bio'>";
                    while ($ligne = $sql->fetch(PDO::FETCH_ASSOC)) {
                        $command = (int)$ligne['command'];
                        $user_id = (int)$ligne['user_id'];
                        $targetUser_id = isset($ligne['targetUser_id']) ? (int)$ligne['targetUser_id'] : null;

                        $pseudo_sos_1 = isset($ligne['pseudo_sos_1']) ? $ligne['pseudo_sos_1'] : null;
                        $pseudo_sos_2 = isset($ligne['pseudo_sos_2']) ? $ligne['pseudo_sos_2'] : null;

                        $page = $ligne['page'];
                        
                        // beau bordel pour la date au bon format
                        $date_mt = DateTime::createFromFormat('Y-m-d H:i:s', $ligne['date']);
                        $date = $date_mt->format('Y-m-d H:i:s');

                        $log = getLog($command, $user_id, $targetUser_id, $date, $page, $pseudo_sos_1, $pseudo_sos_2);
                        echo $log . "<br>";
                    }
                    echo "</div>";
                } else {
                    echo "Erreur dans la requête des logs.";
                }

            ?>
            <div class='formulairemaispassupr'>
                <form method='POST' action='panel'>
                    <label for='message'>Ajouter un message :</label>
                    <br>
                    <div class="panel_message"><textarea id='panel_message' name='message'></textarea></div>
                    <button type='submit'>Envoyer</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
