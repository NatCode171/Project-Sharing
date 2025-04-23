<?php
ob_start();
require_once 'init.php';
    
if (isset($_POST['confirmation']) && $_POST['confirmation'] === 'yes') {
    $delete_deleted_query = $pdo->prepare("DELETE FROM delete_account WHERE user_id = ?");
    $delete_deleted_query->execute([$myUser_id]);
    header('Location: /');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Sharing - Login</title>
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
            <div class="login">
                <form method="POST" action="login" enctype="multipart/form-data" id="login-form">
                    <h1>Se connecter :</h1>
                    <br>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                        $targetEmail = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
                        $targetPassword = $_POST['password'];
                            
                        if (filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
                            $targetEmail = trim(strtolower($targetEmail));
                        } else {
                            $loginErrors['email'] = "<div class='alert'><p>Email non valide.</p></div>";
                        }

                        $sql = $pdo->prepare("SELECT pseudo, id, password, ban, ban_reason FROM users WHERE email = ?");
                        $sql->execute([$targetEmail]);
                        $result = $sql->fetch(PDO::FETCH_ASSOC);

                        if ($result) {
                            $targetPseudo = $result['pseudo'];
                            $myUser_id = (int)$result['id'];
                            $hashed_password = $result['password'];
                            $ban = (int)$result['ban'];
                            $ban_reason = $result['ban_reason'];

                            if (password_verify($targetPassword, $hashed_password)) {
                                $sql = $pdo->prepare("SELECT nb, timestamp FROM checklogin WHERE email = ?");
                                $sql->execute([$targetEmail]);
                                $result = $sql->fetch(PDO::FETCH_ASSOC);

                                if ($result) {
                                    $currentTime = time();
                                    $nb = (int)$result['nb'];
                                    $lastAttempt = strtotime($result['timestamp']);

                                    if ($nb >= 3 && ($currentTime - $lastAttempt) <= 15 * 60) {
                                        $remainingTime = 15 - floor(($currentTime - $lastAttempt) / 60);
                                        $loginErrors['password'] = "<div class='alert'>Trop de tentatives. Réessayez dans $remainingTime minutes.</div>";

                                        $subject = "Tentatives de connexion échouées sur Project-Sharing.";
                                        $content = "Bonjour $targetPseudo,

Nous avons détecté plusieurs tentatives de connexion échouées sur votre compte Project-Sharing.fr.to.
Pour votre sécurité, nous vous conseillons également de modifier votre mot de passe si vous soupçonnez une tentative d'accès non autorisée à votre compte.

Si vous avez des questions ou si vous avez besoin d'assistance, n'hésitez pas à nous contacter à l'adresse suivante : natcode@project-sharing.fr.to.

Cordialement,
L'équipe Project-Sharing.";
                                        sendmail("info@project-sharing.fr.to", $targetEmail, $subject, $content);
                                    } elseif ($nb >= 3 && ($currentTime - $lastAttempt) >= 15 * 60) {
                                        $sql = $pdo->prepare("DELETE FROM checklogin WHERE email = ?");
                                        $sql->execute([$targetEmail]);
                                        $nb = 0;
                                    }
                                } else {
                                    if ($ban) {
                                        die("<div class='alert'><strong>Vous avez été banni de Project Sharing, voici la ou les raison(s) :</strong><br><br>$ban_reason</div>");
                                    }
                                }

                                $requete2 = "SELECT id FROM delete_account WHERE user_id = ?";
                                $sql2 = $pdo->prepare($requete2);

                                if ($sql2->execute([$myUser_id])) {
                                    if ($sql2->rowCount() > 0) {
                                        echo '<form method="post">
                                                <p>Votre compte est marqué pour suppression. Êtes-vous sûr de vouloir vous connecter ?</p>
                                                <p>Si vous vous connecter, votre compte ne sera plus en cour de supression !</p>
                                                <button type="submit" name="confirmation" value="yes">Oui</button>
                                                <a href="/">Non</a>
                                            </form>';
                                        exit();
                                    }
                                }

                                $_SESSION['user_id'] = $myUser_id;
                                $_SESSION['pseudo'] = $targetPseudo;
                                $_SESSION['password'] = $hashed_password;

                                insertLog(1, $myUser_id, null);

                                header('Location: /');
                                exit();
                            } else {
                                    $sql = $pdo->prepare("
                                        INSERT INTO checklogin (email, nb, timestamp)
                                        VALUES (?, 1, NOW())
                                        ON DUPLICATE KEY UPDATE
                                            nb = nb + 1,
                                            timestamp = NOW()
                                    ");
                                    $sql->execute([$targetEmail]);

                                    $loginErrors['password'] = "<div class='alert'>Mot de passe incorrect.</div>";
                            }
                        } else {
                            $loginErrors['email'] = "<div class='alert'>Email non valide.</div>";
                        }
                    }
                    ob_end_flush();
                    ?>
                    <label for="email">Votre Email :</label>
                    <br>
                    <input type="text" id="email" name="email" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                    <?php if (isset($loginErrors['email'])) { echo $loginErrors['email']; } ?>
                    <br>

                    <label for="password">Mot de passe :</label>
                    <br>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required>
                        <span class="toggle-password">
                            <img src="/img/eye-icon-close.png" alt="Afficher/Masquer le mot de passe">
                        </span>
                    </div>
                    <?php if (isset($loginErrors['password'])) { echo $loginErrors['password']; } ?>
                    <br>

                    <button type="submit">Envoyer</button>

                    <br>
                    <a href="/forgotpassword">Mot de passe oublié ?</a>
                </form>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const togglePassword = document.querySelector('.toggle-password');
            const passwordField = document.querySelector('#password');
            
            togglePassword.addEventListener('click', () => {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
                const icon = type === 'password' ? '/img/eye-icon-close.png' : '/img/eye-icon-open.png';
                togglePassword.querySelector('img').setAttribute('src', icon);
            });
        });
    </script>
</body>
</html>
