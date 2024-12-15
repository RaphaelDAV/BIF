<?php
// Inclure la connexion à la base de données
require "pdo.php";

// Vérifier que la session est démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification de l'ID utilisateur dans la session
if(isset($_SESSION['id_utilisateurs'])){
    $id = $_SESSION['id_utilisateurs'];
} else {
    header('Location: ../index.php');
    exit();
}


// Récupérer les informations de l'utilisateur connecté
$query = "SELECT identifiant, siren, raison_sociale FROM bif_utilisateurs WHERE id_utilisateurs = ?";
$stmt = $mysqli->prepare($query);
if ($stmt === false) {
    die('Erreur de préparation de la requête : ' . $mysqli->error);
}

$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

// Vérification si l'utilisateur existe
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();  // Récupérer les informations de l'utilisateur
} else {
    die('Utilisateur non trouvé.');
}

$stmt->close();

// Génération du token CSRF
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Gestion de la mise à jour du profil
if (isset($_POST['submit'])) {
    $password = $_POST['password'];
    $siren = $_POST['siren'];
    $raison = $_POST['raison'];

    // Préparer la mise à jour
    $query = "UPDATE bif_utilisateurs SET siren = ?, raison_sociale = ? WHERE id_utilisateurs = ?";
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query = "UPDATE bif_utilisateurs SET password = ?, siren = ?, raison_sociale = ? WHERE id_utilisateurs = ?";
    }

    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        die('Erreur de préparation de la requête : ' . $mysqli->error);
    }

    if (!empty($password)) {
        $stmt->bind_param('sssi', $hashed_password, $siren, $raison, $id);
    } else {
        $stmt->bind_param('ssi', $siren, $raison, $id);
    }

    if ($stmt->execute()) {
        echo "";
    } else {
        echo "Erreur lors de la mise à jour : " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client</title>
    <link rel="stylesheet" href="../css/profil.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Unicase:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Paytone+One&display=swap');
    </style>
</head>
<body>
<?php
include ("header.inc.php");
?>
<main class="container-profil">
    <h1>Profil</h1>
    <form method="post" class="info-profil">
        <!-- Champ caché pour le token CSRF -->
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <label for="password">Mot de passe  :</label>
        <input name="password" type="password">

        <label for="siren">Siren :</label>
        <input name="siren" type="text" value="<?php echo htmlspecialchars($user['siren']); ?>" required>

        <label for="raison">Raison sociale :</label>
        <input name="raison" type="text" value="<?php echo htmlspecialchars($user['raison_sociale']); ?>" required>

        <p>Rôle :     <?php echo $_SESSION['role'] ?></p>

        <button type="submit" name="submit">Enregistrer</button>
    </form>

    <div class="details-profil">
        <!-- Code pour afficher les statistiques de remises, transactions, et impayés -->
        <div class="detail">
            <h2>Nombre de remises</h2>
            <p>
                <?php
                try {
                    $remises = $mysqli->prepare('SELECT COUNT(*) as total_remises FROM bif_remise WHERE id_utilisateurs = ?');
                    $remises->bind_param('i', $id);
                    $remises->execute();
                    $result = $remises->get_result();
                    if ($row = $result->fetch_assoc()) {
                        echo $row['total_remises'];
                    } else {
                        echo "0"; // Si aucun résultat n'est retourné
                    }
                } catch (mysqli_sql_exception $e) {
                    echo "Erreur SQL : " . $e->getMessage();
                }
                ?>
            </p>
        </div>

        <div class="detail">
            <h2>Nombre de transactions</h2>
            <p><?php
                try {
                    $trans = $mysqli->prepare('SELECT COUNT(*) as total_transaction FROM bif_transaction tr
                                     left join bif_remise r on tr.num_remise = r.num_remise WHERE r.id_utilisateurs = ?');
                    $trans->bind_param('i', $id);
                    $trans->execute();
                    $result = $trans->get_result();
                    if ($row = $result->fetch_assoc()) {
                        echo $row['total_transaction'];
                    } else {
                        echo "0"; // Si aucun résultat n'est retourné
                    }
                } catch (mysqli_sql_exception $e) {
                    echo "Erreur SQL : " . $e->getMessage();
                }
                ?></p>
        </div>
        <div class="detail">
            <h2>Nombre d’impayés</h2>
            <p><?php
                try {
                    $trans = $mysqli->prepare('SELECT COUNT(*) as total_impaye FROM bif_impaye imp left join 
                                     bif_transaction tr on imp.id_transaction = tr.id_transaction 
                                     join bif_remise r on tr.num_remise = r.num_remise WHERE r.id_utilisateurs = ?');
                    $trans->bind_param('i', $id);
                    $trans->execute();
                    $result = $trans->get_result();
                    if ($row = $result->fetch_assoc()) {
                        echo $row['total_impaye'];
                    } else {
                        echo "0"; // Si aucun résultat n'est retourné
                    }
                } catch (mysqli_sql_exception $e) {
                    echo "Erreur SQL : " . $e->getMessage();
                }
                ?></p>
        </div>
    </div>
</main>
<?php
include ("footer.inc.php");
?>
</body>
</html>
