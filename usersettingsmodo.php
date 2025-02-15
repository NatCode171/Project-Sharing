<?php
require_once 'init.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Sharing - Paramètres du compte (Modo)</title>
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
            <div class="usersettings">
                <?php
                if ($myStatutInt !== $statutModo) {
                    location("/");
                }

                $user_pseudo_to_edit = isset($_GET['pseudo']) ? $_GET['pseudo'] : '';

                if (empty($user_pseudo_to_edit)) {
                    echo "<div class='alert'><p>Pseudo utilisateur non valide.</p></div>";
                    exit();
                }

                $stmt = $pdo->prepare("SELECT id, pseudo, email, is_public, ban, COALESCE(ban_reason, '') AS ban_reason FROM users WHERE pseudo = :pseudo");
                $stmt->execute(['pseudo' => $user_pseudo_to_edit]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $myUser_id_to_edit = $row['id'];
                    $pseudo = $row['pseudo'];
                    $email = $row['email'];
                    $is_public = $row['is_public'];
                    $ban = $row['ban'];
                    $ban_reason = $row['ban_reason'];
                } else {
                    echo "<div class='alert'><p>Utilisateur introuvable.</p></div>";
                    exit();
                }

                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {

                    $stmt = $pdo->prepare("INSERT INTO delete_account (user_id, date) VALUES (?, ?)");
                    if ($stmt->execute([$myUser_id_to_edit, $current_date_time])) {

                        insertLog(4, $myUser_id, $targetUser_id);
                        
                        location("/");
                    } else {
                        echo "<div class='alert'> Erreur : " . implode(" ", $stmt->errorInfo()) ."</div>";
                    } 
                }

                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signalement') {
                    $stmt_check = $pdo->prepare("SELECT user_id_who_reported FROM user_reports WHERE reported_user_id = ? AND user_id_who_reported = ? AND modo = ?");
                    $stmt_check->execute([$myUser_id_to_edit, $myUser_id, 1]);
                    $check = $stmt_check->fetch(PDO::FETCH_ASSOC);

                    if ($check) {
                        echo '<div class="alert">Tu as déjà signalé cet utilisateur !</div>';
                    } else {
                        $reason = htmlspecialchars($_POST['reason']);

                        $stmt_insert = $pdo->prepare("INSERT INTO user_reports (reported_user_id, user_id_who_reported, reason, report_date) VALUES (?, ?, ?, NOW())");
                        if (!$stmt_insert->execute([$myUser_id_to_edit, $myUser_id, $reason])) {
                            echo "<div class='alert'>Erreur lors du signalement de l'utilisateur.</div>";
                        }
                    }
                }

                if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action']) && $_POST['action'] !== 'delete_account') {

                    $test_log_ban = isset($_POST['ban']) ? intval($_POST['ban']) : $ban;
                    if ($ban != $test_log_ban)  {
                        if ($ban > $test_log_ban) {
                            insertLog(8, $myUser_id, $targetUser_id);
                        } else if ($ban < $test_log_ban) {
                            insertLog(13, $myUser_id, $targetUser_id);
                        }
                    }
                    $ban = $test_log_ban;

                    $ban_reason = ($ban == 1) ? (isset($_POST['ban_reason']) ? htmlspecialchars($_POST['ban_reason'], ENT_QUOTES) : '') : null;

                    $update_query = "UPDATE users SET ban = :ban, ban_reason = :ban_reason WHERE id = :id";
                    $stmt_update = $pdo->prepare($update_query);
                    $stmt_update->execute([
                        'ban' => $ban,
                        'ban_reason' => $ban_reason,
                        'id' => $myUser_id_to_edit
                    ]);
                    location("/");

                }
                ?>
                <h1>Modifier les paramètres de l'utilisateur</h1>
                <form method="POST">

                    <label for="statut">Statut :</label>
                    <br>
                    <select name="statut" id="statut">
                        <option value="1" <?php if ($MyStatut == 2) echo 'selected'; ?>>Utilisateur</option>
                        <option value="2" <?php if ($MyStatut == 4) echo 'selected'; ?>>Programmeur</option>
                    </select>
                    <br><br>

                    <label for="ban">Ban :</label>
                    <br>
                    <select name="ban" id="ban" onchange="toggleBanReason()">
                        <option value="1" <?php if ($ban == 1) echo 'selected'; ?>>Banni</option>
                        <option value="0" <?php if ($ban == 0) echo 'selected'; ?>>Non banni</option>
                    </select>
                    <br><br>

                    <div class="ban_reason" style="display: <?php echo $ban == 1 ? 'block' : 'none'; ?>;">
                        <label for="ban_reason">Raison du bannissement :</label>
                        <br>
                        <textarea id="ban_reason" name="ban_reason"><?php echo htmlspecialchars($ban_reason, ENT_QUOTES); ?></textarea>
                    </div>
                    <br>

                    <button type="submit">Mettre à jour</button>
                </form>

                <div class='delete_account'>
                    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer le compte ?');">
                        <input type="hidden" name="action" value="delete_account">
                        <button type="submit">Supprimer le compte</button>
                    </form>
                </div>

                <br>
                <div class="signaler">
                    <form method="POST">
                        <br>
                        <label for="reason">Raison du signalement :</label>
                        <br>
                        <textarea id="reason" name="reason" value="signalement" required></textarea>
                        <button type="submit">Signaler l\'utilisateur</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <script>
        function toggleBanReason() {
            var banSelect = document.getElementById('ban');
            var banReasonDiv = document.querySelector('.ban_reason');
            var banReasonTextarea = document.getElementById('ban_reason');

            if (banSelect.value == "1") {
                banReasonDiv.style.display = 'block';
                banReasonTextarea.setAttribute('required', 'required');
            } else {
                banReasonDiv.style.display = 'none';
                banReasonTextarea.removeAttribute('required');
                banReasonTextarea.value = '';
            }
        }
    </script>
</body>
</html>
