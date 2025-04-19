<?php
require_once 'init.php';

if (isset($_POST['supr_signalement'])) {
    $sql = $pdo->prepare("DELETE FROM user_reports WHERE user_id_who_reported = :user_id");
    $sql->bindValue(":user_id", $myUser_id);
    $sql->execute();
    
    if ($sql->rowCount() > 0) {
        header("Location: $url");
        exit();
    } else {
        echo "<div class='alert'><h3>Erreur lors de la suppression du commentaire.</h3></div>";
    }                                       
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Sharing - Profil</title>
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
            <div class="profile">
                <?php
                    $targetPseudo = isset($_GET['pseudo']) ? htmlspecialchars($_GET['pseudo'], ENT_QUOTES, 'UTF-8') : '';
                    
                    $sql = $pdo->prepare("SELECT * FROM users WHERE pseudo = ?");
                    $sql->execute([$targetPseudo]);
                    $user = $sql->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                        $bio = $user['bio'];
                        $targetStatutInt = (int)$user['statut'];
                        $targetStatut = getStatutInt($targetStatutInt);
                        $targetRoleInt = (int)$user['role'];
                        $targetRole = getRoleInt($targetRoleInt);
                        $email = htmlspecialchars($user['email']);
                        $is_public = (int)$user['is_public'];
                        $ban = (int)$user['ban'];
                        $certified = (int)$user['certified'];
                        $targetUser_id = (int)$user['id'];
                        $targetIdChannelYt = $user['idChannelYt'];
                        $targetPremium = (int)$user['premium'];

                        $user_profile_url = '/profile?pseudo=' . urlencode($targetPseudo);

                        $sql2 = $pdo->prepare("SELECT id FROM abonnements WHERE user_id_who_abo = ? AND user_id = ?");
                        $sql2->execute([$myUser_id, $targetUser_id]);
                        $results = $sql2->fetchAll();

                        if ($results) {
                            $jesuisabo = true;
                        }

                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['abo']) && $_POST['abo'] === 'abo' && $myUser_id != $targetUser_id) {
                            if ($jesuisabo) {
                                // se désabonner
                                $sql = $pdo->prepare("DELETE FROM abonnements WHERE user_id = ? AND user_id_who_abo = ?");
                                $jesuisabo = false;

                                $log = insertLog(11, $myUser_id, $targetUser_id, $myPseudo, $targetPseudo);
                            } else {
                                // s'abonner
                                $sql = $pdo->prepare("INSERT INTO abonnements (user_id, user_id_who_abo) VALUES (?, ?)");
                                $jesuisabo = true;

                                $log = insertLog(10, $myUser_id, $targetUser_id, $myPseudo, $targetPseudo);
                            }

                            $sql->execute([$targetUser_id, $myUser_id]);
                        }

                        echo "<h1>Profil de $targetPseudo</h1>
                              <div class='user-profile'>";
                        
                        $logoDirectory = "/users/$targetUser_id/logo.png";
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $logoDirectory)) {
                            $logoDirectory = "/img/profil_logo_default.png";
                        }
                        echo "<img class='logo' src='$logoDirectory' alt='Logo'>";

                        echo "<p><strong>Pseudo:</strong>$targetPseudo";

                        if ($certified) {
                            echo '✔️';
                        }
                    
                        echo "<br><strong>Statut:</strong>$targetStatut<br><strong>Rôle:</strong>$targetRole<br>";

                        if ($is_public === 1) {
                            echo "<strong>Email:</strong>$email<br><strong>Confidentialité:</strong> Public</p>";
                        } else {
                            $email_parts = explode('@', $email);
                            $masked_email = str_repeat('*', strlen($email_parts[0])) . '@' . $email_parts[1];
                            echo "<strong>Email:</strong>$masked_email<br><strong>Confidentialité:</strong> Privé</p>";
                        }

                        $sql = $pdo->prepare("SELECT COUNT(*) FROM abonnements WHERE user_id = ?");
                        $sql->execute([$targetUser_id]);
                        $nb_abo = $sql->fetchColumn();
                        
                        if ($nb_abo < 2) {
                            echo "<h2>$nb_abo abonné</h2>";
                        } else {
                            echo "<h2>$nb_abo abonnés</h2>";
                        }

                        if ($myUser_id && $myUser_id != $targetUser_id) {
                            echo "
                                <form method='POST' action='$user_profile_url'>
                                    <input type='hidden' name='abo' value='abo'>
                                    <input type='hidden' name='users_id' value='$targetUser_id'>
                                    <input type='hidden' name='user_profile_url' value='$user_profile_url'>";

                            if ($jesuisabo == true) {
                                echo "<button type='submit'>Se désabonner</button>";
                                
                            } else {
                                echo "<button type='submit'>S'abonner</button>";
                            }

                            echo "</form><br><br>";
                        }

                        if ($bio) {
                            echo "<strong>Biographie:</strong>
                                  <br>
                                  <div class='ma_bio'>$bio</div>";
                        }

                        echo '<a href="/"><button>Accueil</button></a>
                              </div>';
                    } else {
                        echo "Utilisateur non trouvé.";
                    }

                    if ($myUser_id) {
                        if (($myStatutInt === $statutAdmin) && ($targetUser_id != $myUser_id) && ($targetStatutInt != $myStatutInt)) {
                            echo '<a href="usersettingsadmin?pseudo=' . urlencode($targetPseudo) . '">
                                    <button type="button">Paramètre admin</button>
                                </a>';
                        } elseif (($myStatutInt === $statutMdmin) && ($targetUser_id != $myUser_id) && ($targetStatutInt != $myStatutInt) && ($targetStatutInt != $statutAdmin)) {
                            echo '<a href="usersettingsmodo?pseudo=' . urlencode($targetPseudo) . '">
                                    <button type="button">Paramètre Modo</button>
                                </a>';
                        }

                        if ($targetStatutInt != $statutAdmin && $targetUser_id != $myUser_id) {
                            echo "<br>
                                    <div class='signaler'>
                                      <form method='POST' action='$user_profile_url'>
                                      <br>
                                      <label for='reason'>Raison du signalement :</label>
                                      <br>
                                      <textarea id='reason' name='reason' required></textarea>
                                      <button type='submit'>Signaler l'utilisateur</button>
                                    </form>
                                  </div>";
                        }
                    }

                    if (isset($_POST['reason']) && isset($_SESSION['user_id'])) {

                        $sql_check = $pdo->prepare("SELECT user_id_who_reported FROM user_reports WHERE reported_user_id = ? AND user_id_who_reported = ?");
                        $sql_check->execute([$targetUser_id, $myUser_id]);
                        $check = $sql_check->fetch(PDO::FETCH_ASSOC);

                        if ($check) {
                            echo '<div class="alert">Tu as déjà signalé cet utilisateur !</div>';
                        } else {
                            $reason = htmlspecialchars($_POST['reason']);

                            $sql_insert = $pdo->prepare("INSERT INTO user_reports (reported_user_id, user_id_who_reported, reason, report_date) VALUES (?, ?, ?, NOW())");
                            if (!$sql_insert->execute([$targetUser_id, $myUser_id, $reason])) {
                                echo "Erreur lors du signalement de l'utilisateur.";
                            }
                        }
                    }

                    $sql_reports = $pdo->prepare("SELECT user_id_who_reported, reason, report_date FROM user_reports WHERE reported_user_id = ?");
                    $sql_reports->execute([$targetUser_id]);
                    $reports = $sql_reports->fetchAll(PDO::FETCH_ASSOC);

                    if ($reports) {
                        echo '<h1><strong><u>Signalements :</u></strong></h1>';
                        foreach ($reports as $report) {
                            $formatted_date = date("d/m/Y", strtotime($report['report_date']));

                            $myUser_id_who_reported = $report['user_id_who_reported'];

                            $sql_pseudo = $pdo->prepare("SELECT pseudo FROM users WHERE id = ?");
                            $sql_pseudo->execute([$myUser_id_who_reported]);
                            $result_pseudo = $sql_pseudo->fetch(PDO::FETCH_ASSOC);

                            echo '<p>Signalé par <strong>' . htmlspecialchars($result_pseudo['pseudo']) . '</strong> le <u>' . $formatted_date . '</u> pour la raison : <br>' . htmlspecialchars($report['reason']) . '</p>';
                            
                            if ($myUser_id_who_reported == $myUser_id) {
                                echo "<form method='POST' action='$user_profile_url' enctype='multipart/form-data'>
                                        <input type='submit' name='supr_signalement' class='supr_signalement' value='Supprimer'>
                                      </form>";
                            }
                        } 
                    }

                    if (($targetStatutInt === $statutModo) && $targetIdChannelYt != null) {
                        echo "<div class='NatcodeYtVideo'>";
                        require_once "videoapi.php";
                        echo "</div>";
                    }
                ?>
            </div>
        </div>
    </main>
</body>
</html>
