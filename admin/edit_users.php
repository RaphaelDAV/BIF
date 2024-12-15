<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['id_utilisateurs']) || $_SESSION['role'] != "Admin") {
    header('Location: ../src/login.php');
    exit();
}

include("../src/pdo.php");  // Assurez-vous que ce fichier contient bien votre connexion à mysqli, pas PDO.

if (isset($_POST['update']) && isset($_POST['user_id'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }

    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $role = $_POST['role'];

    // Préparation de la requête avec mysqli
    $query = "UPDATE bif.bif_utilisateurs SET identifiant = ?, role = ? WHERE id_utilisateurs = ?";
    $stmt = $mysqli->prepare($query);

    if ($stmt === false) {
        die('Erreur de préparation de la requête : ' . $mysqli->error);
    }

    // On utilise "s" pour string, et "i" pour integer
    $stmt->bind_param('ssi', $username, $role, $user_id);

    if ($stmt->execute()) {
        header('Location: admin.php');
        exit();
    } else {
        echo "Erreur lors de l'exécution de la requête : " . $stmt->error;
    }

    $stmt->close();
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $query = "SELECT id_utilisateurs, identifiant, role FROM bif.bif_utilisateurs WHERE id_utilisateurs = ?";
    $stmt = $mysqli->prepare($query);

    if ($stmt === false) {
        die('Erreur de préparation de la requête : ' . $mysqli->error);
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    // Récupération des résultats
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
}

// Génération du token CSRF
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'utilisateur</title>

    <!-- Google Fonts for Gloock -->
    <link href="https://fonts.googleapis.com/css2?family=Gloock&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    <!-- Custom CSS for Colors and Font -->
    <style>
        body {
            background-color: #FDECD4;
            font-family: 'Gloock', serif;
        }

        h1 {
            color: #4D1E10;
        }

        label {
            color: #4D1E10;
        }

        .form-control {
            background-color: #B8A597;
            border-color: #4D1E10;
            color: #4D1E10;
        }

        .form-control:focus {
            border-color: #89220B;
            box-shadow: none;
        }

        .btn-primary {
            background-color: #4D1E10;
            border-color: #4D1E10;
        }

        .btn-primary:hover {
            background-color: #89220B;
            border-color: #89220B;
        }

        .btn-secondary {
            background-color: #806350;
            border-color: #806350;
        }

        .btn-secondary:hover {
            background-color: #4D1E10;
            border-color: #4D1E10;
        }

        .container {
            background-color: #B8A597;
            padding: 20px;
            border-radius: 8px;
            margin-top: 50px;
        }

        a {
            color: #4D1E10;
            text-decoration: none;
        }

        a:hover {
            color: #89220B;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="mt-5">Modifier l'utilisateur</h1>
    <form method="post" action="">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id_utilisateurs']); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <!-- Champ pour le nom d'utilisateur -->
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['identifiant']); ?>" required>
        </div>

        <!-- Liste déroulante pour sélectionner le rôle -->
        <div class="form-group">
            <label for="role">Rôle</label>
            <select class="form-control" id="role" name="role" required>
                <option value="Admin" <?php echo $user['role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="ProductOwner" <?php echo $user['role'] == 'ProductOwner' ? 'selected' : ''; ?>>ProductOwner</option>
                <option value="Client" <?php echo $user['role'] == 'Client' ? 'selected' : ''; ?>>Client</option>
            </select>
        </div>

        <!-- Boutons de soumission et de retour -->
        <button type="submit" name="update" class="btn btn-primary">Mettre à jour</button>
        <a href="admin.php?user_id=<?php echo $user['id_utilisateurs']; ?>" class="btn btn-secondary">Revenir en arrière</a>
    </form>
</div>

<!-- JS libraries -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>


