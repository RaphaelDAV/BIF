<?php
include("../src/pdo.php");
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['id_utilisateurs']) || $_SESSION['role'] != "Admin") {
    header('Location: ../src/login.php?non');
    exit();
}

// Gestion de la suppression d'utilisateur
if (isset($_POST['delete']) && isset($_POST['user_id'])) {
    // Protection CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }

    $user_id = $_POST['user_id'];

    // Requête préparée pour supprimer un utilisateur
    $query = "DELETE FROM BIF_utilisateurs WHERE id_utilisateurs = ?";
    $stmt = $mysqli->prepare($query);

    if ($stmt === false) {
        die('Erreur de préparation de la requête : ' . $mysqli->error);
    }

    // Liaison des paramètres, ici 'i' est pour un entier (id_utilisateurs)
    $stmt->bind_param('i', $user_id);

    // Exécution de la requête
    if ($stmt->execute()) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Erreur lors de la suppression de l'utilisateur : " . $stmt->error;
    }

    $stmt->close();
}

// Gestion de la création d'un nouveau gestionnaire
if (isset($_POST['create'])) {
    header('Location: create.php');
    exit();
}

// Génération d'un token CSRF
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Sélection des utilisateurs qui ne sont pas des administrateurs
$query = "SELECT id_utilisateurs, identifiant, role FROM BIF_utilisateurs WHERE role != 'Admin'";
$stmt = $mysqli->prepare($query);

if ($stmt === false) {
    die('Erreur de préparation de la requête : ' . $mysqli->error);
}

$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($user = $result->fetch_assoc()) {
    $users[] = $user;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Administrateur</title>
    <link rel="stylesheet" href="../css/admin.css">
    <!-- Google Fonts for Gloock -->
    <link href="https://fonts.googleapis.com/css2?family=Gloock&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    <!-- Custom CSS for Colors and Font -->
    <style>
        body {
            background-color: #7c624a;
            font-family: 'Gloock', serif;
        }

        h2 {
            color: #4D1E10;
        }

        .table {
            background-color: #B8A597;
            color: #4D1E10;
        }

        .table th, .table td {
            color: #4D1E10;
        }

        .table th {
            background-color: #89220B;
            color: #FDECD4;
        }

        .btn-danger {
            background-color: #89220B;
            border-color: #89220B;
        }

        .btn-danger:hover {
            background-color: #4D1E10;
            border-color: #4D1E10;
        }

        .btn-secondary {
            background-color: #806350;
            border-color: #806350;
        }

        .btn-secondary:hover {
            background-color: #4D1E10;
            border-color: #4D1E10;
        }

        table td:nth-child(1) {
            color: #4D1E10;
        }

        .btn-primary {
            background-color: #4D1E10;
            border-color: #4D1E10;
            margin-bottom: 100px;
        }

        table td:nth-child(1) {
            background-color: #4D1E10;
            color: #4D1E10;
        }


        .btn-primary:hover {
            background-color: #89220B;
            border-color: #89220B;
        }

        .container a {
            color: #4D1E10;
            text-decoration: none;
            font-size: 1rem;
        }

        .container a:hover {
            color: #89220B;
        }

        .container{
            height: 40vw;
        }
    </style>
</head>
<body>

<?php
include ("../src/header.inc.php");
?>

<div class="container">
    <h2 class="mt-5">Tableau de Bord - Administrateur</h2>
    <table class="table table-bordered mt-3">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nom d'utilisateur</th>
            <th>Rôle</th>
            <th>Modification</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id_utilisateurs']); ?></td>
                <td><?php echo htmlspecialchars($user['identifiant']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td>
                    <form method="post" action="">
                        <input type="hidden" name="user_id" value="<?php echo $user['id_utilisateurs']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Vous êtes sûr de vouloir supprimer cet utilisateur ?')">Supprimer</button>
                        <a href="edit_users.php?user_id=<?php echo $user['id_utilisateurs']; ?>" class="btn btn-secondary">Modifier</a>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="create.php?user_id=<?php echo $user['id_utilisateurs']; ?>"><button type="submit" name="create" class="btn btn-primary">Créer un nouveau compte</button></a>
</div>

<?php
include ("../src/footer.inc.php");
?>
</body>
</html>
