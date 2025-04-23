<?php
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
            <div class="forgotpassword">
                <form method="POST" action="modifypass" enctype="multipart/form-data" id="forgotpassword-form">
                    <h1>Mot de passe oublié ?</h1>
                    <br>
                    <?php
                    $target_user_id = isset($_GET['id']) ? $_GET['id'] : '';
                    $target_user_id_code_email = isset($_GET['idemail']) ? $_GET['idemail'] : '';
                    if ($target_user_id !== '' && $target_user_id_code_email !== '') {

                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                            $targetEmail = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
                            $newpassword = $_POST['password'];
                            
                            $targetCode =(int)$_POST['code'];
                                
                            if (filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
                                $targetEmail = trim(strtolower($targetEmail));

                                $sql = $pdo->prepare("SELECT id, ban FROM users WHERE email = ?");
                                $sql->execute([$targetEmail]);
                                $result = $sql->fetch(PDO::FETCH_ASSOC);

                                if ($result) {
                                    $targetUser_id = (int)$result['id'];
                                    if ((int)$result['ban']) {
                                        die("<div class='alert'><strong>Vous avez été banni de Project Sharing, voici la ou les raison(s) :</strong><br><br>$ban_reason</div>");
                                    } else {
                                        if ($targetUser_id != $target_user_id) {
                                            $forgotpasswordErrors = "<div class='alert'><p>Email non valide.</p></div>";
                                        } else {
                                            $sql = $pdo->prepare("SELECT nbpass, nbpassemail FROM modifypass WHERE email = ?");
                                            $sql->execute([$targetEmail]);
                                            $result = $sql->fetch(PDO::FETCH_ASSOC);

                                            if ($result) {
                                                $nbpass = (int)$result['nbpass'];
                                                $nbpassemail = (int)$result['nbpassemail'];

                                                if ($nbpass == $targetCode && $nbpassemail == $target_user_id_code_email) {

                                                    $currentTime = time();
                                                    $lastAttempt = strtotime($result['timestamp']);                         

                                                    if ($currentTime - $lastAttempt <= 60 * 15) {
                                                        $remainingTime = 60 * 15 - floor(($currentTime - $lastAttempt) / 60);
                                                        $forgotpasswordErrors = "<div class='alert'>Délais dépassé.";
                                                    } else {
                                                        if (strlen($password) < 8 || 
                                                            !preg_match('/\d/', $password) || 
                                                            !preg_match('/[a-z]/', $password) || 
                                                            !preg_match('/[A-Z]/', $password) || 
                                                            !preg_match('/[\W_]/', $password)) {
                                                            $codeErrorspassword['password'] = "<div class='alert'>Votre mot de passe doit contenir au moins 8 caractères, incluant une lettre minuscule, une lettre majuscule, un chiffre et un caractère spécial.</div>";
                                                        } else {
                                                            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

                                                            $update_query = "UPDATE users SET password = ? WHERE id = ?";
                                                            $stmt_update = $pdo->prepare($update_query);
                                                            if ($stmt_update->execute([$new_password_hashed, $target_user_id])) {
                                                                header('Location: /');
                                                                exit();
                                                            } else {
                                                                $codeErrorspassword['password'] = "<div class='alert'><p>Erreur lors de la mise à jour.</p></div>";
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                $forgotpasswordErrors = "<div class='alert'>Email non valide.</div>";
                                            }
                                        }
                                    }
                                } else {
                                    $forgotpasswordErrors = "<div class='alert'><p>Email non valide.</p></div>";
                                }
                            } else {
                                $forgotpasswordErrors = "<div class='alert'><p>Email non valide.</p></div>";
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

                    <label for="password">Nouveau mot de passe :</label>
                    <br>
                    <input type="text" id="password" name="password" required value="<?php echo isset($_POST['password']) ? $_POST['password'] : ''; ?>">
                    <?php if (isset($codeErrorspassword)) { echo $codeErrorspassword . "<br>"; } ?>
                    <br>
                    <button type="submit">Envoyer</button>
                </form>
            </div>
        </div> 
    </main>
</body>
</html>
