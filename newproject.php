<?php
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    
    $targetTitle = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $targetDescription = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');

    $sql = $pdo->prepare("INSERT INTO projects (title, description, user_id) VALUES (?, ?, ?)");
    $result = $sql->execute([$targetTitle, $targetDescription, $myUser_id]);

    if ($result) {
        echo $result;
    }

    $sql = $pdo->prepare("SELECT id FROM projects WHERE title = ?");
    $sql->execute([$targetTitle]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    $project_id = (int)$result['id'];

    if (isset($_FILES['fichiers'])) {
        foreach ($_FILES['fichiers']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['fichiers']['error'][$key] === 0) {
                $fichier_tmp = $tmp_name;
                $fichier_name = basename($_FILES['fichiers']['name'][$key]);
                $file_ext = strtolower(pathinfo($fichier_name, PATHINFO_EXTENSION));
                $valid_image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'img'];
                if (in_array($file_ext, $valid_image_extensions)) {
                    $new_fichier_name = $fichier_name;
                } else {
                    $new_fichier_name = str_replace('.', '_', pathinfo($fichier_name, PATHINFO_FILENAME)) . "_$file_ext.txt";
                }
                $fichierDirectory = "./users/$myUser_id/$project_id/" . $new_fichier_name;

                if (!file_exists("./users/$myUser_id/$project_id")) {
                    mkdir("./users/$myUser_id/$project_id", 0777, true);
                }

                if (move_uploaded_file($fichier_tmp, $fichierDirectory)) {
                } else {
                    echo "<div class='alert'>Erreur lors du téléchargement du fichier : $fichier_name.<br></div>";
                    exit();
                }
            } else {
                echo "<div class='alert'>Erreur : un problème est survenu avec le fichier : $fichier_name.<br></div>";
                exit();
            }
        }
    } else {
        echo "<div class='alert'>Erreur : aucun fichier sélectionné ou une erreur s'est produite.</div>";
        exit();
    }

    $log = insertLog(12, $myUser_id, null);
    header('Location: /');
    exit();
}
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset="utf-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Project Sharing - Nouveau projet</title>
    <link rel='icon' href='/img/Logo_Project-Sharing.png' type='image/png'>
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
                <div class="newproject">
                    <h1>Nouveau projet :</h1>

                    <form method="POST" action="newproject" enctype="multipart/form-data">
                        <label for="title">Titre :</label>
                        <br>
                        <input type="text" id="title" name="title" required>
                        <br>

                        <label for="description">Description :</label>
                        <br>
                        <textarea id="description" name="description" required></textarea>
                        <br>

                        <label for="fichiers">Choisissez des fichiers :</label>
                        <br>
                        <input type="file" name="fichiers[]" id="fichiers" accept="*/*" multiple required>
                        <br>       
                        <label>
                            <input type="checkbox" id="conditionsutilisation" name="conditionsutilisation" required>
                            J'accepte les <a href="/conditionsutilisation" target="_blank">conditions d'utilisation</a> <?php echo " (" . htmlspecialchars($vconditionsutilisation, ENT_QUOTES, 'UTF-8') . ")"; ?>
                        </label>
                        <br>

                        <button type="submit">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
