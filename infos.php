<?php
require_once 'init.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Sharing - Infos</title>
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
            <div class="conditionsutilisation">
                <h1>Infos de la <?php echo $vwebsite; ?> du site :</h1>
        
                <h2>1. Nouveautés</h2>
                <p>Voici les nouveautés du site :</p>
                <ul>
                    <li><strong>Modification des <a href="https://project-sharing.fr.to/conditionsutilisation">conditions d'utilisation</a> :</strong> Les <strong><a href="https://project-sharing.fr.to/conditionsutilisation">conditions d'utilisation</a></strong> ont été modifiées, à vous d'aller voir ces modifications.</li>
                    <li><strong>Correction de bugs :</strong> Des bugs ont été corrigés par ci par là sur le site, mais si vous en voyez d'autres, signalez-les nous sur le serveur Discord.</li>
                    <li><strong>Collaboration :</strong> Nous avons toujours la collaboration avec Yatsuko et son moteur de recherche <strong><a href="https://weeble.fr/">Weeble</a></strong>.</li>
                    <li><strong>Premium :</strong> Un système de compte premium encore en cours de développement, mais les personnes qui sont premium peuvent mettre l'url de leur chaîne YouTube, et la dernière vidéo s'affichera en dessous de leur compte.</li>
                    <li><strong>Page "404" et "403" :</strong> La page "404" et "403" a été créée.</li>
                    <li><strong>Des logs :</strong> Des logs ont été mis en place pour savoir qui a fait quoi et quand, pour plus de sécurité.</li>
                </ul>
        
                <h2>2. Développeurs</h2>
                <p>Voici les développeurs qui ont codé la <?php echo $vwebsite; ?> du site :</p>
                <ul>
                    <li><strong><a href="/profile?pseudo=NatCode">NatCode</a></strong></li>
                    <li><strong><a href="/profile?pseudo=Clovis">Clovis</a></strong></li>
                </ul>
        
                <h2>3. Auditeurs de sécurité</h2>
                <p>Voici les personnes chargées de vérifier la sécurité et vérifier le fonctionnement du site :</p>
                <ul>
                    <li><strong><a href="/profile?pseudo=InfiniteStall">InfiniteStall</a></strong></li>
                </ul>

                <h1>Infos de la 2.1 du site :</h1>
        
                <h2>1. Nouveautés</h2>
                <p>Voici les nouveautés du site :</p>
                <ul>
                    <li><strong>Modification des <a href="https://project-sharing.fr.to/conditionsutilisation">conditions d'utilisation</a> :</strong> Les <strong><a href="https://project-sharing.fr.to/conditionsutilisation">conditions d'utilisation</a></strong> ont été modifiées, à vous d'aller voir ces modifications.</li>
                    <li><strong>Correction de bugs :</strong> Des bugs ont été corrigés par ci par là sur le site, mais si vous en voyez d'autres, signalez-les nous sur le serveur Discord.</li>
                    <li><strong>Ajout de la page "mes abonnés" :</strong> Une page "mes abonnés" a été ajoutée, vous pouvez maintenant voir les personnes qui se sont abonnées à votre compte ainsi que leurs projets.</li>
                    <li><strong>Collaboration :</strong> Nous avons toujours la collaboration avec Yatsuko et son moteur de recherche <strong><a href="https://weeble.fr/">Weeble</a></strong>.</li>
                    <li><strong>Premium :</strong> Un système de compte premium encore en cours de développement.</li>
                    <li><strong>Pixel War :</strong> La Pixel War s'ouvre maintenant dans un nouvel onglet, vous permettant d'y accéder tout en restant sur le site.</li>
                    <li><strong>Corrections :</strong> De petites failles de sécurité ont été corrigés.</li>
                    <li><strong>Ajout de rôles :</strong> Des rôles ont été ajoutés aux modérateurs.</li>
                    <li><strong>Page "Problem" :</strong> La page "Problem" a été améliorée pour que les modérateurs et les administrateurs puissent supprimer ou ajouter des problèmes afin de communiquer et d'améliorer le site.</li>
                    <li><strong>Dernière vidéo sous mon compte :</strong> Ma dernière vidéo de ma chaîne YouTube s'affiche en dessous de mon profil <strong><a href="/profile?pseudo=NatCode">ici</a></strong>.</li>
                </ul>
        
                <h2>2. Développeurs</h2>
                <p>Voici les développeurs qui ont codé la <?php echo $vwebsite; ?> du site :</p>
                <ul>
                    <li><strong><a href="/profile?pseudo=NatCode">NatCode</a></strong></li>
                </ul>
        
                <h2>3. Auditeurs de sécurité</h2>
                <p>Voici les personnes chargées de vérifier la sécurité et vérifier le fonctionnement du site :</p>
                <ul>
                    <li><strong><a href="/profile?pseudo=InfiniteStall">InfiniteStall</a></strong></li>
                </ul>
        
                <h1>Infos de la v2.0 du site :</h1>
        
                <h2>1. Nouveautés</h2>
                <p>Voici les nouveautés du site :</p>
                <ul>
                    <li><strong>Système de like :</strong> Les personnes peuvent enfin liker ou disliker des projets. Merci à InfiniteStall qui a trouvé quelques bugs "mineurs" (qui supprimait tout les likes d'un projet par exemple)</li>
                    <li><strong>Amélioration du code :</strong> Le code a été amélioré.</li>
                    <li><strong>Pixel War :</strong> La Pixel War a maintenant un chat en direct et on peux se connecter directement depuis le site NatCode.fr.to avec son compte Project-Sharing si Project-Sharing est en pause.</li>
                    <li><strong>Premium :</strong> Un système de compte premium encore en cours de développement.</li>
                    <li><strong>Popup :</strong> Un système de pop-up fonctionnel.</li>
                    <li><strong>Collaboration :</strong> Une collaboration avec Yatsuko et son moteur de recherche <strong><a href="https://weeble.fr/">Weeble</a></strong>.</li>
                    <li><strong>Page "mes projets" :</strong> Une page web pour voir ses projets.</li>
                    <li><strong>Page "infos" :</strong> Ajout de cette page web.</li>
                    <li><strong>Page "panel" :</strong> La page web "panel" a été améliorée, les Modérateurs et les Administrateurs peuvent maintenant arrêter le site à tout moment, changer la version du site / des conditions d'utilisation, afficher certaines popups sur le site, mettre les comptes en privé / public lorsqu'une personne s'enregistre !</li>
                    <li><strong>Style :</strong> Le CSS a été complètement refait par InfiniteStall.</li>
                    <li><strong>Corrections :</strong> De nombreuses failles de sécurité permettant de mettre des likes à l'infini sans se connecter ou encore de supprimer le projet de n'importe qui sans se connecter ont été corrigés.</li>
                    <li><strong>Code source :</strong> Le code source du site n'est plus disponible publiquement. Seul les modérateurs ont accès à une copie du code depuis l'ancienne page de téléchargement, et les administrateurs au code actuel du site hébergé sur GitHub ainsi que la permission de le modifier librement.</li>
                    <li><strong>Prévisualisation des fichiers :</strong> Les types de fichiers lisibles par des humains (tel que le code) sont affichés avec des couleurs. Les images sont aussi affichées correctement, et les autres fichiers ne sont pas visible en ligne (bien que listés dans les fichiers).</li>
                    <li><strong>Téléchargement :</strong> Chaque fichier peut maintenant être téléchargé invididuellement en cliquant sur son nom. Un message apparait alors pour avertir les utilisateurs des risques de virus dans les fichiers uploadés.</li>
                    <li><strong>Autres :</strong> De nombreux autres changements ont été effectués, comme le changement de noms de variables ou la réécriture complète de certains passages de certains fichiers.</li>
                </ul>
        
                <h2>2. Développeurs</h2>
                <p>Voici les développeurs qui ont codé la <?php echo $vwebsite; ?> du site :</p>
                <ul>
                    <li><strong><a href="/profile?pseudo=NatCode">NatCode</a></strong></li>
                    <li><strong><a href="/profile?pseudo=HGStyle">HGStyle</a></strong></li>
                    <li><strong><a href="/profile?pseudo=InfiniteStall">InfiniteStall</a></strong></li>
                </ul>
        
                <h2>3. Auditeurs de sécurité</h2>
                <p>Voici les personnes chargées de vérifier la sécurité et vérifier le fonctionnement du site :</p>
                <ul>
                    <li><strong><a href="/profile?pseudo=InfiniteStall">InfiniteStall</a></strong></li>
                    <li><strong><a href="/profile?pseudo=HGStyle">HGStyle</a></strong></li>
                </ul>
                <p>ainsi que tous les développeurs du site.</p>
            </div>
        </div>
    </main>
</body>
</html>
