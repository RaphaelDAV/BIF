<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <link rel="stylesheet" href="../css/stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Unicase:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Unicase:wght@300;400;500;600;700&family=Gloock&display=swap');
    </style>
</head>
<body>
<?php
    include ("header.inc.php");
?>
<div class="service">
    <p>Gestion de vos comptes en banques client et professionels  - Service disponible  24/24h   &   7/7j</p>
</div>
<section id="intro">
    <div class="text">
        <h1>Une question ? Nous sommes à votre <br> disposition</h1>
        <hr class="slash">
        <br>
        <p>Notre service client est disponible 7/7j afin d'avoir des réponses à toutes vos <br> questions.
    </div>
</section>

<div class="container-contact">
    <div class="contact">
        <h1><u>Formulaire de contact</u></h1>
        <form method="POST" action="https://formspree.io/f/xwpkpagb" class="message">
            <input name="name" id="identifiant" style="text-indent:17px;" class="icon-id" type="text" placeholder="Nom & Prénom">
            <input name="email" id="email" style="text-indent:17px;" class="icon-mail" type="email" placeholder="Email">
            <textarea name="message" style="text-indent:17px;" id="message" class="textarea-message" type="text" placeholder="Message"></textarea>
            <div class="soumettre-contact">
                <button name="submit" type="submit">Soumettre</button>
                <p>Déjà <b>inscrit</b> ? Accéder à votre espace en vous <a href="../index.php">connectant</a>.</p>
            </div>
        </form>
    </div>
</div>
<?php
include ("footer.inc.php");
?>
</body>
</html>