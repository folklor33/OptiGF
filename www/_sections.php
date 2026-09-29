<?php
// Charger les sections depuis le fichier JSON
$sectionsFile = 'assets/sections.json';
$sections = [];
if (file_exists($sectionsFile)) {
    $sectionsData = json_decode(file_get_contents($sectionsFile), true);
    if ($sectionsData) {
        $sections = $sectionsData;
    }
}

// Déterminer la page actuelle
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
if ($currentPage == 'index') {
    $currentPage = 'accueil';
}

// Obtenir les sections pour la page actuelle
$pageSections = isset($sections[$currentPage]) ? $sections[$currentPage] : [];
?>

<div class="mt-5">
    <h5 class="text-white-50 text-uppercase small fw-bold mb-3">Sections</h5>

    <?php if (!empty($pageSections)): ?>
        <nav class="nav flex-column">
            <?php
            // Récupérer le nom de la page actuelle avec extension .php
            $currentPageFile = basename($_SERVER['PHP_SELF']);
            foreach ($pageSections as $section):
            ?>
                <!-- MODIFIÉ : Inclure le nom de la page dans le lien hash -->
                <a href="<?php echo htmlspecialchars($currentPageFile); ?>#<?php echo htmlspecialchars($section['id']); ?>"
                    class="nav-link text-white section-link py-2 px-0 border-start border-3 border-transparent"
                    data-section="<?php echo htmlspecialchars($section['id']); ?>">
                    <span><?php echo htmlspecialchars($section['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php else: ?>
        <p class="text-white small">Aucune section disponible pour cette page.</p>
    <?php endif; ?>

    <hr class="border-secondary my-4">
</div>