<?php
$servername = "192.168.1.15";
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

// 1. Top 10 applications par grand client
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

echo "<h1>Top 10 applications par grand client</h1>";
echo "<table border='1'>";
echo "<tr><th>Grand client</th><th>Application</th><th>Total</th></tr>";
foreach ($topApps as $row) {
    echo "<tr><td>{$row['NomGrandClient']}</td><td>{$row['nomAppli']}</td><td>{$row['total']}</td></tr>";
}

// 2. Top 10 applications par famille