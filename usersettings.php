<?php
require_once 'init.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Sharing - Paramètres du compte</title>
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
                <h1>Paramètres du compte</h1>
                
                <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {

                        $stmt = $pdo->prepare("INSERT INTO delete_account (user_id, date) VALUES (?, ?)");
                        if ($stmt->execute([$myUser_id, $current_date_time])) {

                            $log = insertLog(3, $myUser_id, null);
                            
                            $current_date_time->modify('+30 days');
                            $date_de_suppression = $current_date_time->format('Y-m-d');

                            $subject = "Suppression de votre compte...";
                            $content = "Bonjour $pseudo,

Nous avons bien tristement reçu votre demande de suppression de compte sur Project-Sharing (https://project-sharing.fr.to/). Votre compte sera donc définitivement supprimé dans 30 jours si aucune connexion n’est effectuée d'ici là.
Détails de votre demande :

    - Nom d'utilisateur : $pseudo
    - Adresse e-mail : $email
    - Date prévue de suppression définitive : $date_de_suppression

Important :

Si vous n'avez pas demandé ceci, veuillez vous connecter à votre compte le plus rapidement possible et changer votre mot de passe: un intru pourrait s'être connecté à votre place, et changer votre mot de passe le déconnectera de nos systèmes.

Si vous changez d’avis et souhaitez conserver votre compte, il vous suffit de vous reconnecter avant la date de suppression prévue. Cela annulera automatiquement la procédure de suppression.
Passé ce délai, toutes vos données seront effacées de manière permanente et ne pourront être récupérées.

Cordialement,
L’équipe Project-Sharing
                            ";
                            sendmail("info@project-sharing.fr.to", $email, $subject, $content);

                            header("Location: /");
                            exit();
                        } else {
                            echo "<div class='alert'>";
                            echo "Erreur : " . implode(" ", $stmt->errorInfo());
                            echo "</div>";
                        }                
                    }

                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'saveaccount') {
                        $new_pseudo = htmlspecialchars($_POST['pseudo'], ENT_QUOTES);
                        $new_idChannelYt = htmlspecialchars($_POST['idChannelYt'], ENT_QUOTES);
                        $new_bio = htmlspecialchars($_POST['bio'], ENT_QUOTES);
                        $new_is_public = isset($_POST['is_public']) ? intval($_POST['is_public']) : $is_public;
                        $new_email = trim(strtolower(htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8')));

                        if ($_POST['password']) {

                            $new_password = $_POST['password'];
                            $new_password_verify = 1;

                            if (strlen($new_password) < 8 || 
                                !preg_match('/\d/', $new_password) || 
                                !preg_match('/[a-z]/', $new_password) || 
                                !preg_match('/[A-Z]/', $new_password) || 
                                !preg_match('/[\W_]/', $new_password)) {
                                $errors['password'] = "<div class='alert'>Votre mot de passe doit contenir au moins 8 caractères, incluant une lettre minuscule, une lettre majuscule, un chiffre et un caractère spécial.</div>";
                            }

                            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        }

                        if (strlen($new_pseudo) > 20 || !preg_match('/^[a-zA-Z0-9_-]+$/', $new_pseudo)) {
                            $errors['pseudo'] = "<div class='alert'>Votre pseudo doit contenir au maximum 10 caractères.</div>";
                        } else {
                            $stmt = $pdo->prepare("SELECT id FROM users WHERE pseudo = ? AND id != ?");
                            $stmt->execute([$new_pseudo, $myUser_id]);
                            if ($stmt->rowCount() > 0) {
                                $errors['pseudo'] = "<div class='alert'>Le pseudo est déjà utilisé.</div>";
                            }
                        }

                        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                            $errors['email'] = "<div class='alert'>Adresse email invalide.</div>";
                        } else {
                            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                            $stmt->execute([$new_email, $myUser_id]);
                            if ($stmt->rowCount() > 0) {
                                $errors['email'] = "<div class='alert'>L'email est déjà utilisé.</div>";
                            }
                        }

                        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                            $image_info = getimagesize($_FILES['logo']['tmp_name']);
                            if ($image_info) {
                                $logoPath = $_SERVER['DOCUMENT_ROOT'] . "/users/$myUser_id/logo.png";
                                if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath)) {
                                    resizeImage($logoPath, $logoPath, 200, 200);
                                } else {
                                    $errors['logo'] = "<div class='alert'>Erreur lors du téléchargement de l'image.</div>";
                                }
                            } else {
                                $errors['logo'] = "<div class='alert'>Le fichier uploadé n'est pas une image valide.</div>";
                            }
                        }

                        if (empty($errors)) {
                            if ($new_password_verify == 1) {

                                $update_query = "UPDATE users SET pseudo = ?, email = ?, is_public = ?, password = ?, bio = ?, idChannelYt = ? WHERE id = ?";
                                $stmt_update = $pdo->prepare($update_query);
                                if ($stmt_update->execute([$new_pseudo, $new_email, $new_is_public, $new_password_hashed, $new_bio, $new_idChannelYt, $myUser_id])) {
                                    $_SESSION['password'] = $new_password_hashed;
                                    $new_password_verify = 0;
                                    header('Location: /');
                                    exit();
                                } else {
                                    echo "<div class='alert'><p>Erreur lors de la mise à jour.</p></div>";
                                }
                            } else {
                                $update_query = "UPDATE users SET pseudo = ?, email = ?, is_public = ?, bio = ?, new_idChannelYt = ? WHERE id = ?";
                                $stmt_update = $pdo->prepare($update_query);
                                if ($stmt_update->execute([$new_pseudo, $new_email, $new_is_public, $new_bio, $new_idChannelYt, $myUser_id])) {
                                    header('Location: /');
                                    exit();
                                } else {
                                    echo "<div class='alert'><p>Erreur lors de la mise à jour.</p></div>";
                                }
                            }
                        }
                    }

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
                        imagejpeg($newImage, $destination);
                        imagedestroy($srcImage);
                        imagedestroy($newImage);
                        return true;
                    }
                ?>

                <form method="POST" enctype="multipart/form-data">
                    <label for="pseudo">Pseudo :</label>
                    <br>
                    <input type="text" name="pseudo" id="pseudo" value="<?php echo htmlspecialchars($myPseudo, ENT_QUOTES); ?>" required>
                    <?php if (isset($errors['pseudo'])) echo $errors['pseudo']; ?>
                    <br>

                    <label for="email">Email :</label>
                    <br>
                    <input type="text" name="email" id="email" value="<?php echo htmlspecialchars($myEmail, ENT_QUOTES); ?>" required>
                    <?php if (isset($errors['email'])) echo $errors['email']; ?>
                    <br>

                    <?php if ($myPremium == 1) {
                        ?>

                        <label for="idChannelYt">Chaîne YouTube :</label>
                        <h4>Entrez l'ID de votre chaîne YouTube pour qu'elle apparaisse en bas de votre profil !</h4>
                        <input type="text" name="idChannelYt" id="idChannelYt" value="<?php echo htmlspecialchars($myIdChannelYt, ENT_QUOTES); ?>" required>
                        <?php if (isset($errors['idChannelYt'])) echo $errors['idChannelYt']; ?>
                        <br>

                        <?php
                    }
                    ?>

                    <label for="is_public">Confidentialité :</label>
                    <br>
                    <select name="is_public" id="is_public">
                        <option value="1" <?php if ($myIs_public == 1) echo 'selected'; ?>>Public</option>
                        <option value="0" <?php if ($myIs_public == 0) echo 'selected'; ?>>Privé</option>
                    </select>
                    <br>

                    <label for="logo">Choisissez un logo :</label>
                    <div class="image-preview" onclick="document.getElementById('logo').click();">
                        <img id="imagePreview" src="<?php echo $myLogoDirectory ?>" alt="Aperçu du logo">
                    </div>
                    <input type="file" name="logo" id="logo" accept="image/*" onchange="previewImage(event)">
                    <br>

                    <br>
                    <label for="bio">Biographie :</label>
                    <br>
                    <textarea id='bio' name='bio'><?php echo $bio; ?></textarea>
                    <br>
                    
                    <br>
                    <label for="password">Mot de passe :</label>
                    <br>
                    <div class="password-container">
                        <input type="password" id="password" name="password">
                        <span class="toggle-password">
                            <img src="/img/eye-icon-close.png" alt="Afficher/Masquer le mot de passe">
                        </span>
                    </div>
                    <?php if (isset($errors['password'])) { echo $errors['password']; } ?>
                    <br>

                    <label>
                        <input type="checkbox" id="conditionsutilisation" name="conditionsutilisation" required>
                        J'accepte les <a href="/conditionsutilisation" target="_blank">conditions d'utilisation</a>
                    </label>
                    <br>
                    <input type="hidden" name="action" value="saveaccount">
                    <button type="submit">Mettre à jour</button>
                </form>
                <div class="delete_account">
                    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ?');">
                        <input type="hidden" name="action" value="delete_account">
                        <button type="submit">Supprimer mon compte</button>
                    </form>
                </div>
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
    </script>
</body>
</html>

