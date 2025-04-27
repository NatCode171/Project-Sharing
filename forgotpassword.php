<?php
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $targetEmail = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        
    if (filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
        $targetEmail = trim(strtolower($targetEmail));
    } else {
        $errorEmail = "<div class='alert'>Email non valide.</div>";
    }

    $sql = $pdo->prepare("SELECT pseudo, id, password, ban, ban_reason FROM users WHERE email = ?");
    $sql->execute([$targetEmail]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $targetPseudo = $result['pseudo'];
        $targetUser_id = (int)$result['id'];
        $ban = (int)$result['ban'];
        $ban_reason = $result['ban_reason'];

        $sql = $pdo->prepare("SELECT nb, timestamp FROM sendforgotpassword WHERE email = ?");
        $sql->execute([$targetEmail]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $nb = (int)$result['nb'];
            $lastAttempt = strtotime($result['timestamp']);
            $timeDiff = $current_time - $lastAttempt;                

            if ($nb >= 1 && ($timeDiff) <= 60) {
                $remainingTime = 60 - $timeDiff;
                $errorMessage = "<div class='alert'>Trop de tentatives. Réessayez dans $remainingTime secondes.</div>";
                $isGood_forSendEmail = False; // Par défaut c'est True dans le init
            } elseif ($nb >= 1 && ($current_time - $lastAttempt) >= 60) {
                $pdo->beginTransaction();
                // Réinitialisation des tentatives
                $pdo->prepare("DELETE FROM sendforgotpassword WHERE email = ?")->execute([$targetEmail]);
                // Ajout de la nouvelle entrée
                $pdo->prepare("INSERT INTO sendforgotpassword (email, nb, timestamp) VALUES (?, 1, NOW())")->execute([$targetEmail]); 
                $pdo->commit();
            }
        }
        if ($ban) {
            $errorMessage = "<div class='alert'><strong>Vous avez été banni de Project Sharing, voici la ou les raison(s) :</strong><br><br>$ban_reason</div>";
        } else {
            if ($isGood_forSendEmail) {

                $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                $url .= "://".$_SERVER['HTTP_HOST']; // si on est sur dev. ou pas

                $nbpass = rand(100000, 999999); // Code de vérification

                // Autre code qui sera dans le lien de l'email
                $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $nbpassemail = $lettres = substr(str_shuffle($alphabet), 0, rand(10, 20));

                $subject = "Changement de mot de passe sur Project-Sharing.";
                $content = "Bonjour $targetPseudo,

Le code de vérification pour modifier votre mot de passe : $nbpass
Merci de vous rendre sur le lien suivant afin de modifier votre mot de passe : $url/modifypass?id=$targetUser_id&idemail=$nbpassemail
Vous disposez de 15 minutes avant que ce lien ne sois plus actif.

Si vous avez des questions ou si vous avez besoin d'assistance, n'hésitez pas à nous contacter à l'adresse suivante : natcode@project-sharing.fr.to.

Cordialement,
L'équipe Project-Sharing.";
                sendmail("info@project-sharing.fr.to", $targetEmail, $subject, $content);

                // Inserer dans sendforgotpassword
                $sql = $pdo->prepare("INSERT INTO sendforgotpassword (email, nb, timestamp) VALUES (?, 1, NOW()) ON DUPLICATE KEY UPDATE nb = 1, timestamp = NOW()");
                $sql->execute([$targetEmail]);

                // Inserer dans modifypass
                $sql = $pdo->prepare("INSERT INTO modifypass (email, nbpass, nbpassemail) VALUES (?, ?, ?)");
                $sql->execute([$targetEmail, $nbpass, $nbpassemail]);

                $goodMessage = "<div class='info'>L'email à bien été envoyer, veuillez suivre les instructions qui seront données.</div>";
            }
        }
    } else {
        $errorEmail = "<div class='alert'>Email n'est pas enrgistrée sur le site.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Sharing - forgotpassword</title>
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
            <div class="forgotpassword">
                <?php
                if (isset($goodMessage)) {
                    echo "<h1>Code envoyé!</h1>
                          $goodMessage
                          <br>";
                } else {
                    ?>
                    <form method="POST" action="forgotpassword" enctype="multipart/form-data" id="forgotpassword-form">
                        <h1>Mot de passe oublié ?</h1>
                        <br>
                        <label for="email">Votre Email :</label>
                        <br>
                        <input type="text" id="email" name="email" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                        <?php if (isset($errorEmail)) { echo $errorEmail . "<br>"; } ?>
                        <br>
                        <?php if (isset($errorMessage)) { echo $errorMessage . "<br>"; } ?>
                        <button type="submit">Envoyer</button>
                    </form>
                    <?php
                }
                ?>
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
