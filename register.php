<?php
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'], $_POST['code_verif'], $_POST['pseudo'])) {

    $email = trim(strtolower(htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8')));
    $pseudo = htmlspecialchars($_POST['pseudo'], ENT_QUOTES, 'UTF-8');
    $hashed_password = $_POST['hashed_password'];
    $password = $_POST['password'];
    

    $code = (int)$_POST['code_verif'];

    $stmt1 = $pdo->prepare("SELECT * FROM code_register WHERE email = ? AND code = ?");
    $stmt1->execute([$email, $code]);

    if ($stmt1->rowCount() == 0) {
        $errors['code_verif'] = "<div class='alert'>Code invalide.</div>";
    } else {
        $stmt2 = $pdo->prepare("SELECT * FROM code_register WHERE email = ? AND code = ? AND date >= (NOW() - INTERVAL 15 MINUTE)");
        $stmt2->execute([$email, $code]);

        if ($stmt2->rowCount() == 0) {
            $errors['code_verif'] = "<div class='alert'>Code expiré.</div>";
        } else {
            if (password_verify($password, $hashed_password)) {
                $stmt = $pdo->prepare("INSERT INTO users (statut, ban, is_public, pseudo, password, email) VALUES (?, '0', ?, ?, ?, ?)");
                $stmt->execute([$statut_de_base, $is_public_de_base, $pseudo, $hashed_password, $email]);

                if ($stmt) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE pseudo = ?");
                    $stmt->execute([$pseudo]);

                    if ($stmt->rowCount() > 0) {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $myUser_id = (int)$row['id'];
                        
                        $log = insertLog(5, $myUser_id, null);

                        function resizeImage($source, $destination, $width, $height) {
                            list($srcWidth, $srcHeight, $imageType) = getimagesize($source);
                            $newImage = imagecreatetruecolor($width, $height);
                            switch ($imageType) {
                                case IMAGETYPE_JPEG:
                                    $srcImage = imagecreatefromjpeg($source);
                                    break;
                                case IMAGETYPE_PNG:
                                    $srcImage = imagecreatefrompng($source);
                                    break;
                                case IMAGETYPE_GIF:
                                    $srcImage = imagecreatefromgif($source);
                                    break;
                                default:
                                    return false;
                            }
                            imagecopyresampled($newImage, $srcImage, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
                            switch ($imageType) {
                                case IMAGETYPE_JPEG:
                                    imagejpeg($newImage, $destination);
                                    break;
                                case IMAGETYPE_PNG:
                                    imagepng($newImage, $destination);
                                    break;
                                case IMAGETYPE_GIF:
                                    imagegif($newImage, $destination);
                                    break;
                            }
                            imagedestroy($srcImage);
                            imagedestroy($newImage);
                            return true;
                        }

                        $userDirectory = 'users/' . $user_id;
                        if (!is_dir($userDirectory)) {
                            mkdir($userDirectory, 0777, true);
                        }

                        if (isset($_FILES['logo']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
                            $logoPath = $userDirectory . '/logo.png';

                            if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath)) {
                                if (resizeImage($logoPath, $logoPath, 100, 100)) {
                                    echo "<div class='alert'>Logo uploadé et redimensionné avec succès !</div>";
                                } else {
                                    echo "<div class='alert'>Erreur lors du redimensionnement du logo.</div>";
                                }
                            } else {
                                echo "<div class='alert'>Erreur lors du téléchargement du logo.</div>";
                            }
                        }

                        // Supprimer le code après validation
                        $stmt = $pdo->prepare("DELETE FROM code_register WHERE email = ?");
                        $stmt->execute([$email]);
                            

                        $_SESSION['user_id'] = $myUser_id = $row['id'];
                        $_SESSION['pseudo'] = $pseudo;
                        $_SESSION['password'] = $hashed_password;

                        header('Location: /');
                        exit();
                    }
                }
            }
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['code_verif'])) {
        
    $pseudo = htmlspecialchars($_POST['pseudo'], ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];
    $email = trim(strtolower(htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8')));
    
    if (strlen($password) < 8 || 
        !preg_match('/\d/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[\W_]/', $password)) {
        $errors['password'] = "<div class='alert'>Votre mot de passe doit contenir au moins 8 caractères, incluant une lettre minuscule, une lettre majuscule, un chiffre et un caractère spécial.</div>";
    }

    if (strlen($pseudo) > 20 || !preg_match('/^[a-zA-Z0-9_-]+$/', $pseudo)) {
        $errors['pseudo'] = "<div class='alert'>Votre pseudo doit contenir au maximum 20 caractères et ne doit inclure que des lettres, des chiffres, des underscores ou des tirets.</div>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE pseudo = ?");
        $stmt->execute([$pseudo]);
        if ($stmt->rowCount() > 0) {
            $errors['pseudo'] = "<div class='alert'>Le pseudo est déjà utilisé.</div>";
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "<div class='alert'>Adresse email invalide.</div>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors['email'] = "<div class='alert'>L'email est déjà utilisé.</div>";
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // code de vérif
        $code = mt_rand(100000, 999999);

        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM code_register WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
        // Mettre à jour le code existant
        $stmt = $pdo->prepare("UPDATE code_register SET code = ?, date = CURRENT_TIMESTAMP WHERE email = ?");
        $stmt->execute([$code, $email]);
        } else {
        // Insérer un nouveau code
        $stmt = $pdo->prepare("INSERT INTO code_register (email, code, date) VALUES (?, ?, CURRENT_TIMESTAMP)");
        $stmt->execute([$email, $code]);
        }

        $subject = "Bienvenue sur Project-Sharing !";
        $content = "Bonjour $pseudo,

Merci de vous être inscrit(e) sur Project-Sharing.fr.to !
Nous sommes ravis de vous accueillir dans notre communauté dédiée au partage et à la collaboration sur des projets passionnants.

Voici un résumé de votre inscription :

- Nom d'utilisateur : $pseudo
- Adresse e-mail : $email

Votre code de vérification : $code

Si vous n'êtes pas à l'origine de cette inscription, ne faites rien, l'utilisateur ne pourra pas se créer un compte car il n'a pasle code de vérification !

Ce que vous pourrez faire dès que vous aurez validé(e) le code :

- Créer un projet : Partagez vos idées et travaillez avec d'autres membres.
- Pixel War : Nous avons une pixel war sur Project-Sharing !!!

- N'oubliez pas de personnaliser votre profil pour mieux vous présenter à la communauté.

Si vous avez des questions ou besoin d’aide, notre équipe est là pour vous.
Vous pouvez nous contacter à tout moment via l'adresse natcode@project-sharing.fr.to.

Nous vous souhaitons une expérience enrichissante et collaborative sur notre plateforme !

Cordialement,
L'équipe Project-Sharing.";
        sendmail("info@project-sharing.fr.to", $email, $subject, $content);
        $code = true;
    } else {
        $errors['login'] = "<div class='alert'>Nom d'utilisateur ou mot de passe incorrect.</div>";
    }
} else {
    $errors['login'] = "<div class='alert'>Nom d'utilisateur ou mot de passe incorrect.</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Sharing - Register</title>
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
            <div class="register">
                <?php
                if ($code) {
                    ?>
                    <form method="POST" action="register" enctype="multipart/form-data">
                        <h1>Bonjour <?php echo $pseudo; ?> !</h1>
                        <p>Un code de vérification a été envoyé à l'email suivante : <strong><?php echo $email; ?></strong></p>
                        <p>Vous avez un délais de 15 minutes pour entrer le code si dessous.</p>
                        <label for="code_verif">Code de vérification :</label>
                        <br>
                        <input type="text" id="code_verif" name="code_verif" required value="<?php echo isset($_POST['code_verif']) ? htmlspecialchars($_POST['code_verif'], ENT_QUOTES) : ''; ?>">
                        <?php if (isset($errors['code_verif'])) { echo $errors['code_verif']; } ?>

                        <input type="hidden" name="email" value="<?php echo $email; ?>">
                        <input type="hidden" name="pseudo" value="<?php echo $pseudo; ?>">
                        <input type="hidden" name="hashed_password" value="<?php echo $hashed_password; ?>">
                        <input type="hidden" name="password" value="<?php echo $password; ?>">

                        <br>
                        <button type="submit">Envoyer</button>
                    </form>
                    <?php
                } else {
                    ?>
                    <h1>S'enregistrer :</h1>
                    <form method="POST" action="register" enctype="multipart/form-data">
                        <label for="pseudo">Votre pseudo :</label>
                        <br>
                        <input type="text" id="pseudo" name="pseudo" required value="<?php echo isset($_POST['pseudo']) ? htmlspecialchars($_POST['pseudo'], ENT_QUOTES) : ''; ?>">
                        <?php if (isset($errors['pseudo'])) { echo $errors['pseudo']; } ?>
                        <br>

                        <label for="password">Mot de passe :</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" required>
                            <span class="toggle-password">
                                <img src="/img/eye-icon-close.png" alt="Afficher/Masquer le mot de passe">
                            </span>
                        </div>
                        <?php if (isset($errors['password'])) { echo $errors['password']; } ?>
                        <br>

                        <label for="email">Votre adresse email :</label>
                        <br>
                        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES) : ''; ?>">
                        <?php if (isset($errors['email'])) { echo $errors['email']; } ?>
                        <br>

                        <label for="logo">Choisissez un logo :</label>
                        <div class="image-preview" onclick="document.getElementById('logo').click();">
                            <img id="imagePreview" src="/img/profil_logo_default.png" alt="Aperçu du logo">
                        </div>
                        <input type="file" name="logo" id="logo" accept="image/*" onchange="previewImage(event)">
                        <br>       
                        <label>
                            <input type="checkbox" id="conditionsutilisation" name="conditionsutilisation" required>
                            J'accepte les <a href="/conditionsutilisation" target="_blank">conditions d'utilisation</a><?php echo " (" . $vconditionsutilisation . ")"; ?>
                        </label>
                        <br>

                        <button type="submit">Envoyer</button>
                    </form>
                <?php
                }
                ?>
            </div>
        </div>
    </main>
    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('imagePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

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
