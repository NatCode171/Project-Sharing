<?php
require_once 'init.php';

if ($myStatutInt !== $statutAdmin && $myStatutInt !== $statutModo) {
    header("Location: /");
    exit;
}

if (isset($_GET["dl"]) && $_GET["dl"] && file_exists("code" . DIRECTORY_SEPARATOR . $_GET["dl"] . ".zip")) {
    $file = "code" . DIRECTORY_SEPARATOR . $_GET["dl"] . ".zip";
    header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
    header("Cache-Control: public");
    header("Content-Type: application/zip");
    header("Content-Transfer-Encoding: Binary");
    header("Content-Length: " . filesize($file));
    header("Content-Disposition: attachment");
    readfile($file);
    exit();
}

?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset="utf-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Project Sharing - download</title>
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
            <div class="downloadmywebsite">
                <h1>Télécharger le code source du site :</h1>

                <a href="?dl=v2.1" download="project-sharing-v2.1.zip">
                    <button>Cliquez ici pour télécharger project-sharing-v2.1.zip</button>
                </a>    
                </br>

                <a href="?dl=v2.0" download="project-sharing-v2.0.zip">
                    <button>Cliquez ici pour télécharger project-sharing-v2.0.zip</button>
                </a>    
                </br>

                <a href="?dl=v1.5.5" download="project-sharing-v1.5.5.zip">
                    <button>Cliquez ici pour télécharger project-sharing-v1.5.5.zip</button>
                </a>    
                </br>

                <a href="?dl=v1.5.4" download="project-sharing-v1.5.4.zip">
                    <button>Cliquez ici pour télécharger project-sharing-v1.5.4.zip</button>
                </a>    
                </br>
                
                <a href="?dl=v1.5.3" download="project-sharing-v1.5.3.zip">
                    <button>Cliquez ici pour télécharger project-sharing-v1.5.3.zip</button>
                </a>    
                <br>

                <a href="?dl=v1.5.2" download="project-sharing-v1.5.2.zip">
                    <button>Cliquez ici pour télécharger project-sharing-v1.5.2.zip</button>
                </a>
                <br>

                <a href="?dl=v1.5.1" download="project-sharing-v1.5.1.zip">
                    <button>Cliquez ici pour télécharger project-sharing-v1.5.1.zip</button>
                </a>
                <br>
                
                <a href="?dl=v1.5" download="project-sharing-v1.5.zip">
                    <button>Cliquez ici pour télécharger project-sharing-v1.5.zip</button>
                </a>
                <br>
                
                <a href="?dl=v1.4" download="project-sharing-v1.4.zip">
                    <button>Cliquez ici pour télécharger project-sharing-v1.4.zip</button>
                </a>
                <br>

                <a href="?dl=v1.3" download="project-sharing-v1.3.zip">
                    <button>Cliquez ici pour télécharger project-sharing-v1.3.zip</button>
                </a>
                <br>

                <button class="bouton-indispo" onclick='alert("La version 1.2 a été perdue à tout jamais de façon inconnue.")'>Cliquez ici pour télécharger project-sharing-v1.2.zip</button>
                <br>

                <a href="?dl=v1.1" download="project-sharing-v1.1.zip">
                    <button>Cliquez ici pour télécharger project-sharing-v1.1.zip</button>
                </a>
                <br>

                <a href="?dl=v1.0" download="project-sharing-v1.0.zip">
                    <button>Cliquez ici pour télécharger project-sharing-v1.0.zip</button>
                </a>
                <br>
            </div>
        </div>
    </main>
</body>
</html>
