<?php
$servername = "10.200.124.51";
$port = 30120;
$username = "root";
$password = "rootpassword";
$dbname = "tableau_bord";

try {
    $dsn = "mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// 1️⃣ Top 10 applications par grand client
$query = "
    SELECT gc.NomGrandClient, a.nomAppli, SUM(lf.prix) AS total
    FROM ligne_facturation lf
    JOIN application a ON lf.IRT = a.IRT
    JOIN clients c ON lf.CentreActiviteID = c.CentreActiviteID
    JOIN grandclients gc ON c.GrandClientID = gc.GrandClientID
    GROUP BY gc.NomGrandClient, a.nomAppli
    ORDER BY total DESC
    LIMIT 10;
";
$topApps = $pdo->query($query)->fetchAll();

// 2️⃣ Evolution des montants pour les 5 plus grands clients
$query = "
    SELECT gc.NomGrandClient, DATE_FORMAT(lf.mois, '%Y-%m') AS mois, SUM(lf.prix) AS total
    FROM ligne_facturation lf
    JOIN clients c ON lf.CentreActiviteID = c.CentreActiviteID
    JOIN grandclients gc ON c.GrandClientID = gc.GrandClientID
    WHERE lf.mois BETWEEN '2021-01-01' AND '2022-04-30'
    AND gc.GrandClientID IN (
        SELECT GrandClientID FROM (
            SELECT gc.GrandClientID, SUM(lf.prix) AS total
            FROM ligne_facturation lf
            JOIN clients c ON lf.CentreActiviteID = c.CentreActiviteID
            JOIN grandclients gc ON c.GrandClientID = gc.GrandClientID
            WHERE lf.mois BETWEEN '2021-01-01' AND '2022-04-30'
            GROUP BY gc.GrandClientID
            ORDER BY total DESC
            LIMIT 5
        ) AS top_clients
    )
    GROUP BY gc.NomGrandClient, mois
    ORDER BY mois
    
";

$topClients = $pdo->query($query)->fetchAll();


// 3. Evolution des volumes des produits 1_1 et 1_4
$query = "
    SELECT p.NOM_PRODUIT, DATE_FORMAT(lf.mois, '%Y-%m') AS mois, SUM(lf.volume) AS total
    FROM ligne_facturation lf
    JOIN produit p ON lf.produitID = p.produitID
    WHERE lf.mois BETWEEN '2021-01-01' AND '2022-04-30'
    AND (p.NOM_PRODUIT = '1_1' OR p.NOM_PRODUIT = '1_4')
    GROUP BY p.NOM_PRODUIT, mois
    ORDER BY mois;
";
$produitEvolution = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2>Top 10 Applications par Grand Client</h2>
    <table border="1">
        <tr><th>Grand Client</th><th>Application</th><th>Total (€)</th></tr>
        <?php foreach ($topApps as $row) { ?>
            <tr><td><?= $row['NomGrandClient'] ?></td><td><?= $row['nomAppli'] ?></td><td><?= number_format($row['total'], 2, ',', ' ') ?></td></tr>
        <?php } ?>
    </table>

    <h2>Évolution des Montants pour les 5 Plus Grands Clients</h2>
    <canvas id="montantGraph"></canvas>
    <script>
    const montantData = <?= json_encode($topClients, JSON_NUMERIC_CHECK) ?>;
    
    // Vérification console
    console.log("Données JSON :", montantData);

    const ctx1 = document.getElementById('montantGraph').getContext('2d');

    // Extraction des labels (mois) et datasets (clients)
    let labels = [...new Set(montantData.map(row => row.mois))]; // Liste des mois uniques
    let datasets = {};

    montantData.forEach(row => {
        if (!datasets[row.NomGrandClient]) {
            datasets[row.NomGrandClient] = {
                label: row.NomGrandClient,
                data: Array(labels.length).fill(null), // Initialisation avec des valeurs nulles
                borderColor: getRandomColor(),
                fill: false
            };
        }
        let index = labels.indexOf(row.mois);
        if (index !== -1) {
            datasets[row.NomGrandClient].data[index] = row.total;
        }
    });

    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: labels,
            datasets: Object.values(datasets)
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });

    function getRandomColor() {
        return `rgb(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)})`;
    }
</script>


    <h2>Évolution des Volumes pour Produits 1_1 et 1_4</h2>
    <canvas id="produitGraph"></canvas>
    <script>
        const produitData = <?= json_encode($produitEvolution, JSON_NUMERIC_CHECK) ?>;
        console.log("Données JSON pour les produits :", produitData);

        const ctx2 = document.getElementById('produitGraph').getContext('2d');

        let labels2 = [...new Set(produitData.map(row => row.mois))];
        let datasets2 = {};

        produitData.forEach(row => {
            if (!datasets2[row.NOM_PRODUIT]) {
                datasets2[row.NOM_PRODUIT] = {
                    label: row.NOM_PRODUIT,
                    data: Array(labels2.length).fill(null),
                    borderColor: getRandomColor(),
                    fill: false
                };
            }
            let index = labels2.indexOf(row.mois);
            if (index !== -1) {
                datasets2[row.NOM_PRODUIT].data[index] = row.total;
            }
        });

        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: labels2,
                datasets: Object.values(datasets2)
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    </script>

    <script>
        function getRandomColor() {
            return `rgb(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)})`;
        }
    </script>
    

</body>
</html>
