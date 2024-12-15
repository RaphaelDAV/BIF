<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="../css/stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Unicase:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Unicase:wght@300;400;500;600;700&family=Gloock&display=swap');
    </style>
</head>
<body id="page-inscr">
<?php
    include ("header.inc.php");
?>
<div class="service">
    <p>Gestion de vos comptes en banques client et professionels  - Service disponible  24/24h   &   7/7j</p>
</div>
<div class="header-inscription">
    <h1>Banque Intercontinentale<br>Française</h1>
    <div class="container-inscription">
        <h2 id="button-inscrire">Formulaire d'inscription</h2>
        <div class="inscription">
            <input id="create-identifiant" style="text-indent:17px;" class="icon-id" type="text" placeholder="Identifiant">
            <input id="create-password" style="text-indent:17px;" class="icon-pass" type="password" placeholder="Mot de passe">
            <input id="raison" style="text-indent:17px;" class="icon-raison" type="text" placeholder="Raison Social">
            <input id="siren" style="text-indent:17px;" class="icon-siren" type="text" placeholder="N° Siren"> 
        </div>
        <button type="submit">S'inscrire</button>
        <p>Déjà <b>inscrit</b> ? Accéder à votre espace en vous <a href="../index.php">connectant</a>.</p>
    </div>
</div>

<section id="intro">
    <div class="text">
        <h1>Encore un <u>doute</u>?</h1>
        <hr class="slash">
        <br>
        <p>Notre service client est disponible 7/7j afin d'avoir des réponses à toutes vos <br> questions.
    </div>
    <button id="contact-inscr"><a href="contact.php">Contact</a></button>
</section>

<section id="engagement">
    <div class="img"></div>
    <div class="gauche">
        <h3> <u>Aucuns engagements</u></h3>
        <hr class="slash">
        <p>Nous faisons <b>confiance</b> à nos clients en leur proposans un service <b>fiable</b> sans imposer <b>aucuns engaments</b>. Nous mettons un point d’honneur au bon <b>rapport</b> entre  le fournisseuir et le client.</p>
    </div>
</section>
<?php
include ("footer.inc.php");
?>
</body>
</html>