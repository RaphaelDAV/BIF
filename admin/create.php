<?php
include('../src/pdo.php');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id_utilisateurs']) || $_SESSION['role'] != "Admin") {
    header('Location: ../src/login.php');
    exit();
}

if (isset($_POST['valid'])) {
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Check if password and confirm password match
    if ($password !== $confirm_password) {
        die('Les mots de passe ne correspondent pas.');
    }

    // Validate username
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        die('Nom d\'utilisateur invalide. Veuillez utiliser uniquement des lettres, des chiffres et des traits de soulignement.');
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the insert query
    $query = "INSERT INTO bif.bif_utilisateurs (identifiant, password, role) VALUES (?, ?, ?)";
    $req_insert = $mysqli->prepare($query);

    if (!$req_insert) {
        error_log('Erreur de préparation: ' . $mysqli->error);
        die('Une erreur est survenue. Veuillez réessayer.');
    }

    // Bind the parameters and execute
    $req_insert->bind_param('sss', $username, $hashed_password, $role);
    if ($req_insert->execute()) {
        $user_id = $mysqli->insert_id; // Get the last inserted ID
        header('Location: admin.php?user_id=' . $user_id);
        exit();
    } else {
        error_log('Erreur lors de l\'insertion: ' . $req_insert->error);
        die('Une erreur est survenue. Veuillez réessayer.');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de compte - Administrateur</title>

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

        ::placeholder {
            color: #806350;
            opacity: 1; /* Pour garantir que la couleur est appliquée */
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

        a {
            color: #4D1E10;
            text-decoration: none;
        }

        a:hover {
            color: #89220B;
        }

        .container {
            background-color: #B8A597;
            padding: 20px;
            border-radius: 8px;
        }
    </style>
</head>

<body>
<div class="container mt-5">
    <h1 class="mb-4">Créez un compte</h1>
    <form action="create.php" method="post">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Entrez le nom d'utilisateur" required>
        </div>
        <div class="form-group">
            <label for="role">Rôle</label>
            <select class="form-control" id="role" name="role" required>
                <option value="Admin">Admin</option>
                <option value="ProductOwner">ProductOwner</option>
                <option value="Client">Client</option>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Entrez le mot de passe" required>
        </div>
        <div class="form-group">
            <label for="confirm-password">Confirmez le mot de passe</label>
            <input type="password" class="form-control" id="confirm-password" name="confirm_password" placeholder="Confirmez le mot de passe" required>
        </div>
        <button type="submit" name="valid" class="btn btn-primary">Créer le compte</button>
        <a href="admin.php" class="btn btn-secondary">Revenir en arrière</a>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>

