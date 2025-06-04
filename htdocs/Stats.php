<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (PDOException $e) {echo 'Erreur : ' . $e->getMessage();}
$id_actif = isset($_SESSION['id_actif']) ? $_SESSION['id_actif'] : null;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'none';">
    <title>Statistiques</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Inclure Chart.js -->
    <link rel="stylesheet" href="STYLE/IndexStyle.css">
    <link rel="stylesheet" href="STYLE/FooterStyle.css">
    <link rel="stylesheet" href="STYLE/HeaderStyle.css">

</head>

<body>
<?php include 'Includes/PHP/header.php'; ?> <!-- Inclusion du header -->

<div class="stats-container">
    <h1>Statistiques de la plateforme</h1>

    <!-- Section global stats -->
    <h2>Statistiques Globales</h2>

    <ul>
        <?php
        // Requêtes SQL pour statistiques globales
        // Connexions totales
        $total_connections = $db->query("SELECT COUNT(*) AS total FROM log")->fetch(PDO::FETCH_ASSOC)['total'];

        // Pourcentages connexions avec outils
        $stats_tools = $db->query("
                SELECT 
                    SUM(box) AS total_box, 
                    SUM(glorix) AS total_glorix, 
                    SUM(epix) AS total_epix, 
                    COUNT(*) AS total
                FROM log
            ")->fetch(PDO::FETCH_ASSOC);

        $percentage_box = $stats_tools['total'] > 0 ? ($stats_tools['total_box'] / $stats_tools['total']) * 100 : 0;
        $percentage_glorix = $stats_tools['total'] > 0 ? ($stats_tools['total_glorix'] / $stats_tools['total']) * 100 : 0;
        $percentage_epix = $stats_tools['total'] > 0 ? ($stats_tools['total_epix'] / $stats_tools['total']) * 100 : 0;

        // Profils actifs le mois dernier
        $last_month_active = $db->query("SELECT COUNT(DISTINCT user_id) AS active_last_month FROM log WHERE date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)")->fetch(PDO::FETCH_ASSOC)['active_last_month'];

        // Profils totaux
        $total_profiles = $db->query("SELECT COUNT(*) AS total FROM users")->fetch(PDO::FETCH_ASSOC)['total'];

        // Nombre de projets "epix"
        $total_projects = $db->query("SELECT COUNT(*) AS total FROM projects")->fetch(PDO::FETCH_ASSOC)['total'];

        // Sources enregistrées (list_id != 0)
        $total_sources = $db->query("SELECT COUNT(*) AS total FROM sources")->fetch(PDO::FETCH_ASSOC)['total'];
        $total_links = $db->query("SELECT COUNT(*) AS total FROM links")->fetch(PDO::FETCH_ASSOC)['total'];

        // Outils enregistrés
        $total_tools = $db->query("SELECT COUNT(*) AS total FROM tools")->fetch(PDO::FETCH_ASSOC)['total'];

        // Affichage Global
        echo "<li>Nombre de connexions totales : $total_connections</li>";
        echo "<li>Taux d'emploi de Box : " . round($percentage_box, 2) . "%</li>";
        echo "<li>Taux d'emploi de Glorix : " . round($percentage_glorix, 2) . "%</li>";
        echo "<li>Taux d'emploi d' Epix : " . round($percentage_epix, 2) . "%</li>";
        echo "<li>Profils actifs le mois dernier : $last_month_active</li>";
        echo "<li>Profils totaux : $total_profiles</li>";
        echo "<li>Projets Epix créés : $total_projects</li>";
        echo "<li>Sources catégorisées : $total_sources</li>";
        echo "<li>Liens enregistrés : $total_sources</li>";
        echo "<li>Outils enregistrés : $total_tools</li>";
        ?>
    </ul>

    <?php if ($id_actif): ?>
        <!-- Section stats utilisateur connecté -->
        <h2>Statistiques de mon profil</h2>

        <ul>
            <?php
            // Requêtes SQL pour stats utilisateur connecté
            $user_connections = $db->prepare("SELECT COUNT(*) AS total FROM log WHERE user_id = ?");
            $user_connections->execute([$id_actif]);
            $user_connections = $user_connections->fetch(PDO::FETCH_ASSOC)['total'];

            $user_tools = $db->prepare("
                SELECT 
                    SUM(box) AS total_box, 
                    SUM(glorix) AS total_glorix, 
                    SUM(epix) AS total_epix, 
                    COUNT(*) AS total
                FROM log
                WHERE user_id = ?
            ");
            $user_tools->execute([$id_actif]);
            $user_stats = $user_tools->fetch(PDO::FETCH_ASSOC);

            $user_percentage_box = $user_stats['total'] > 0 ? ($user_stats['total_box'] / $user_stats['total']) * 100 : 0;
            $user_percentage_glorix = $user_stats['total'] > 0 ? ($user_stats['total_glorix'] / $user_stats['total']) * 100 : 0;
            $user_percentage_epix = $user_stats['total'] > 0 ? ($user_stats['total_epix'] / $user_stats['total']) * 100 : 0;

            echo "<li>Nombre de vos connexions : $user_connections</li>";
            echo "<li>Vos connexions utilisant Box : " . round($user_percentage_box, 2) . "%</li>";
            echo "<li>Vos connexions utilisant Glorix : " . round($user_percentage_glorix, 2) . "%</li>";
            echo "<li>Vos connexions utilisant Epix : " . round($user_percentage_epix, 2) . "%</li>";
            ?>
        </ul>
    <?php endif; ?>

    <!-- Diagramme avec Chart.js -->
    <canvas id="connectionsChart"></canvas>

</div>

<?php include 'Includes/PHP/footer.php'; ?> <!-- Inclusion du footer -->

<script>
    // Exemple d'extraction des données pour le graphique
    const ctx = document.getElementById('connectionsChart').getContext('2d');

    // Ces données doivent être générées dynamiquement côté PHP
    const monthlyData = {
        labels: <?= json_encode($db->query("SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') AS month FROM log ORDER BY month")->fetchAll(PDO::FETCH_COLUMN)); ?>,
        data: <?= json_encode($db->query("SELECT COUNT(*) AS total FROM log GROUP BY DATE_FORMAT(date, '%Y-%m')")->fetchAll(PDO::FETCH_COLUMN)); ?>
    };

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthlyData.labels,
            datasets: [{
                label: 'Connexions par mois',
                data: monthlyData.data,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Mois'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Nombre de connexions'
                    },
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>

</html>