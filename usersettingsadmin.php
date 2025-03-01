<?php
ob_start();
require_once 'init.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Sharing - Paramètres du compte (Admin)</title>
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
                    if ($myStatutInt !== $statutAdmin) {
                        location("/");
                    }

                    $user_pseudo_to_edit = isset($_GET['pseudo']) ? $_GET['pseudo'] : '';

                    if (empty($user_pseudo_to_edit)) {
                        echo "<div class='alert'><p>Pseudo utilisateur non valide.</p></div>";
                        exit();
                    }

                    $sql = $pdo->prepare("SELECT id, pseudo, email, statut, role, is_public, ban, certified, COALESCE(ban_reason, '') AS ban_reason FROM users WHERE pseudo = :pseudo");
                    $sql->execute(['pseudo' => $user_pseudo_to_edit]);
                    $result = $sql->fetch(PDO::FETCH_ASSOC);
                    if ($result) {
                        $targetUser_id = (int)$result['id'];
                        $targetPseudo = $result['pseudo'];
                        $email = $result['email'];
                        $targetStatutInt = (int)$result['statut'];
                        $is_public = (int)$result['is_public'];
                        $ban = (int)$result['ban'];
                        $ban_reason = $result['ban_reason'];
                        $certify = (int)$result['certified'];
                        $targetRoleInt = $result['role'];
                    } else {
                        echo "<div class='alert'><p>Utilisateur introuvable.</p></div>";
                        exit();
                    }

                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {

                        $sql = $pdo->prepare("INSERT INTO delete_account (user_id, date) VALUES (?, ?)");
                        if ($sql->execute([$targetUser_id, $current_date_time])) {

                            insertLog(4, $myUser_id, $targetUser_id);
                            
                            location("/");
                        } else {
                            echo "<div class='alert'>Erreur : " . implode(" ", $sql->errorInfo()). "</div>";
                        } 
                    }

                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action']) && $_POST['action'] !== 'delete_account') {
                        $new_pseudo = isset($_POST['pseudo']) ? htmlspecialchars($_POST['pseudo'], ENT_QUOTES) : $targetPseudo;
                        $new_is_public = isset($_POST['is_public']) ? intval($_POST['is_public']) : $is_public;
                        $new_email = isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES) : $email;

                        $test_log_ban = isset($_POST['ban']) ? intval($_POST['ban']) : $ban;
                        if ($ban != $test_log_ban) {
                            if ($ban > $test_log_ban) {
                                insertLog(8, $myUser_id, $targetUser_id);
                            } else if ($ban < $test_log_ban) {
                                insertLog(13, $myUser_id, $targetUser_id);
                            }
                        }
                        $ban = $test_log_ban;

                        $ban_reason = ($ban == 1) ? (isset($_POST['ban_reason']) ? htmlspecialchars($_POST['ban_reason'], ENT_QUOTES) : '') : null;
                        $new_targetStatutInt = isset($_POST['statut']) ? intval($_POST['statut']) : $targetStatutInt;
                        $new_targetRole = isset($_POST['role']) ? intval($_POST['role']) : $targetRoleInt;
                        $new_certify = isset($_POST['certify']) ? intval($_POST['certify']) : $certify;

                        if (strlen($new_pseudo) > 20 || !preg_match('/^[a-zA-Z0-9_-]+$/', $new_pseudo)) {
                            $errors['pseudo'] = "<div class='alert'>Votre pseudo doit contenir au maximum 10 caractères et ne doit inclure que des lettres, des chiffres, des underscores ou des tirets.</div>";
                        } else {
                            $sql = $pdo->prepare("SELECT id FROM users WHERE pseudo = :pseudo AND id != :id");
                            $sql->execute(['pseudo' => $new_pseudo, 'id' => $targetUser_id]);
                            if ($sql->rowCount() > 0) {
                                $errors['pseudo'] = "<div class='alert'>Le pseudo est déjà utilisé.</div>";
                            }
                        }

                        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                            $errors['email'] = "<div class='alert'>Adresse email invalide.</div>";
                        } else {
                            $sql = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
                            $sql->execute(['email' => $new_email, 'id' => $targetUser_id]);
                            if ($sql->rowCount() > 0) {
                                $errors['email'] = "<div class='alert'>L'email est déjà utilisé.</div>";
                            }
                        }

                        if (empty($errors)) {
                            $update_query = "UPDATE users SET pseudo = :pseudo, email = :email, certified = :certified, is_public = :is_public, ban = :ban, statut = :statut, role = :role, ban_reason = :ban_reason WHERE id = :id";
                            $sql_update = $pdo->prepare($update_query);
                            $sql_update->execute([
                                'pseudo' => $new_pseudo,
                                'certified' => $new_certify,
                                'email' => $new_email,
                                'is_public' => $new_is_public,
                                'ban' => $ban,
                                'statut' => $new_targetStatutInt,
                                'role' => $new_targetRole,
                                'ban_reason' => $ban_reason,
                                'id' => $targetUser_id
                            ]);
                            location("/");
                        }
                    }
                    ob_end_flush();
                ?>
                <h1>Modifier les paramètres de l'utilisateur</h1>
                <form method="POST">
                    <label for="pseudo">Pseudo :</label>
                    <br>
                    <input type="text" name="pseudo" id="pseudo" value="<?php echo htmlspecialchars($targetPseudo, ENT_QUOTES); ?>" required>
                    <?php if (isset($errors['pseudo'])) echo $errors['pseudo']; ?>
                    <br><br>

                    <label for="email">Email :</label>
                    <br>
                    <input type="text" name="email" id="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>" required>
                    <?php if (isset($errors['email'])) echo $errors['email']; ?>
                    <br><br>

                    <label for="is_public">Confidentialité :</label>
                    <br>
                    <select name="is_public" id="is_public">
                        <option value="1" <?php if ($is_public == 1) echo 'selected'; ?>>Public</option>
                        <option value="0" <?php if ($is_public == 0) echo 'selected'; ?>>Privé</option>
                    </select>
                    <br><br>

                    <label for="statut">Statut :</label>
                    <br>
                    <select name="statut" id="statut">
                        <option value="1" <?php if ($targetStatutInt == 1) echo 'selected'; ?>>Utilisateur</option>
                        <option value="2" <?php if ($targetStatutInt == 2) echo 'selected'; ?>>Modérateur</option>
                        <option value="3" <?php if ($targetStatutInt == 3) echo 'selected'; ?>>Administrateur</option>
                    </select>
                    <br><br>

                    <label for="role">Role :</label>
                    <br>
                    <select name="role">
                        <option value="0" <?php if ($targetRoleInt == 0) echo 'selected'; ?>><?php echo getRoleInt(0); ?></option>
                        <option value="1" <?php if ($targetRoleInt == 1) echo 'selected'; ?>><?php echo getRoleInt(1); ?></option>
                        <option value="2" <?php if ($targetRoleInt == 2) echo 'selected'; ?>><?php echo getRoleInt(2); ?></option>
                        <option value="3" <?php if ($targetRoleInt == 3) echo 'selected'; ?>><?php echo getRoleInt(3); ?></option>
                        <option value="4" <?php if ($targetRoleInt == 4) echo 'selected'; ?>><?php echo getRoleInt(4); ?></option>
                        <option value="5" <?php if ($targetRoleInt == 5) echo 'selected'; ?>><?php echo getRoleInt(5); ?></option>
                        <option value="6" <?php if ($targetRoleInt == 6) echo 'selected'; ?>><?php echo getRoleInt(6); ?></option>
                        <option value="7" <?php if ($targetRoleInt == 7) echo 'selected'; ?>><?php echo getRoleInt(7); ?></option>
                        <option value="8" <?php if ($targetRoleInt == 8) echo 'selected'; ?>><?php echo getRoleInt(8); ?></option>
                        <option value="9" <?php if ($targetRoleInt == 9) echo 'selected'; ?>><?php echo getRoleInt(9); ?></option>
                        <option value="10" <?php if ($targetRoleInt == 10) echo 'selected'; ?>><?php echo getRoleInt(10); ?></option>
                        <option value="11" <?php if ($targetRoleInt == 11) echo 'selected'; ?>><?php echo getRoleInt(11); ?></option>
                        <option value="12" <?php if ($targetRoleInt == 12) echo 'selected'; ?>><?php echo getRoleInt(12); ?></option>
                        <option value="13" <?php if ($targetRoleInt == 13) echo 'selected'; ?>><?php echo getRoleInt(13); ?></option>
                        <option value="14" <?php if ($targetRoleInt == 14) echo 'selected'; ?>><?php echo getRoleInt(14); ?></option>
                    </select>
                    <br><br>

                    <label for="certify">Certification :</label>
                    <br>
                    <select name="certify" id="certify">
                        <option value="1" <?php if ($certify == 1) echo 'selected'; ?>>Certifié</option>
                        <option value="0" <?php if ($certify == 0) echo 'selected'; ?>>Non certifié</option>
                    </select>
                    <br><br>

                    <label for="ban">Ban :</label>
                    <br>
                    <select name="ban" id="ban" onchange="toggleBanReason()">
                        <option value="1" <?php if ($ban == 1) echo 'selected'; ?>>Banni</option>
                        <option value="0" <?php if ($ban == 0) echo 'selected'; ?>>Non banni</option>
                    </select>
                    <br>

                    <div class="ban_reason" style="display: <?php echo $ban == 1 ? 'block' : 'none'; ?>;">
                        <label for="ban_reason">Raison du bannissement :</label>
                        <br>
                        <textarea id="ban_reason" name="ban_reason"><?php echo htmlspecialchars($ban_reason, ENT_QUOTES); ?></textarea>
                    </div>
                    <br><br>

                    <button type="submit">Mettre à jour</button>
                </form>
                <div class='delete_account'>
                    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer le compte ?');">
                        <input type="hidden" name="action" value="delete_account">
                        <button type="submit">Supprimer le compte</button>
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
