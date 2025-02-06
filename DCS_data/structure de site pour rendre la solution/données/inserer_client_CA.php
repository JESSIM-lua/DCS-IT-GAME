<?php
$servername = "jdbc:mysql://172.18.143.44:30120";
$username = "root";
$password = "rootpassword";
$dbname = "tableau_bord";

// Créer la connexion
$pdo = new PDO("mysql:host=".$servername.";dbname=".$dbname, $username, $password);


// Ouvrir le fichier CSV
$file = fopen("Client_CA.csv", "r");

while (!feof($file)) {
    $data = fgetcsv($file,1000,';');

    $nomGrandClient = $data[0];
    $nomClient = $data[1];
    $numeroCentreActivite = $data[2];

    echo ("######<br>recuperation ligne nomGC = ".$nomGrandClient." nomC = ".$nomClient." numCA = ".$numeroCentreActivite."<br>");

    // Insérer ou récupérer GrandClientID

    $sql = "SELECT count(*) AS nb from  GrandClients WHERE NomGrandClient=:nomGC";
    $req=$pdo->prepare($sql);
    $req->bindValue(':nomGC', $nomGrandClient, PDO::PARAM_STR);
    $req->execute();
    $resultat=$req->fetch(PDO::FETCH_ASSOC);
    //echo ("======<br>recherche de ".$nomGrandClient." ->".$resultat["nb"]."<br>");
    
    if($resultat["nb"]==0){
        //si le grand client n'existe pas dejà on insere le nouveau grand client
        $sql="INSERT INTO GrandClients (NomGrandClient) VALUES (:nomGC)";
        $req=$pdo->prepare($sql);
        $req->bindValue(':nomGC', $nomGrandClient, PDO::PARAM_STR);
        $req->execute();
    //    echo ("insertion de ".$nomGrandClient."<br>");
    }
    $sql = "SELECT GrandClientID AS ID from  GrandClients WHERE NomGrandClient=:nomGC";
    $req=$pdo->prepare($sql);
    $req->bindValue(':nomGC', $nomGrandClient, PDO::PARAM_STR);
    $req->execute();
    $resultat=$req->fetch(PDO::FETCH_ASSOC);
    
    $grandClientId = $resultat["ID"];
    //echo("le grand client ".$nomGrandClient." a l'id ".$grandClientId."<br>" );


    // Insérer ou récupérer CentreActiviteID
    $sql = "SELECT count(*) AS nb from  CentresActivite WHERE NumeroCentreActivite=:numCA";
    $req=$pdo->prepare($sql);
    $req->bindValue(':numCA', $numeroCentreActivite, PDO::PARAM_INT);
    $req->execute();
    $resultat=$req->fetch(PDO::FETCH_ASSOC);
    //echo (">>>>>>><br>recherche du centre ".$numeroCentreActivite." ->".$resultat["nb"]."<br>");
    if($resultat["nb"]==0){
        //si le grand client n'existe pas dejà on insere le nouveau grand client
        $sql="INSERT INTO centresactivite(NumeroCentreActivite) VALUES (".$numeroCentreActivite.")";
        $req=$pdo->prepare($sql);
        //$req->bindValue(':numCA', $numeroCentreActivite, PDO::PARAM_INT);
        $req->execute();
        //echo ("insertion du centre ".$numeroCentreActivite."<br>");
        //echo ($sql."<br>");
        //echo ("INSERT INTO CentresActivite (NumeroCentreActivite) VALUES (".$numeroCentreActivite.")<br>");

    }

    $sql = "SELECT CentreActiviteID AS ID from  centresactivite WHERE NumeroCentreActivite = ".$numeroCentreActivite;
    $req=$pdo->prepare($sql);
    //$req->bindValue(':numCA', $numeroCentreActivite, PDO::PARAM_INT);
    $req->execute();
    $resultat=$req->fetch(PDO::FETCH_ASSOC);
    //echo ($sql."<br>");
    //var_dump($resultat);
    $centreActiviteId = $resultat["ID"];
    //echo("le centre d'activité ".$numeroCentreActivite." a l'id ".$centreActiviteId."<br>" );

    // Insérer Client

    $sql = "INSERT INTO Clients (NomClient, GrandClientID, CentreActiviteID) 
            VALUES ('".$nomClient."', ".$grandClientId.", ".$centreActiviteId.")";
    $req=$pdo->prepare($sql);
    
    $req->execute();
    echo ($sql."<br>");
}

fclose($file);
?>
