<?php
require_once 'init.php';

$project_id = isset($_GET['id']) ? htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8') : false;

if (!$project_id) {
    echo "<h1><div class='projects'><div class='alert'><strong>ID invalide !</strong></div></div></h1>";
    exit();
}

$stmt = $pdo->prepare("SELECT title, description, user_id, likes, dislikes FROM projects WHERE id = ?");
$stmt->execute([$project_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $title = htmlspecialchars($result['title']);
    $description = $result['description'];
    $targetUser_id = (int)$result['user_id'];
    $likes = (int)$result['likes'];
    $dislikes = (int)$result['dislikes'];

    $stmt_user = $pdo->prepare("SELECT pseudo FROM users WHERE id = ?");
    $stmt_user->execute([$targetUser_id]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

    $targetPseudo = $user_data ? htmlspecialchars($user_data['pseudo']) : "Utilisateur inconnu";
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['like']) || isset($_POST['dislike'])) && $myUser_id) {
        
        // tester si on a liké ou disliké
        $sql = $pdo->prepare("SELECT is_like FROM likes WHERE user_id = ? AND project_id = ?");
        $sql->execute([$myUser_id, $project_id]);
        $data = $sql->fetchAll();
        
        // si on a liké ou disliké
        if ($data) {
            if ($data[0]["is_like"] == 0 && isset($_POST['like']) && $_POST['like']) {
                // il avait disliké et il veux liker
                // changer le dislike en like
                $stmt = $pdo->prepare("UPDATE likes SET is_like = 1 WHERE user_id = ? AND project_id = ?");
                $stmt->execute([$myUser_id, $project_id]);
                // mettre a jour les likes dans la table projects
                $stmt = $pdo->prepare("UPDATE projects SET likes = likes + 1, dislikes = dislikes - 1 WHERE id = ?");
                $stmt->execute([$project_id]);
                // augmenter les likes, décrémenter les dislikes
                $likes++;
                $dislikes--;
            } else if ($data[0]["is_like"] == 1 && isset($_POST['dislike']) && $_POST['dislike']) {
                // il a déja liké et il veux disliker
                // changer le like en dislike
                $stmt = $pdo->prepare("UPDATE likes SET is_like = 0 WHERE user_id = ? AND project_id = ?");
                $stmt->execute([$myUser_id, $project_id]);
                // mettre a jour les likes dans la table projects
                $stmt = $pdo->prepare("UPDATE projects SET likes = likes - 1, dislikes = dislikes + 1 WHERE id = ?");
                $stmt->execute([$project_id]);
                // augmenter les dislikes, décrémenter les likes
                $dislikes++;
                $likes--;
            }
        } else {
            // on a pas encore déjà liké ou disliké
            if (isset($_POST['like']) && $_POST['like']) {
                // il veux liker
                // ajouter une ligne de like
                $stmt = $pdo->prepare("INSERT INTO likes (user_id, project_id, is_like) VALUES (?, ?, 1)");
                $stmt->execute([$myUser_id, $project_id]);
                // mettre a jour les likes dans la table projects
                $stmt = $pdo->prepare("UPDATE projects SET likes = likes + 1 WHERE id = ?");
                $stmt->execute([$project_id]);
                // augmenter les likes
                $likes++;
            } else if (isset($_POST['dislike']) && $_POST['dislike']) {
                // ajouter une ligne de dislike
                $stmt = $pdo->prepare("INSERT INTO likes (user_id, project_id, is_like) VALUES (?, ?, 0)");
                $stmt->execute([$myUser_id, $project_id]);
                // mettre a jour les likes dans la table projects
                $stmt = $pdo->prepare("UPDATE projects SET dislikes = dislikes + 1 WHERE id = ?");
                $stmt->execute([$project_id]);
                // augmenter les dislikes
                $dislikes++;
            }
        }

        if (isset($_GET["gohome"])) {
            if ($_GET["gohome"] == "1") {
                header("Location: /");
                exit;
            } else {
                header("Location: /myprojects");
                exit;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset="utf-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Project Sharing - Projets</title>
    <link rel='icon' href='/img/Logo_Project-Sharing.png' type='image/png'>
    <link rel="stylesheet" href="/styles.css">
    <link rel="stylesheet" href="/highlight/styles/monokai.min.css">
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
            <?php
                echo "<div class='projects'>
                        <h1>Projet de $targetPseudo</h1>
                        <h3>$title</h3>
                        <p>$description</p><ul>
                        <div class='like'>
                          <form method='POST' action=''>
                            <input type='hidden' name='like' value='1'>
                            <button type='submit' class='like-button'><img src='/img/like.png' alt='Like'>$likes</button>
                          </form>
                        </div>
                        <div class='dislike'>
                          <form method='POST' action=''>
                            <input type='hidden' name='dislike' value='1'>
                            <button type='submit' class='like-button'>
                            <img src='/img/dislike.png' alt='Dislike'>$dislikes</button>
                          </form>
                        </div>
                        <span class='likeratio'>Ratio: " . ($likes - $dislikes) . "</span>";

                $dir = __DIR__ . "/users/$targetUser_id/$project_id/";

                if (is_dir($dir)) {
                    $files = array_diff(scandir($dir), array('.', '..'));

                    foreach ($files as $file) {
                        $file_path = "$dir$file";
                        $fileInfo = pathinfo($file);
                        $ext = strtolower($fileInfo['extension']);
                        $is_binary = mb_detect_encoding(file_get_contents($file_path)) === false;
                        $dl_link = "/downloadfile?uid=$targetUser_id&pid=$project_id&bn=" . urlencode($fileInfo['basename']);

                        $valid_image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];

                        if (in_array($ext, $valid_image_extensions)) {
                            echo "<div class='project-content'><h4><a download='$file' onclick='showDownloadWarning(this);return false' href='$dl_link'>$file</a></h4><img src='$dl_link' alt='$file' style='max-width:100%; height:auto;'><br><br></div>";
                        } else if (!$is_binary) {
                            $content = file_get_contents($file_path);
                            $content = htmlspecialchars($content);

                            $newFileName = str_replace('_', '.', $fileInfo['filename']) . '.' . $fileInfo['extension'];
                            $newFileName = substr($newFileName, 0, -4); // Retirer ".txt" à la fin
                            $languageClass = 'language-plaintext';
                            $codeExtension = strtolower(end(explode(".", $newFileName)));

                            switch ($codeExtension) {
                                case 'as': $languageClass = 'language-actionscript'; break;
                                case 'ada': $languageClass = 'language-ada'; break;
                                case 'adb': $languageClass = 'language-ada'; break;
                                case 'ads': $languageClass = 'language-ada'; break;
                                case 'as': $languageClass = 'language-angelscript'; break;
                                case 'conf': $languageClass = 'language-apache'; break;
                                case 'htaccess': $languageClass = 'language-apache'; break;
                                case 'applescript': $languageClass = 'language-applescript'; break;
                                case 'scpt': $languageClass = 'language-applescript'; break;
                                case 'workflow': $languageClass = 'language-applescript'; break;
                                case 'ahk': $languageClass = 'language-autohotkey'; break;
                                case 'au3': $languageClass = 'language-autoit'; break;
                                case 'awk': $languageClass = 'language-awk'; break;
                                case 'sh': $languageClass = 'language-bash'; break;
                                case 'bash': $languageClass = 'language-bash'; break;
                                case 'zsh': $languageClass = 'language-bash'; break;
                                case 'bas': $languageClass = 'language-basic'; break;
                                case 'bsi': $languageClass = 'language-basic'; break;
                                case 'bpr': $languageClass = 'language-basic'; break;
                                case 'bf': $languageClass = 'language-brainfuck'; break;
                                case 'b': $languageClass = 'language-brainfuck'; break;
                                case 'c': $languageClass = 'language-c'; break;
                                case 'h': $languageClass = 'language-c'; break;
                                case 'ceylon': $languageClass = 'language-ceylon'; break;
                                case 'clj': $languageClass = 'language-clojure'; break;
                                case 'cljs': $languageClass = 'language-clojure'; break;
                                case 'cljc': $languageClass = 'language-clojure'; break;
                                case 'coffee': $languageClass = 'language-coffeescript'; break;
                                case 'litcoffee': $languageClass = 'language-coffeescript'; break;
                                case 'v': $languageClass = 'language-coq'; break;
                                case 'ml': $languageClass = 'language-coq'; break;
                                case 'mli': $languageClass = 'language-coq'; break;
                                case 'cpp': $languageClass = 'language-cpp'; break;
                                case 'h': $languageClass = 'language-cpp'; break;
                                case 'cc': $languageClass = 'language-cpp'; break;
                                case 'cxx': $languageClass = 'language-cpp'; break;
                                case 'cr': $languageClass = 'language-crystal'; break;
                                case 'cs': $languageClass = 'language-csharp'; break;
                                case 'css': $languageClass = 'language-css'; break;
                                case 'd': $languageClass = 'language-d'; break;
                                case 'dart': $languageClass = 'language-dart'; break;
                                case 'pas': $languageClass = 'language-delphi'; break;
                                case 'dpr': $languageClass = 'language-delphi'; break;
                                case 'dfm': $languageClass = 'language-delphi'; break;
                                case 'dpk': $languageClass = 'language-delphi'; break;
                                case 'diff': $languageClass = 'language-diff'; break;
                                case 'patch': $languageClass = 'language-diff'; break;
                                case 'ex': $languageClass = 'language-elixir'; break;
                                case 'exs': $languageClass = 'language-elixir'; break;
                                case 'elm': $languageClass = 'language-elm'; break;
                                case 'erl': $languageClass = 'language-erlang'; break;
                                case 'hrl': $languageClass = 'language-erlang'; break;
                                case 'flix': $languageClass = 'language-flix'; break;
                                case 'f': $languageClass = 'language-fortran'; break;
                                case 'for': $languageClass = 'language-fortran'; break;
                                case 'f90': $languageClass = 'language-fortran'; break;
                                case 'f95': $languageClass = 'language-fortran'; break;
                                case 'fs': $languageClass = 'language-fsharp'; break;
                                case 'fsi': $languageClass = 'language-fsharp'; break;
                                case 'fsx': $languageClass = 'language-fsharp'; break;
                                case 'go': $languageClass = 'language-go'; break;
                                case 'golo': $languageClass = 'language-golo'; break;
                                case 'graphql': $languageClass = 'language-graphql'; break;
                                case 'gql': $languageClass = 'language-graphql'; break;
                                case 'groovy': $languageClass = 'language-groovy'; break;
                                case 'gvy': $languageClass = 'language-groovy'; break;
                                case 'gy': $languageClass = 'language-groovy'; break;
                                case 'hs': $languageClass = 'language-haskell'; break;
                                case 'lhs': $languageClass = 'language-haskell'; break;
                                case 'hx': $languageClass = 'language-haxe'; break;
                                case 'hy': $languageClass = 'language-hy'; break;
                                case 'ini': $languageClass = 'language-ini'; break;
                                case 'java': $languageClass = 'language-java'; break;
                                case 'jar': $languageClass = 'language-java'; break;
                                case 'js': $languageClass = 'language-javascript'; break;
                                case 'mjs': $languageClass = 'language-javascript'; break;
                                case 'cjs': $languageClass = 'language-javascript'; break;
                                case 'jsx': $languageClass = 'language-javascript'; break;
                                case 'json': $languageClass = 'language-json'; break;
                                case 'jl': $languageClass = 'language-julia'; break;
                                case 'kt': $languageClass = 'language-kotlin'; break;
                                case 'kts': $languageClass = 'language-kotlin'; break;
                                case 'less': $languageClass = 'language-less'; break;
                                case 'lisp': $languageClass = 'language-lisp'; break;
                                case 'cl': $languageClass = 'language-lisp'; break;
                                case 'lsp': $languageClass = 'language-lisp'; break;
                                case 'ls': $languageClass = 'language-livescript'; break;
                                case 'lua': $languageClass = 'language-lua'; break;
                                case 'md': $languageClass = 'language-markdown'; break;
                                case 'markdown': $languageClass = 'language-markdown'; break;
                                case 'm': $languageClass = 'language-mercury'; break;
                                case 'moo': $languageClass = 'language-mercury'; break;
                                case 'moon': $languageClass = 'language-moonscript'; break;
                                case 'conf': $languageClass = 'language-nginx'; break;
                                case 'nim': $languageClass = 'language-nim'; break;
                                case 'nix': $languageClass = 'language-nix'; break;
                                case 'm': $languageClass = 'language-objectivec'; break;
                                case 'h': $languageClass = 'language-objectivec'; break;
                                case 'ml': $languageClass = 'language-ocaml'; break;
                                case 'mli': $languageClass = 'language-ocaml'; break;
                                case 'pl': $languageClass = 'language-perl'; break;
                                case 'pm': $languageClass = 'language-perl'; break;
                                case 't': $languageClass = 'language-perl'; break;
                                case 'sql': $languageClass = 'language-pgsql'; break;
                                case 'php': $languageClass = 'language-php'; break;
                                case 'phtml': $languageClass = 'language-php'; break;
                                case 'tpl': $languageClass = 'language-php-template'; break;
                                case 'txt': $languageClass = 'language-plaintext'; break;
                                case 'pony': $languageClass = 'language-pony'; break;
                                case 'ps1': $languageClass = 'language-powershell'; break;
                                case 'psm1': $languageClass = 'language-powershell'; break;
                                case 'psd1': $languageClass = 'language-powershell'; break;
                                case 'pl': $languageClass = 'language-prolog'; break;
                                case 'pro': $languageClass = 'language-prolog'; break;
                                case 'P': $languageClass = 'language-prolog'; break;
                                case 'proto': $languageClass = 'language-protobuf'; break;
                                case 'pb': $languageClass = 'language-purebasic'; break;
                                case 'pbi': $languageClass = 'language-purebasic'; break;
                                case 'py': $languageClass = 'language-python'; break;
                                case 'pyw': $languageClass = 'language-python'; break;
                                case 'q': $languageClass = 'language-q'; break;
                                case 'r': $languageClass = 'language-r'; break;
                                case 'rmd': $languageClass = 'language-r'; break;
                                case 're': $languageClass = 'language-reasonml'; break;
                                case 'rei': $languageClass = 'language-reasonml'; break;
                                case 'rb': $languageClass = 'language-ruby'; break;
                                case 'erb': $languageClass = 'language-ruby'; break;
                                case 'rake': $languageClass = 'language-ruby'; break;
                                case 'rs': $languageClass = 'language-rust'; break;
                                case 'scala': $languageClass = 'language-scala'; break;
                                case 'sbt': $languageClass = 'language-scala'; break;
                                case 'scm': $languageClass = 'language-scheme'; break;
                                case 'ss': $languageClass = 'language-scheme'; break;
                                case 'scss': $languageClass = 'language-scss'; break;
                                case 'sh': $languageClass = 'language-shell'; break;
                                case 'bash': $languageClass = 'language-shell'; break;
                                case 'zsh': $languageClass = 'language-shell'; break;
                                case 'st': $languageClass = 'language-smalltalk'; break;
                                case 'sm': $languageClass = 'language-smalltalk'; break;
                                case 'sql': $languageClass = 'language-sql'; break;
                                case 'swift': $languageClass = 'language-swift'; break;
                                case 'tags': $languageClass = 'language-taggerscript'; break;
                                case 'tcl': $languageClass = 'language-tcl'; break;
                                case 'itcl': $languageClass = 'language-tcl'; break;
                                case 'ts': $languageClass = 'language-typescript'; break;
                                case 'tsx': $languageClass = 'language-typescript'; break;
                                case 'vala': $languageClass = 'language-vala'; break;
                                case 'vapi': $languageClass = 'language-vala'; break;
                                case 'vb': $languageClass = 'language-vbnet'; break;
                                case 'resx': $languageClass = 'language-vbnet'; break;
                                case 'aspx': $languageClass = 'language-vbnet'; break;
                                case 'vbs': $languageClass = 'language-vbscript'; break;
                                case 'wasm': $languageClass = 'language-wasm'; break;
                                case 'xml': $languageClass = 'language-xml'; break;
                                case 'yaml': $languageClass = 'language-yaml'; break;
                                case 'yml': $languageClass = 'language-yaml'; break;
                            }

                            echo "<div class='project-content'><h4><a download='$newFileName' onclick='showDownloadWarning(this);return false' href='$dl_link'>$newFileName</a></h4><pre><code class='$languageClass'>$content</code></pre></div><br><br>";
                        } else if ($is_binary) {
                            $newFileName = str_replace('_', '.', $fileInfo['filename']) . '.' . $fileInfo['extension'];
                            $newFileName = substr($newFileName, 0, -4); // Retirer ".txt" à la fin
                            echo "<div class='project-content'><h4><a download='$newFileName' onclick='showDownloadWarning(this);return false' href='$dl_link'>$newFileName</a></h4>(fichier contenant des caractères non-imprimable)</div><br><br>";
                        }
                    }
                } else {
                    echo "<div class='alert'>Aucun fichier trouvé pour ce projet...</div>";
                }
                echo "</ul></div><br>";
            } else {
                echo "<h1><div class='projects'> <div class='alert'><strong>ID invalide !</strong></div></div></h1>";
                exit();
            }
            ?>
        </div>
    </main>
    <script src="/highlight/highlight.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            hljs.highlightAll();
        });

        function showDownloadWarning(e) {
            setTimeout(function () {
                alert("ATTENTION: Nous ne sommes pas responsables si les fichiers téléchargés contiennent des virus. Nous faisont de notre mieux pour éviter ce cas, mais il faut toujours rester attentif et ne pas éxécuter du code que vous ne comprenez pas!");
                let linkOpener = document.createElement("a");
                linkOpener.href = e.href;
                linkOpener.target = "_blank";
                linkOpener.download = e.download;
                linkOpener.click();
            }, 100);
        }
    </script>
</body>
</html>
