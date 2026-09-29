<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid gap-5">
        <a style="text-decoration: none !important;" class="navbar-brand d-flex align-items-center gap-2"
            href="/">
            <img class="rounded" src="assets/img/logo.png" height="72px" alt="Logo Accueil" />
            <h1 class="title mb-0">OPTIGF</h1>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link p-2 <?php echo (basename($_SERVER['PHP_SELF']) == '/') ? 'active' : ''; ?>"
                        title="Compte - FAQ - À propos" href="/"><strong>Accueil</strong></a>
                </li>

                <li class="nav-item">
                    <a class="nav-link p-2 <?php echo (basename($_SERVER['PHP_SELF']) == 'injection.php') ? 'active' : ''; ?>"
                        title="Présentation - Aperçus - Téléchargement"
                        href="injection.php"><strong>Injection</strong></a>
                </li>

                <li class="nav-item" title="Cette page n'est pas pas encore disponible !">
                    <a class="nav-link p-2 <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>"
                        href="settings.php"><strong>Paramètres</strong></a>
                </li>

                <li class="disabled nav-item" title="Cette page n'est pas pas encore disponible !">
                    <a class="disabled nav-link p-2 <?php echo (basename($_SERVER['PHP_SELF']) == 'ui.php') ? 'active' : ''; ?>"
                        href="ui.php"><strong>Créateur d'UIs</strong></a>
                </li>
            </ul>
        </div>
    </div>
</nav>