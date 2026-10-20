<?php
require_once 'managers/config.php';

$current_page = 'accueil';
$current_section = isset($_GET['section']) ? $_GET['section'] : 'home';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= BASE_PATH ?>">
    <title>OptiGF.eu - Mentions légales</title>
    <script src="assets/sections.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="assets/styles.css" rel="stylesheet">
    <link href="assets/mobile.css" rel="stylesheet">
</head>

<body>
    <?php include '_header.php'; ?>

    <div class="main-container bg-dark text-white pb-5">
        <!-- Menu gauche fixe -->
        <div class="sidebar-left col-md-2">
            <?php include '_sections.php'; ?>
        </div>

        <!-- Contenu principal -->
        <br><br><br>
        <div class="main-content col-md-7">
            <div class="mt-2">

                <!-- Section d'accueil -->
                <div id="home-section" class="content-section">
                    <h2>Mentions légales</h2>
                    <p class="lead mt-4">Nous vous recommandons fortement de lire toutes les mentions sans omission.</p>

                    <p>Découvrez ici les <a href="legal.php#cgu">Conditions Générales d'Utilisation</a> et notre <a
                            href="legal.php#privacy">Politique de Confidentialité</a>.</p>

                    <p class="mt-4 text-justify">Pour toute question relative à celles-ci, veuillez nous adresser un
                        e-mail <strong>en français</strong> à l'adresse <a target="_blank" rel="noopener noreferrer"
                            href="mailto:bulle.optigf@gmail.com">bulle.optigf@gmail.com</a> en précisant le ou les articles
                        concernés.</p>
                    <p class="mt-4 text-justify">Pour toute résiliation volontaire, conformément à l'article 3.3 de nos
                        <a href="legal.php#cgu">CGU</a> et dans le cas spécifique où l'accès à votre compte personnel
                        OptiGF est
                        impossible, veuillez nous adresser votre requête par MP ou sur le serveur <a target="_blank"
                            rel="noopener noreferrer" href="https://discord.gg/58j4JnYFdz">Discord</a>, en précisant la
                        date exacte
                        d'inscription sans quoi votre demande ne pourra être prise en compte.
                    </p>
                </div>

                <!-- Section CGU -->
                <div id="cgu-section" class="content-section">
                    <h2>Conditions Générales d'Utilisation</h2>
                    <p class="lead">Veuillez prendre en considération les CGU suivantes :</p>

                    <h3 class="mt-4">1. Description du service</h3>
                    <p>OptiGF est un service principalement à destination des joueurs de Grand Fantasia qui cherchent à
                        optimiser leur expérience sur le jeu. Le site vous permet de vous documenter sur certains
                        aspects techniques du jeu ou télécharger des ressources, dans le but de bénéficier de meilleures
                        performances in-game.</p>

                    <h3 class="mt-4">2.1. Acceptation des conditions</h3>
                    <p>En accédant et en utilisant les services OptiGF, vous acceptez d'être lié par ces conditions
                        générales d'utilisation. Si vous n'acceptez pas ces conditions, vous ne devez pas utiliser le
                        service ou télécharger quelconque ressource logicielle.</p>

                    <h3 class="mt-4">2.2. Mise à jour des conditions</h3>
                    <p>Nous nous réservons le droit de mettre à jour ces conditions générales d'utilisation à tout
                        moment. La date de dernière modification sera indiquée systématiquement. L'utilisation continue
                        de nos services après modification constitue votre acceptation des nouvelles conditions.</p>

                    <h3 class="mt-4">3.1. Compte utilisateur : Création</h3>
                    <p class="mb-0">Un compte OptiGF vous permet entre autres de télécharger notre "Injection OptiGF".
                        Pour créer votre compte :</p>
                    <ul>
                        <li>Votre identifiant OptiGF doit être votre nom d'utilisateur Discord</li>
                        <li>Votre mot de passe doit respecter des critères de sécurité minimum</li>
                        <li>Vous devez nous notifier de toute usurpation de compte ou modification de votre nom
                            d'utilisateur Discord (qui ne correspondrait donc plus à votre identifiant OptiGF)</li>
                        <li>Pour créer votre compte, vous devez vous rendre sur la page <a
                                href="index.php#account">Compte</a>
                            et dans le formulaire d'inscription saisir votre "Clé de Carso", un identifiant et un mot de
                            passe</li>
                        <li>Pour obtenir votre "Clé de Carso", vous devez rejoindre notre <a target="_blank"
                                rel="noopener noreferrer" href="https://discord.gg/58j4JnYFdz">Serveur Discord</a> et
                            faire une demande directement depuis celui-ci</li>
                    </ul>

                    <h3 class="mt-4">3.2. Compte utilisateur : Sécurité</h3>
                    <p class="mb-0">Vous devez fournir et maintenir des informations exactes et à jour, et êtes
                        responsable de la confidentialité de ces informations</p>
                    <ul>
                        <li>Votre compte OptiGF est lié à votre compte Discord (indirectement via le même "username")
                        </li>
                        <li>Une "Clé de Carso" est strictement personnelle et est renouvelable par message privé Discord
                            via notre bot ; elle vous sera utile pour réaliser une action forte (résiliation,
                            modification identifiant ou mot de passe)</li>
                        <li>Vous ne devez pas partager vos identifiants, l'accès à votre compte ou rendre publique votre
                            clé</li>
                        <li>Nous nous réservons le droit de suspendre ou résilier les comptes que nous jugeons compromis
                        </li>
                    </ul>

                    <h3 class="mt-4">3.3. Compte utilisateur : Résiliation volontaire</h3>
                    <ul>
                        <li>Pour toute résiliation volontaire, veuillez accéder à votre compte personnel puis effectuer
                            une demande de résiliation directement depuis votre espace</li>
                        <li>En cas de perte totale d'accès, veuillez consulter les <a href="legal.php#">mentions
                                légales</a> et
                            nous contacter à l'adresse indiquée</li>
                    </ul>

                    <h3 class="mt-4">3.4. Compte utilisateur : Résiliation par le service</h3>
                    <p class="mb-0">Nous pouvons résilier ou suspendre votre accès au service :</p>
                    <ul>
                        <li>En cas de violation de ces conditions</li>
                        <li>Si votre identifiant OptiGF ne correspond à aucun compte Discord du serveur OptiGF après
                            plusieurs jours de vérification (incluant la modification du nom d'utilisateur Discord ou le
                            fait de quitter le serveur)</li>
                        <li>Pour protéger la sécurité du service</li>
                        <li>Pour respecter nos obligations légales</li>
                        <li>Si vous utilisez des comptes Discord trop récents en boucle après bannissement</li>
                    </ul>

                    <h3 class="mt-4">4. Utilisation acceptable</h3>
                    <p class="mb-0">Vous acceptez de ne pas :</p>
                    <ul>
                        <li>Tenter de contourner nos mesures de sécurité</li>
                        <li>Utiliser le service d'une manière qui pourrait l'endommager ou nuire aux autres utilisateurs
                        </li>
                        <li>Partager ou vendre votre accès au service</li>
                        <li>Utiliser le service à des fins illégales</li>
                    </ul>

                    <h3 class="mt-4">5. Propriété intellectuelle</h3>
                    <ul>
                        <li>Toutes les ressources liées directement au service OptiGF appartiennent à cette même entité.
                        </li>
                        <li>Les ressources de notre injection Grand Fantasia appartiennent aux détenteurs réels, à
                            savoir <strong>&copy; X-Legend</strong>.</li>
                        <li>Celles modifiées par nos soins sont issues de ressources appartenant aux détenteurs
                            initiaux.</li>
                        <li>Nous ne sommes pas responsables des détournements pouvant être faits à partir de ces
                            ressources logicielles.</li>
                    </ul>

                    <h3 class="mt-4">6. Limitation de responsabilité</h3>
                    <p class="mb-0">Dans les limites permises par la loi, nous ne serons pas responsables des :</p>
                    <ul>
                        <li>Dommages indirects</li>
                        <li>Pertes de données</li>
                        <li>Interruptions de service</li>
                    </ul>

                    <h3 class="mt-4">7. Modifications du service</h3>
                    <p class="mb-0">Nous nous réservons le droit de :</p>
                    <ul>
                        <li>Modifier ou interrompre le service</li>
                        <li>Changer les fonctionnalités</li>
                    </ul>

                    <h3 class="mt-4">8. Droit applicable</h3>
                    <p>Ces conditions sont régies par le droit français en vigueur.</p>

                    <h3 class="mt-4">9. Contact</h3>
                    <p>Pour toute question concernant ces conditions générales d'utilisation, veuillez vous référer aux
                        <a href="legal.php#">mentions légales</a> qui détaillent les modalités de prise de contact.
                    </p>
                </div>

                <!-- Section Privacy -->
                <div id="privacy-section" class="content-section">
                    <h2>Politique de Confidentialité</h2>
                    <p class="lead">Veuillez prendre connaissance de notre politique de confidentialité avant tout
                        utilisation de nos services.</p>

                    <h3 class="mt-4">1. Introduction</h3>
                    <p>Cette politique de confidentialité explique comment OptiGF collecte, utilise et protège vos
                        données personnelles lorsque vous utilisez notre service. Nous nous engageons à assurer la
                        protection de votre vie privée conformément au Règlement Général sur la Protection des Données
                        (RGPD) et aux lois applicables en matière de protection des données.</p>

                    <h3 class="mt-4">2.1. Données collectées fournies par l'utilisateur</h3>
                    <ul>
                        <li>Identifiant Discord (celui indiqué en formulaire d'inscription)</li>
                        <li>Mot de passe (stocké de manière sécurisée et chiffrée)</li>
                    </ul>

                    <h3 class="mt-4">2.2. Données collectées automatiquement</h3>
                    <ul>
                        <li>Adresse IP</li>
                        <li>Agent utilisateur du navigateur</li>
                        <li>Horodatages des connexions et des actions</li>
                        <li>Journaux d'activité</li>
                    </ul>

                    <h3 class="mt-4">3. Utilisation des données</h3>
                    <p class="mb-0">Vos données personnelles sont utilisées aux seules fins suivantes :</p>
                    <ul>
                        <li>Authentification et gestion de votre compte</li>
                        <li>Sécurisation de votre compte et prévention des fraudes</li>
                        <li>Communications relatives aux services (bot discord)</li>
                        <li>Amélioration de la sécurité du service</li>
                        <li>Détections d'utilisation malveillante ou qui nuisent à nos services</li>
                    </ul>

                    <h3 class="mt-4">4. Base légale du traitement</h3>
                    <p class="mb-0">Nous traitons vos données personnelles sur les bases légales suivantes :</p>
                    <ul>
                        <li>Exécution du contrat de service</li>
                        <li>Respect de nos obligations légales</li>
                        <li>Nos intérêts légitimes (sécurité, prévention des fraudes)</li>
                        <li>Votre consentement, lorsque applicable</li>
                    </ul>

                    <h3 class="mt-4">5. Conservation des données</h3>
                    <p class="mb-0">Nous conservons vos données personnelles aussi longtemps que nécessaire pour :</p>
                    <ul>
                        <li>Fournir nos services</li>
                        <li>Respecter nos obligations légales</li>
                        <li>Résoudre les litiges</li>
                        <li>Faire respecter nos accords</li>
                    </ul>

                    <h3 class="mt-4">6. Sécurité des données</h3>
                    <p class="mb-0">Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles
                        appropriées, notamment :</p>
                    <ul>
                        <li>Chiffrement des mots de passe avec des algorithmes sécurisés (Argon2id)</li>
                        <li>Protection contre les attaques par force brute</li>
                        <li>Limitation du taux de tentatives de connexion</li>
                        <li>Utilisation de tokens CSRF</li>
                        <li>En-têtes de sécurité HTTP</li>
                        <li>Sessions sécurisées</li>
                        <li>Validation et assainissement des entrées utilisateur</li>
                    </ul>

                    <h3 class="mt-4">7. Vos droits</h3>
                    <p class="mb-0">Conformément au RGPD, vous disposez des droits suivants :</p>
                    <ul>
                        <li>Droit d'accès à vos données personnelles</li>
                        <li>Droit de rectification des données inexactes</li>
                        <li>Droit à l'effacement ("droit à l'oubli")</li>
                        <li>Droit à la limitation du traitement</li>
                        <li>Droit à la portabilité des données</li>
                        <li>Droit d'opposition au traitement</li>
                        <li>Droit de retirer votre consentement</li>
                    </ul>
                    <p>Pour exercer ces droits, contactez-nous à l'adresse indiquée ci-dessous.</p>

                    <h3 class="mt-4">8. Partage des données</h3>
                    <p class="mb-0">Nous ne vendons pas vos données personnelles. Nous ne partageons pas vos
                        informations avec des tiers, sauf :</p>
                    <ul>
                        <li>Lorsque requis par la loi</li>
                        <li>Pour protéger nos droits légaux</li>
                        <li>En cas d'urgence impliquant la sécurité publique</li>
                    </ul>

                    <h3 class="mt-4">9. Modifications de la politique</h3>
                    <p class="mb-0">Nous nous réservons le droit de modifier cette politique de confidentialité à tout
                        moment.</p>
                    <ul>
                        <li>Les modifications entrent en vigueur dès leur publication.</li>
                        <li>Nous vous encourageons à consulter régulièrement cette page pour vous tenir informé de toute
                            mise à jour ou modification de notre politique de confidentialité.</li>
                    </ul>

                    <h3 class="mt-4">10. Cookies et technologies similaires</h3>
                    <p class="mb-0">Nous n'utilisons pas de cookies publicitaires ou de traçage. Notre service utilise
                        uniquement des cookies de session essentiels pour :</p>
                    <ul>
                        <li>Maintenir votre session sécurisée</li>
                        <li>Protéger contre les attaques CSRF</li>
                        <li>Gérer la limitation des taux de tentatives</li>
                    </ul>

                    <h3 class="mt-4">11. Contact</h3>
                    <p>Pour toute question concernant cette politique de confidentialité ou vos données personnelles,
                        veuillez vous référer aux <a href="legal.php#">mentions légales</a> qui détaillent les modalités
                        de prise
                        de contact.</p>

                    <h3 class="mt-4">12. Autorité de contrôle</h3>
                    <p>Vous avez le droit d'introduire une réclamation auprès de :</p>
                    <p>Commission Nationale de l'Informatique et des Libertés (CNIL)<br>
                        3 Place de Fontenoy<br>
                        75007 Paris CEDEX<br>
                        FRANCE</p>

                </div>

                <div class="text-end">
                    <p class="mt-5 pt-4 text-white opacity-25">
                        <em>En cours de développement...</em><br>
                        <em>Dernières modifications: Avril 2026</em>
                    </p>
                </div>

            </div>
        </div>

        <!-- Links droite fixe -->
        <div class="sidebar-right col-md-2">
            <?php include '_links.php'; ?>
        </div>

        <br>
    </div>

    <?php include '_footer.php'; ?>
</body>

</html>