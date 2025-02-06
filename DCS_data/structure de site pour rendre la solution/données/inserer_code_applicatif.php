<?php
$servername = "jdbc:mysql://172.18.143.44:30120";
$username = "root";
$password = "rootpassword";
$dbname = "tableau_bord";

// Créer la connexion
$pdo = new PDO("mysql:host=".$servername.";dbname=".$dbname, $username, $password);


// Ouvrir le fichier CSV
$file = fopen("Code_applicatifs.csv", "r");
$colonnes=fgetcsv($file);//lecture entete
while (!feof($file)) {
    $data = fgetcsv($file,1000,';');

    $IRT= $data[0];
    $nomAppli = $data[1];
    

    echo ("######<br>recuperation ligne IRT = ".$IRT." nomAPPLI = ".$nomAppli."<br>");

    // Insérer dans la table application 
    
        $sql="INSERT INTO `application` (`IRT`, `nomAppli`) VALUES (:IRT, :nomappli);";
        $req=$pdo->prepare($sql);
        $req->bindValue(':IRT', $IRT, PDO::PARAM_STR);
        $req->bindValue(':nomappli', $nomAppli, PDO::PARAM_STR);
        $req->execute();
    //    echo ("insertion de ".$nomGrandClient."<br>");
    
}

fclose($file);
?>
