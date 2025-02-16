<?php    
    session_start();

    // Définition du fuseau horaire
    date_default_timezone_set('Europe/Paris');

    // variables
    $url = $_SERVER['REQUEST_URI'];
    $current_time = time();
    $current_date_time = (new DateTime())->format('Y-m-d H:i:s');
    $current_date = new DateTime();
    $myLogoDirectory = "/img/profil_logo_default.png";
    $myPseudo = "Se connecter";
    $statutAdmin = 3;
    $statutModo = 2;
    $statutUser = 1;
    $showPub = false;
    $jesuisabo = false;
    $showInfo = false;
    $showDiscord = false;
    $myUser_id = false;
    $myStatutInt = false;
    $targetPremium = false;
    $info_ou_pub = false;
    $jesuisunadmin = false;
    $message = false;
    $jesuisunmodo = false;
    $form_delete_account = false;
    $bio = false;
    $targetPseudo = false;
    $subscribe = false;
    $subscribe = false;
    $errors = [];
    $abonnesIds = [];
    $myabonnesIds = [];
    $usersByType = [
        'staff' => [],
        'role' => [],
        'premium' => [],
        'nonPremium' => []
    ];
    $new_password_verify = 0;
    $myPremium = 0;
    
    // connextion à la base de donnée
    require_once "NE_PAS_SUPPRIMER.php";

    // fonction pour se déco puis aller sur la page demmandée ou non
    function logout($location, $exit) {

        session_unset();
        session_destroy();

        if ($location) {
            header("Location: $location");
        }

        if ($exit) {
            exit();
        }
    }

    // vérifier notre session
    if (isset($_SESSION['user_id'])) {
        if (!isset($_SESSION['password'])) {
            logout(false, false);
        } else {
            $sql = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $sql->execute([$_SESSION['user_id']]);
            $result = $sql->fetch(PDO::FETCH_ASSOC);
    
            if ($result) {
                if ($_SESSION['password'] != $result['password']) {
                    logout(false, false);
                } else {
                    $myUser_id = $_SESSION['user_id'];
    
                    $sql = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $sql->execute([$myUser_id]);
                    $result = $sql->fetch(PDO::FETCH_ASSOC);
            
                    if ($result) {
                        $myStatutInt = (int)$result['statut'];
                        $myRoleInt = (int)$result['role'];
                        $myPseudo = htmlspecialchars($result['pseudo']);
                        $myPopupDiscord = (int)$result['popup_discord'];
                        $my_info_ou_pub = (int)$result['info_ou_pub'];
                        $myPremium = (int)$result['premium'];
                        $myIdChannelYt = $result['idChannelYt'];
                        $myEmail = $result['email'];
                        $myIs_public = $result['is_public'];
                    } else {
                        logout(false, false);
                    }
                
                    $userDir = $_SERVER['DOCUMENT_ROOT'] . "/users/$myUser_id";
                    if (!file_exists($userDir)) {
                        mkdir($userDir, 0777, true);
                    }

                    $myLogoDirectory = "/users/$myUser_id/logo.png";     
                    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $myLogoDirectory)) {
                        $myLogoDirectory = "/img/profil_logo_default.png";
                    }
                }
            } else {
                logout(false, false);
            }
        }
    }

    // fonction pour afficher les utilisateurs (allusers)
    function allusers($mysubscribe, $subscribe) {

        global $pdo, $myUser_id, $usersByType, $statutModo;

        $requete = "
        SELECT 
            users.id AS user_id, 
            users.pseudo AS user_pseudo, 
            users.statut AS user_statut,
            users.role AS user_role,
            users.premium AS user_premium, 
            users.ban AS user_ban, 
            delete_account.id AS delete_account_id 
        FROM users 
        LEFT JOIN delete_account ON users.id = delete_account.user_id 
        WHERE users.ban = 0 AND delete_account.id IS NULL 
        ORDER BY RAND()";

        $sql = $pdo->query($requete);

        if ($sql->rowCount() > 0) {
            if ($subscribe) {
                $requete2 = "SELECT user_id FROM abonnements WHERE user_id_who_abo = ?";
                $result = $pdo->prepare($requete2);
                $result->execute([$myUser_id]);
                $abonnes = $result->fetchAll(PDO::FETCH_ASSOC);
            
                // S'assurer que le tableau des abonnés n'est pas vide
                if (empty($abonnes)) {
                    echo "<div class='alert'><p>Aucun abonné trouvé.</p></div>";
                    return; // Arrête l'exécution du code si aucun abonné n'est trouvé
                } else {
                    foreach ($abonnes as $abonne) {
                        $abonnesIds[] = (int)$abonne['user_id'];
                    }
                }
            }

            if ($mysubscribe) {
                $requete2 = "SELECT user_id_who_abo FROM abonnements WHERE user_id = ?";
                $result = $pdo->prepare($requete2);
                $result->execute([$myUser_id]);
                $abonnes = $result->fetchAll(PDO::FETCH_ASSOC);
            
                // S'assurer que le tableau des abonnés n'est pas vide
                if (empty($abonnes)) {
                    echo "<div class='alert'><p>Aucun abonné trouvé.</p></div>";
                    return; // Arrête l'exécution du code si aucun abonné n'est trouvé
                } else {
                    foreach ($abonnes as $abonne) {
                        $myAbonnesIds[] = (int)$abonne['user_id_who_abo'];
                    }
                }
            }
            
            foreach ($sql->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
                $targetUser_id = (int)$ligne['user_id'];
                
                // Vérification de l'abonnement pour les abonnés
                if ($subscribe && !in_array($targetUser_id, $abonnesIds)) {
                    continue;
                }

                if ($mysubscribe && !in_array($targetUser_id, $myAbonnesIds)) {
                    continue;
                }
                
                // Tri par statut
                if ((int)$ligne['user_statut'] >= $statutModo) {
                    $usersByType['staff'][] = $ligne;
                } elseif ((int)$ligne['user_premium']) {
                    $usersByType['premium'][] = $ligne;
                } elseif ((int)$ligne['user_role']) {
                    $usersByType['role'][] = $ligne;
                } else {
                    $usersByType['nonPremium'][] = $ligne;
                }
            }        

            function displayUsers($users, $title) {
                if (empty($users)) return;
                echo "<h2>$title :</h2>
                    <div class='users-container'>";
                foreach ($users as $ligne) {
                    $targetUser_id = (int)$ligne['user_id'];
                    $targetPseudo = htmlspecialchars($ligne['user_pseudo']);
                    $targetStatutInt = (int)$ligne['user_statut'];

                    $targetStatut = getStatutInt($targetStatutInt);

                    $userProfileUrl = '/profile?pseudo=' . urlencode($targetPseudo);
                    $logoDirectory = "/users/$targetUser_id/logo.png";
                    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $logoDirectory)) {
                        $logoDirectory = "/img/profil_logo_default.png";
                    }

                    echo "<div class='user-profile'>
                            <a href='$userProfileUrl' title='Voir le profil de $targetPseudo'>
                                <img class='logo' src='$logoDirectory' alt='Logo'>
                                <p><strong>$targetPseudo</strong><br>($targetStatut)</p>
                            </a>
                        </div>";
                }
                echo "</div>";
            }
                
            displayUsers($usersByType['staff'], 'Staff');
            displayUsers($usersByType['role'], 'Rôle');
            displayUsers($usersByType['premium'], 'Premiums');
            displayUsers($usersByType['nonPremium'], 'Utilisateurs');
        } else {
            echo "<div class='alert'><p>Aucun utilisateur enregistré.</p></div>";
        }
    }

    // fonction email
    function sendmail($from_addr, $to_addr, $subject, $content) {
        return mail($to_addr, $subject, $content, "From: " . $from_addr . "\r\nReply-To: " . $from_addr . "\r\nX-Mailer: PHP/" . phpversion());
    }

    // fonction pour le statut de l'utilisateur affiché
    function getStatutInt($a) {
        global $statutModo, $statutAdmin, $statutUser;

        $statut = [
            $statutUser => 'Utilisateur',
            $statutModo => 'Modérateur',
            $statutAdmin => 'Administrateur',
        ];

        if (array_key_exists($a, $statut)) {
            return $statut[$a];
        } return "Error";
    }

    // fonction pour entrer un log
    function insertLog($command, $user_id, $targetUser_id) { // $command = action faite, $user_id = personne qui à faite l'action, $targetUser_id = personne cible si il y en a une

        global $pdo, $current_date_time, $url;

        $pseudo_sos_1 = null;
        $pseudo_sos_2 = null;

        $sql = $pdo->prepare("SELECT pseudo FROM users WHERE id = ?");
        $sql->execute([$user_id]);
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $pseudo_sos_1 = htmlspecialchars($row['pseudo']);
        }

        if ($targetUser_id != null) {
            $sql = $pdo->prepare("SELECT pseudo FROM users WHERE id = ?");
            $sql->execute([$targetUser_id]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $pseudo_sos_2 = htmlspecialchars($row['pseudo']);
            }
        }

        $sql = $pdo->prepare("INSERT INTO logs (user_id, targetUser_id, pseudo_sos_1, pseudo_sos_2, command, date, page) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $sql->execute([$user_id, $targetUser_id, $pseudo_sos_1, $pseudo_sos_2, $command, $current_date_time, $url]);
    }

    // fonction pour afficher les logs
    function getLog($command, $user_id, $targetUser_id, $date, $page, $pseudo_sos_1, $pseudo_sos_2) {

        global $pdo;

        $sql = $pdo->prepare("SELECT pseudo FROM users WHERE id = ?");
        $sql->execute([$user_id]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);
            
        if ($result) {
           $targetPseudo = $result['pseudo'];
        } else {
            $targetPseudo = "Utilisateur suprimé (user_id = $user_id)";
        }

        if ($targetUser_id != null) {
            $sql = $pdo->prepare("SELECT pseudo FROM users WHERE id = ?");
            $sql->execute([$targetUser_id]);
            $result = $sql->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $targetPseudo2 = $result['pseudo'];
            } else {
                $targetPseudo2 = "[Utilisateur suprimé]";
            }
        }

        $logs = [
            1 => "L'utilisateur '$targetPseudo' (anciennement $pseudo_sos_1) s'est connecté le '$date' sur la page '$page'.",
            2 => "L'utilisateur '$targetPseudo' (anciennement $pseudo_sos_1) s'est enregistré le '$date' sur la page '$page'.",
            3 => "L'utilisateur '$targetPseudo' (anciennement $pseudo_sos_1) a désactivé son compte le '$date' sur la page '$page'.",
            4 => "L'utilisateur '$targetPseudo' (anciennement $pseudo_sos_1) a désactivé le compte de '$targetPseudo2' (anciennement $pseudo_sos_2) le '$date' sur la page '$page'.",
            5 => "Le compte de '$targetPseudo2' (anciennement $pseudo_sos_2) a été supprimé définitivement le '$date' car la date limite a été dépassée (sur la page '$page' par '$targetPseudo' (anciennement $pseudo_sos_1)).",
            6 => "Le compte de '$targetPseudo2' (anciennement $pseudo_sos_2) a été supprimé définitivement le '$date' par '$targetPseudo' (anciennement $pseudo_sos_1) sur la page '$page'.",
            7 => "L'utilisateur '$targetPseudo' (anciennement $pseudo_sos_1) a réactivé le compte de '$targetPseudo2' (anciennement $pseudo_sos_2) le '$date' sur la page '$page'.",
            8 => "L'utilisateur '$targetPseudo' (anciennement $pseudo_sos_1) a débanni le compte de '$targetPseudo2' (anciennement $pseudo_sos_2) le '$date' sur la page '$page'.",
            9 => "L'utilisateur '$targetPseudo' (anciennement $pseudo_sos_1) à modifier les paramètres du site le '$date' sur la page '$page'.",
            10 => "L'utilisateur '$targetPseudo' (anciennement $pseudo_sos_1) s'est abonné au compte de '$targetPseudo2' (anciennement $pseudo_sos_2) le '$date' sur la page '$page'.",
            11 => "L'utilisateur '$targetPseudo' (anciennement $pseudo_sos_1) s'est déabonné du compte de '$targetPseudo2' (anciennement $pseudo_sos_2) le '$date' sur la page '$page'.",
            12 => "L'utilisateur '$targetPseudo' (anciennement $pseudo_sos_1) à créer un projet le '$date' sur la page '$page'.",
            13 => "L'utilisateur '$targetPseudo' (anciennement $pseudo_sos_1) a banni le compte de '$targetPseudo2' (anciennement $pseudo_sos_2) le '$date' sur la page '$page'.",
            14 => "L'utilisateur '$targetPseudo' (anciennement $pseudo_sos_1) s'est connecté le '$date' sur la page '$page' depuis l'application PSapp.",
        ];

        if (array_key_exists($command, $logs)) {
            return $logs[$command];
        } return "L'utilisateur '$targetPseudo' a fait une action inconnue ($command) le $date sur la page '$page'.";
    }

    function location($url) {
        header("Location: $url");
        exit();
    }

    // fonction pour le rôle de l'utilisateur affiché
    function getRoleInt($a) {
        $role = [
            0 => 'Aucun',
            1 => 'Bras droit de NatCode',
            2 => 'Testeur',
            3 => 'Contributeur',
            4 => 'Gestionnaire de communauté',
            5 => 'Responsable technique',
            6 => 'Créateur de contenu',
            7 => 'Développeur',
            8 => 'Rédacteur',
            9 => 'Animateur de forums',
            10 => 'Designer graphique',
            11 => 'Auditeur de sécurité',
            12 => 'Invité spécial',
            13 => 'Responsable des partenariats',
            14 => "Organisateur d'événements en ligne",
        ];

        if (array_key_exists($a, $role)) {
            return $role[$a];
        } return "Error";
    }

    // fonction pour obtenire le logo d'un utilisateur :
    function getLogo($targetUser_id) {
        $logoDirectory = "/users/$targetUser_id/logo.png";
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $logoDirectory)) {
            $logoDirectory = "/img/profil_logo_default.png";
        }
        return $logoDirectory;
    }

    // suprimer les comptes en cours de supresion si cela fais plus de 30 jours !
    try {    
        $sql = $pdo->query("SELECT user_id, date FROM delete_account");
    
        // Vérifie si des enregistrements sont trouvés
        if ($sql->rowCount() > 0) {
            while ($ligne = $sql->fetch(PDO::FETCH_ASSOC)) {
                $targetUser_id = (int)$ligne['user_id'];
                $date_account = new DateTime($ligne['date']);
                
                $interval = $current_date->diff($date_account);
                $days_passed = $interval->days;
    
                // Si 30 jours ou plus se sont écoulés
                if ($days_passed >= 30) {
                    $pdo->beginTransaction();
    
                    try {
                        $log = insertLog(5, $myUser_id, $targetUser_id);

                        // Suppression de l'utilisateur
                        $sql_delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        $sql_delete->execute([$targetUser_id]);
    
                        // Suppression de l'enregistrement de delete_account
                        $sql_delete_deleted = $pdo->prepare("DELETE FROM delete_account WHERE user_id = ?");
                        $sql_delete_deleted->execute([$targetUser_id]);
                        
                        // Suppression des abonnements
                        $sql_delete_abo = $pdo->prepare("DELETE FROM abonnements WHERE user_id = ?");
                        $sql_delete_abo->execute([$targetUser_id]);
                        $sql_delete_abo2 = $pdo->prepare("DELETE FROM abonnements WHERE user_id_who_abo = ?");
                        $sql_delete_abo2->execute([$targetUser_id]);
    
                        // Suppression des rapports liés à l'utilisateur
                        $delete_reports_query = $pdo->prepare("DELETE FROM user_reports WHERE user_id_who_reported = ?");
                        $delete_reports_query->execute([$targetUser_id]);
    
                        // Suppression des fichiers et du répertoire utilisateur
                        $userDir = $_SERVER['DOCUMENT_ROOT'] . "/users/$targetUser_id";
                        if (is_dir($userDir)) {
                            $objects = scandir($userDir);
                            foreach ($objects as $object) {
                                if ($object != "." && $object != "..") {
                                    if (filetype($userDir."/".$object) == "dir") rmdir($userDir."/".$object); else unlink($userDir."/".$object);
                                }
                            }
                            reset($objects);
                            rmdir($userDir);
                        }
    
                        // Validation de la transaction
                        $pdo->commit();
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        throw $e; // Relance l'exception pour un traitement global
                    }
                }
            }
        }
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }

    // vérifier si le site est en pause
    $sql = $pdo->prepare("SELECT * FROM administration");
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $sql = $pdo->prepare("INSERT INTO administration (stopsite, stopsite_maintenance_end, stoppixelwar, info_ou_pub, is_public_de_base, statut_de_base, popupdiscord, vwebsite, vconditionsutilisation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $sql->execute([1, null, 1, 1, 1, 2, 1, "vERROR", "vERROR"]); // 1, null, 1 par défaut pour protéger le site !
        $result = [1, 1, 1, 1, 2, 1, "vERROR", "vERROR"];
    }

    // autres variables
    $stopsite = (int)$result['stopsite'];
    $maintenance_end = $result['stopsite_maintenance_end'] ?? null;
    $info_ou_pub = (int)$result['info_ou_pub']; // 0 = rien, 1 = pub et 2 = info
    $is_public_de_base = (int)$result['is_public_de_base'];
    $statut_de_base = (int)$result['statut_de_base'];
    $vwebsite = $result['vwebsite'];
    $vconditionsutilisation = $result['vconditionsutilisation'];
    $popupDiscord = $result['popupdiscord'];

    if ($url === '/stopsite' && $stopsite === 1) {
        // Vérification de la fin de la maintenance
        if ($maintenance_end !== null) {
            if ($current_date_time >= $maintenance_end) {
                try {
                    // Mise à jour de l'état de maintenance dans la base de données
                    $stmt = $pdo->prepare("UPDATE administration SET stopsite = 0, stopsite_maintenance_end = null WHERE id = 1");
                    $stmt->execute();
            
                    // Redirection après la mise à jour
                    header('Location: /');
                    exit();
                } catch (PDOException $e) {
                    error_log("Erreur lors de la mise à jour de la base de données : " . $e->getMessage());
                    die("Erreur. Veuillez réessayer plus tard.");
                }
            } else {
                logout(false, false);
            }
        }
    } else if ($_SERVER['REQUEST_URI'] === '/stopsite' && $stopsite !== 1) {
        header('Location: /');
        exit();
    } else {
        if ($stopsite === 1) {
            header('Location: /stopsite');
            exit();
        }
    }
?>
