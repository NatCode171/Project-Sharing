<?php
require_once 'init.php';

$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$url .= "://".$_SERVER['HTTP_HOST']; // si on est sur dev. ou pas

$target_user_id = isset($_GET['id']) ? $_GET['id'] : '';
$target_user_id_code_email = isset($_GET['idemail']) ? $_GET['idemail'] : '';
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
            <div class="forgotpassword">
                <form method="POST" action="<?php echo "$url/modifypass?id=$target_user_id&idemail=$target_user_id_code_email"; ?>" enctype="multipart/form-data" id="forgotpassword-form">
                    <h1>Mot de passe oublié ?</h1>
                    <br>
                    <?php
                    if ($target_user_id != '' && $target_user_id_code_email != '') {

                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                            $targetEmail = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
                            $newpassword1 = $_POST['password1'];
                            $newpassword2 = $_POST['password2'];

                            if ($newpassword1 == $newpassword2) {

                                $targetCode =(int)$_POST['code'];
                                    
                                if (filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
                                    $targetEmail = trim(strtolower($targetEmail));

                                    $sql = $pdo->prepare("SELECT id, ban, ban_reason FROM users WHERE email = ?");
                                    $sql->execute([$targetEmail]);
                                    $result = $sql->fetch(PDO::FETCH_ASSOC);

                                    if ($result) {
                                        $targetUser_id = (int)$result['id'];
                                        if ((int)$result['ban']) {
                                            $ban_reason = htmlspecialchars($result['ban_reason']);
                                            $errorautre = "<div class='alert'><strong>Vous avez été banni de Project Sharing, voici la ou les raison(s) :</strong><br><br>$ban_reason</div>";
                                        } else {
                                            if ($targetUser_id != $target_user_id) {
                                                $forgotpasswordErrors = "<div class='alert'>$targetUser_id != $target_user_id</div>";
                                            } else {
                                                $sql = $pdo->prepare("SELECT nbpass, nbpassemail, timestamp FROM modifypass WHERE email = ?");
                                                $sql->execute([$targetEmail]);
                                                $result = $sql->fetch(PDO::FETCH_ASSOC);

                                                if ($result) {
                                                    $nbpass = (int)$result['nbpass'];
                                                    $nbpassemail = (int)$result['nbpassemail'];
                                                    $lastAttempt = strtotime($result['timestamp']);
                                                    $currentTime = time();

                                                    if ($nbpass == $targetCode && $nbpassemail == $target_user_id_code_email) {

                                                        if ($currentTime - $lastAttempt >= 60 * 15) {
                                                            $forgotpasswordErrors = "<div class='alert'>Délais dépassé.</div>";
                                                        } else {
                                                            if (strlen($newpassword1) < 8 || 
                                                                !preg_match('/\d/', $newpassword1) || 
                                                                !preg_match('/[a-z]/', $newpassword1) || 
                                                                !preg_match('/[A-Z]/', $newpassword1) || 
                                                                !preg_match('/[\W_]/', $newpassword1)) {
                                                                $codeErrorspassword = "<div class='alert'>Votre mot de passe doit contenir au moins 8 caractères, incluant une lettre minuscule, une lettre majuscule, un chiffre et un caractère spécial.</div>";
                                                            } else {
                                                                $new_password_hashed = password_hash($newpassword1, PASSWORD_DEFAULT);

                                                                $update_query = "UPDATE users SET password = ? WHERE id = ?";
                                                                $stmt_update = $pdo->prepare($update_query);
                                                                if ($stmt_update->execute([$new_password_hashed, $target_user_id])) {
                                                                    header('Location: /login');
                                                                    exit();
                                                                } else {
                                                                    $codeErrorspassword = "<div class='alert'><p>Erreur lors de la mise à jour.</p></div>";
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        $codeErrors = "<div class='alert'>Code incorecte ou vous êtes sur la mauvais lien.</div>";
                                                    }
                                                } else {
                                                    $forgotpasswordErrors = "<div class='alert'>Vous n'avez pas fais de demmande de modification de mot de passe.</div>";
                                                }
                                            }
                                        }
                                    } else {
                                        $forgotpasswordErrors = "<div class='alert'><p>Cette email n'est pas enregistrée sur notre site.</p></div>";
                                    }
                                } else {
                                    $forgotpasswordErrors = "<div class='alert'><p>Email non valide.</p></div>";
                                }
                            } else {
                                $codeErrorspassword = "<div class='alert'><p>Les mots de passe ne coresspondent pas.</p></div>";
                            }
                        }
                    }
                    ?>
                    <label for="email">Votre Email :</label>
                    <br>
                    <input type="text" id="email" name="email" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                    <?php if (isset($forgotpasswordErrors)) { echo $forgotpasswordErrors . "<br>"; } ?>
                    <br>

                    <label for="email">Votre Code :</label>
                    <br>
                    <input type="text" id="code" name="code" required value="<?php echo isset($_POST['code']) ? $_POST['code'] : ''; ?>">
                    <?php if (isset($codeErrors)) { echo $codeErrors . "<br>"; } ?>
                    <br>

                    <label for="password1">Nouveau mot de passe :</label>
                    <br>
                    <input type="password" id="password1" name="password1" required>
                    <?php if (isset($codeErrorspassword)) { echo $codeErrorspassword . "<br>"; } ?>
                    <br>

                    <label for="password2">Confirmer le mot de passe :</label>
                    <br>
                    <input type="password" id="password2" name="password2" required>
                    <?php if (isset($codeErrorspassword)) { echo $codeErrorspassword . "<br>"; } ?>
                    <br>

                    <?php if (isset($errorautre)) { echo $errorautre . "<br>"; } ?>
                    <button type="submit">Envoyer</button>
                </form>
            </div>
        </div> 
    </main>
</body>
</html>
