
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
var_dump($_SESSION);
require 'pdo.php';  // Vérifiez que ce fichier configure bien MySQLi

// Initialisation du compteur de tentatives si ce n'est pas déjà fait
if (!isset($_SESSION['tentatives'])) {
    $_SESSION['tentatives'] = 0;
}

if (isset($_POST['submit'])) {
    // Vérification que les champs identifiant et password ne sont pas vides
    if (!empty($_POST['identifiant']) && !empty($_POST['password'])) {
        $pseudo = htmlspecialchars(trim($_POST['identifiant']));
        $mdp = trim($_POST['password']);  // On nettoie également le mot de passe

        // Préparation de la requête MySQLi
        try {
            $recupUser = $mysqli->prepare('SELECT * FROM BIF_utilisateurs WHERE identifiant = ?');
            if (!$recupUser) {
                throw new Exception('Erreur interne. Veuillez réessayer plus tard.');
            }
        } catch (Exception $e) {
            error_log($e->getMessage()); // Log de l'erreur côté serveur
            header('Location: login.php?error=Erreur interne');
            exit();
        }


        // Liaison des paramètres
        $recupUser->bind_param('s', $pseudo);
        $recupUser->execute();

        // Récupération des résultats
        $result = $recupUser->get_result();
        $user = $result->fetch_assoc();

        // Vérification de l'existence de l'utilisateur
        if ($user) {
            // Vérification du mot de passe
            if (password_verify($mdp, $user['password'])) {
                // Réinitialiser le compteur de tentatives en cas de succès
                $_SESSION['tentatives'] = 0;

                // Stockage des informations de l'utilisateur dans la session
                $_SESSION['identifiant'] = $user['identifiant'];
                $_SESSION['id_utilisateurs'] = $user['id_utilisateurs'];
                $_SESSION['raison_sociale'] = $user['raison_sociale'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['siren'] = $user['siren'];

                // Redirection en fonction du rôle de l'utilisateur
                switch ($user['role']) {
                    case 'Admin':
                        header('Location: ../admin/admin.php?user_id=' . $user['id_utilisateurs']);
                        exit();
                    case 'ProductOwner':
                        header('Location: product_owner.php?user_id=' . $user['id_utilisateurs']);
                        exit();
                    case 'Client':
                        header('Location: client.php?id=' . $user['id_utilisateurs']);
                        exit();
                    default:
                        header('Location: login.php?error=Rôle inconnu');
                        exit();
                }
            } else {
                // Incrémenter le compteur de tentatives si on est en dessous de 3
                if ($_SESSION['tentatives'] < 3) {
                    $_SESSION['tentatives']++;
                }

                // Vérification du nombre de tentatives restantes
                $tentativesRestantes = max(0, 3 - $_SESSION['tentatives']);

                if ($_SESSION['tentatives'] >= 3) {
                    header('Location: login.php?error=ATTENTION : Vous avez dépassé le nombre maximum de tentatives&tentatives=' . $tentativesRestantes);
                } elseif ($_SESSION['tentatives'] == 2) {
                    header('Location: login.php?error=ATTENTION : C\'est votre dernier essai…&tentatives=' . $tentativesRestantes);
                } else {
                    header('Location: login.php?error=Identifiants incorrects&tentatives=' . $tentativesRestantes);
                }
                exit();
            }
        } else {
            // Utilisateur non trouvé - Incrémenter les tentatives si en dessous de 3
            if ($_SESSION['tentatives'] < 3) {
                $_SESSION['tentatives']++;
            }

            // Vérification du nombre de tentatives restantes
            $tentativesRestantes = max(0, 3 - $_SESSION['tentatives']);

            if ($_SESSION['tentatives'] >= 3) {
                header('Location: login.php?error=ATTENTION : Vous avez dépassé le nombre maximum de tentatives&tentatives=' . $tentativesRestantes);
            } elseif ($_SESSION['tentatives'] == 2) {
                header('Location: login.php?error=ATTENTION : C\'est votre dernier essai…&tentatives=' . $tentativesRestantes);
            } else {
                header('Location: login.php?error=Identifiants incorrects&tentatives=' . $tentativesRestantes);
            }
            exit();
        }
    } else {
        // Champs vides
        $tentativesRestantes = max(0, 3 - $_SESSION['tentatives']);
        header('Location: login.php?error=Veuillez remplir tous les champs&tentatives=' . $tentativesRestantes);
        exit();
    }
} else {
    // Si le formulaire n'a pas été soumis correctement
    $tentativesRestantes = max(0, 3 - $_SESSION['tentatives']);
    header('Location: ../index.php?error=Erreur de soumission&tentatives=' . $tentativesRestantes);
    exit();
}
