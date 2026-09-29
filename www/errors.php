<?php
$current_page = 'errors';
$current_section = isset($_GET['section']) ? $_GET['section'] : 'home';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OptiGF.eu - Erreur !</title>
    <script src="/assets/sections.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="/assets/styles.css" rel="stylesheet">
    <link href="/assets/mobile.css" rel="stylesheet">
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
                <!-- Section Home -->
                <div id="home-section" class="content-section">
                    <div class="terminal">
                        <h2>Il semblerait que vous vous soyez perdu...</h2>
                        <p class="lead"><span>Liste des erreurs possibles :</span>
                        <ul>
                            <li><a href="errors.php#400">400 - Bad Request</a></li>
                            <li><a href="errors.php#401">401 - Unauthorized</a></li>
                            <li><a href="errors.php#402">402 - Payment Required</a></li>
                            <li><a href="errors.php#403">403 - Forbidden</a></li>
                            <li><a href="errors.php#404">404 - Page not found</a></li>
                            <li><a href="errors.php#405">405 - Method Not Allowed</a></li>
                            <li><a href="errors.php#406">406 - Not Acceptable</a></li>
                            <li><a href="errors.php#408">408 - Request Timeout</a></li>
                            <li><a href="errors.php#409">409 - Conflict</a></li>
                            <li><a href="errors.php#410">410 - Gone</a></li>
                            <li><a href="errors.php#411">411 - Length Required</a></li>
                            <li><a href="errors.php#413">413 - Payload Too Large</a></li>
                            <li><a href="errors.php#414">414 - URI Too Long</a></li>
                            <li><a href="errors.php#415">415 - Unsupported Media Type</a></li>
                            <li><a href="errors.php#429">429 - Too Many Requests</a></li>
                            <li><a href="errors.php#500">500 - Internal Server Error</a></li>
                            <li><a href="errors.php#501">501 - Not Implemented</a></li>
                            <li><a href="errors.php#502">502 - Bad Gateway</a></li>
                            <li><a href="errors.php#503">503 - Service Unavailable</a></li>
                            <li><a href="errors.php#504">504 - Gateway Timeout</a></li>
                            <li><a href="errors.php#511">511 - Network Authentication Required</a></li>
                        </ul>
                        </p>
                        <a href="">Retour à l'accueil</a>
                    </div>
                </div>
                <!-- Section Error 400 -->
                <div id="400-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 400</h2>
                        <p>Bad Request<span class="blink">_</span></p>
                        <p class="error">La requête n'a pas pu être traitée !</p>
                        <p class="info">ERR_BAD_REQUEST | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 401 -->
                <div id="401-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 401</h2>
                        <p>Unauthorized<span class="blink">_</span></p>
                        <p class="error">Authentification requise pour accéder à cette page !</p>
                        <p class="info">ERR_UNAUTHORIZED | <a href="login">Se connecter</a></p>
                    </div>
                </div>
                <!-- Section Error 402 -->
                <div id="402-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 402</h2>
                        <p>Payment Required<span class="blink">_</span></p>
                        <p class="error">Paiement nécessaire pour accéder à cette ressource !</p>
                        <p class="info">ERR_PAYMENT_REQUIRED | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 403 -->
                <div id="403-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 403</h2>
                        <p>Forbidden<span class="blink">_</span></p>
                        <p class="error">Vous n'avez pas les droits pour accéder à cette page !</p>
                        <p class="info">ERR_FORBIDDEN | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 404 -->
                <div id="404-section" class="content-section">
                    <div class="terminal">
                        <h2>ERREUR 404</h2>
                        <p>Page not found<span class="blink">_</span></p>
                        <p class="error">La page que vous recherchez n'existe pas !</p>
                        <p class="info">ERR_PAGE_NOT_FOUND | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 405 -->
                <div id="405-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 405</h2>
                        <p>Method Not Allowed<span class="blink">_</span></p>
                        <p class="error">Méthode non autorisée !</p>
                        <p class="info">ERR_METHOD_NOT_ALLOWED | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 406 -->
                <div id="406-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 406</h2>
                        <p>Not Acceptable<span class="blink">_</span></p>
                        <p class="error">Le serveur ne peut pas fournir le contenu dans le format demandé !</p>
                        <p class="info">ERR_NOT_ACCEPTABLE | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 408 -->
                <div id="408-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 408</h2>
                        <p>Request Timeout<span class="blink">_</span></p>
                        <p class="error">La requête a pris trop de temps !</p>
                        <p class="info">ERR_REQUEST_TIMEOUT | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 409 -->
                <div id="409-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 409</h2>
                        <p>Conflict<span class="blink">_</span></p>
                        <p class="error">Conflit détecté dans la requête !</p>
                        <p class="info">ERR_CONFLICT | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 410 -->
                <div id="410-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 410</h2>
                        <p>Gone<span class="blink">_</span></p>
                        <p class="error">Cette ressource n'est plus disponible !</p>
                        <p class="info">ERR_GONE | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 411 -->
                <div id="411-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 411</h2>
                        <p>Length Required<span class="blink">_</span></p>
                        <p class="error">La longueur du contenu est requise !</p>
                        <p class="info">ERR_LENGTH_REQUIRED | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 413 -->
                <div id="413-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 413</h2>
                        <p>Payload Too Large<span class="blink">_</span></p>
                        <p class="error">Le contenu envoyé est trop volumineux !</p>
                        <p class="info">ERR_PAYLOAD_TOO_LARGE | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 414 -->
                <div id="414-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 414</h2>
                        <p>URI Too Long<span class="blink">_</span></p>
                        <p class="error">L'URL de la requête est trop longue !</p>
                        <p class="info">ERR_URI_TOO_LONG | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 415 -->
                <div id="415-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 415</h2>
                        <p>Unsupported Media Type<span class="blink">_</span></p>
                        <p class="error">Type de média non supporté !</p>
                        <p class="info">ERR_UNSUPPORTED_MEDIA_TYPE | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 429 -->
                <div id="429-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 429</h2>
                        <p>Too Many Requests<span class="blink">_</span></p>
                        <p class="error">Trop de requêtes envoyées !</p>
                        <p class="info">ERR_TOO_MANY_REQUESTS | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 500 -->
                <div id="500-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 500</h2>
                        <p>Internal Server Error<span class="blink">_</span></p>
                        <p class="error">Erreur interne du serveur !</p>
                        <p class="info">ERR_INTERNAL_SERVER_ERROR | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 501 -->
                <div id="501-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 501</h2>
                        <p>Not Implemented<span class="blink">_</span></p>
                        <p class="error">Fonctionnalité non implémentée !</p>
                        <p class="info">ERR_NOT_IMPLEMENTED | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 502 -->
                <div id="502-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 502</h2>
                        <p>Bad Gateway<span class="blink">_</span></p>
                        <p class="error">Passerelle défaillante !</p>
                        <p class="info">ERR_BAD_GATEWAY | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 503 -->
                <div id="503-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 503</h2>
                        <p>Service Unavailable<span class="blink">_</span></p>
                        <p class="error">Service temporairement indisponible !</p>
                        <p class="info">ERR_SERVICE_UNAVAILABLE | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 504 -->
                <div id="504-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 504</h2>
                        <p>Gateway Timeout<span class="blink">_</span></p>
                        <p class="error">Délai d'attente de la passerelle dépassé !</p>
                        <p class="info">ERR_GATEWAY_TIMEOUT | <a href="">Retour à l'accueil</a></p>
                    </div>
                </div>
                <!-- Section Error 511 -->
                <div id="511-section" class="content-section" style="display: none;">
                    <div class="terminal">
                        <h2>ERREUR 511</h2>
                        <p>Network Authentication Required<span class="blink">_</span></p>
                        <p class="error">Authentification réseau requise !</p>
                        <p class="info">ERR_NETWORK_AUTH_REQUIRED | <a href="">Retour à l'accueil</a></p>
                    </div>
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