    <?php
$servername = "172.18.143.44:30120";
$username = "root";
$password = "rootpassword";
$dbname = "tableau_bord";

// Créer la connexion
$pdo = new PDO("mysql:host=".$servername.";dbname=".$dbname, $username, $password);


// Ouvrir le fichier CSV
$file = fopen("Base_facture.csv", "r");
$colonnes = fgetcsv($file);//lecture ligne de titres

while (!feof($file)) {
    $data = fgetcsv($file,1000,';');
    
    $IRT=strtoupper($data[0]);
    $famille= strtoupper($data[1]);
    $nomProduit= strtoupper($data[2]);
    $numeroCentreActivite = $data[3];
    $mois= $data[4];
    $montant= $data[5];
    $unite= strtoupper($data[6]);
    $quantite= $data[7];


    echo ("######<br>\nrecuperation ligne applicatif = ".$IRT." famille = ".$famille." produit = ".$nomProduit." numCA = ".$numeroCentreActivite." mois = ".$mois." montant = ".$montant." unité = ".$unite." quantite = ".$quantite."<br>\n");

    // Insérer ou récupérer IRT

    $sql = "SELECT count(*) AS nb from  application WHERE IRT=:irt";
    $req=$pdo->prepare($sql);
    $req->bindValue(':irt', $IRT, PDO::PARAM_STR);
    $req->execute();
    $resultat=$req->fetch(PDO::FETCH_ASSOC);
    echo ("======<br>\nrecherche de l'applicatif".$IRT." ->".$resultat["nb"]."<br>\n");
    
    if($resultat["nb"]==0){
    //si le grand client n'existe pas dejà on insere le nouveau grand client
        $sql="INSERT INTO `application` (`IRT`, `nomAppli`) VALUES (:IRT, \"appli non renseigné\");";
        $req=$pdo->prepare($sql);
        $req->bindValue(':IRT', $IRT, PDO::PARAM_STR);
        $req->execute();
    //    echo ("insertion de ".$nomGrandClient."<br>\n");
    }

    $sql = "SELECT nomAppli AS nomA from  application WHERE IRT=:irt";
    $req=$pdo->prepare($sql);
    $req->bindValue(':irt', $IRT, PDO::PARAM_STR);
    $req->execute();
    $resultat=$req->fetch(PDO::FETCH_ASSOC);
    $nomAppli = $resultat["nomA"];
    echo(" -> applicatif ".$nomAppli." a l'id ".$IRT."<br>\n" );

    // Insérer ou récupérer Famille

    $sql = "SELECT count(*) AS nb from  famille WHERE FAMILLE_NAME=:nomF";
    $req=$pdo->prepare($sql);
    $req->bindValue(':nomF', $famille, PDO::PARAM_STR);
    $req->execute();
    $resultat=$req->fetch(PDO::FETCH_ASSOC);
    //echo ("======<br>\nrecherche de ".$nomGrandClient." ->".$resultat["nb"]."<br>\n");
    
    if($resultat["nb"]==0){
    //si le grand client n'existe pas dejà on insere le nouveau grand client
        $sql="INSERT INTO `famille` (`FAMILLE_NAME`) VALUES (:nomf);";
        $req=$pdo->prepare($sql);
        $req->bindValue(':nomf', $famille, PDO::PARAM_STR);
        $req->execute();
    //    echo ("insertion de ".$nomGrandClient."<br>\n");
    }
    $sql = "SELECT `familleID` AS ID from  famille WHERE FAMILLE_NAME=:nomF";
    $req=$pdo->prepare($sql);
    $req->bindValue(':nomF', $famille, PDO::PARAM_STR);
    $req->execute();
    $resultat=$req->fetch(PDO::FETCH_ASSOC);
    $familleID = $resultat["ID"];
    echo(" -> famille ".$famille." a l'id ".$familleID."<br>\n" );

    // Insérer ou récupérer produit

    $sql = "SELECT count(*) AS nb from  produit WHERE familleID=:idF AND NOM_PRODUIT=:nomP";
    $req=$pdo->prepare($sql);
    $req->bindValue(':idF', $familleID, PDO::PARAM_STR);
    $req->bindValue(':nomP', $nomProduit, PDO::PARAM_STR);
    $req->execute();
    $resultat=$req->fetch(PDO::FETCH_ASSOC);
    //echo ("======<br>\nrecherche de ".$nomGrandClient." ->".$resultat["nb"]."<br>\n");
    
    if($resultat["nb"]==0){
    //si le grand client n'existe pas dejà on insere le nouveau grand client
        $sql="INSERT INTO `produit` (`NOM_PRODUIT`,`familleID`) VALUES (:nomP,:idF);";
        $req=$pdo->prepare($sql);
        $req->bindValue(':nomP', $nomProduit, PDO::PARAM_STR);
        $req->bindValue(':idF', $familleID, PDO::PARAM_STR);
        $req->execute();
    //    echo ("insertion de ".$nomGrandClient."<br>\n");
    }
    $sql = "SELECT produitID AS ID from  produit WHERE familleID=:idF AND NOM_PRODUIT=:nomP";
    $req=$pdo->prepare($sql);
    $req->bindValue(':idF', $familleID, PDO::PARAM_STR);
    $req->bindValue(':nomP', $nomProduit, PDO::PARAM_STR);
    $req->execute();
    $resultat=$req->fetch(PDO::FETCH_ASSOC);
    $produitID = $resultat["ID"];
    echo(" -> produit ".$nomProduit." a l'id ".$produitID."<br>\n" );

    // Insérer ou récupérer CentreActiviteID
    $sql = "SELECT count(*) AS nb from  CentresActivite WHERE NumeroCentreActivite=:numCA";
    $req=$pdo->prepare($sql);
    $req->bindValue(':numCA', $numeroCentreActivite, PDO::PARAM_INT);
    $req->execute();
    $resultat=$req->fetch(PDO::FETCH_ASSOC);
    //echo (">>>>>>><br>\nrecherche du centre ".$numeroCentreActivite." ->".$resultat["nb"]."<br>\n");
    if($resultat["nb"]==0){
        //si le grand client n'existe pas dejà on insere le nouveau grand client
        $sql="INSERT INTO centresactivite(NumeroCentreActivite) VALUES (".$numeroCentreActivite.")";
        $req=$pdo->prepare($sql);
        //$req->bindValue(':numCA', $numeroCentreActivite, PDO::PARAM_INT);
        $req->execute();
        //echo ("insertion du centre ".$numeroCentreActivite."<br>\n");
        //echo ($sql."<br>\n");
        //echo ("INSERT INTO CentresActivite (NumeroCentreActivite) VALUES (".$numeroCentreActivite.")<br>\n");

    }

    $sql = "SELECT CentreActiviteID AS ID from  centresactivite WHERE NumeroCentreActivite = ".$numeroCentreActivite;
    $req=$pdo->prepare($sql);
    //$req->bindValue(':numCA', $numeroCentreActivite, PDO::PARAM_INT);
    $req->execute();
    $resultat=$req->fetch(PDO::FETCH_ASSOC);
    //echo ($sql."<br>\n");
    //var_dump($resultat);
    $centreActiviteId = $resultat["ID"];
    echo("  -> le centre d'activité ".$numeroCentreActivite." a l'id ".$centreActiviteId."<br>\n" );

    // Insérer Client

    $sql = "INSERT INTO ligne_facturation (produitID, CentreActiviteID, mois, IRT, prix, nature, volume) 
            VALUES (:pID, :CAID, :mois, :IRT, :px, :nat, :vol)";
    $req=$pdo->prepare($sql);
    //$req->bindValue(':mois', $mois, PDO::PARAM_STR);
    $req->bindValue(':mois', date("Y-d-m", strtotime($mois)), PDO::PARAM_STR);
    $req->bindValue(':CAID', $centreActiviteId, PDO::PARAM_INT);
    $req->bindValue(':pID', $produitID, PDO::PARAM_INT);
    $montant=str_replace(",",".",$montant);
    $req->bindValue(':px', $montant, PDO::PARAM_STR);
    $quantite=str_replace(",",".",$quantite);
    $req->bindValue(':nat', $unite, PDO::PARAM_STR);
    $req->bindValue(':vol', $quantite, PDO::PARAM_STR);
    $req->bindValue(':IRT', $IRT, PDO::PARAM_STR);
    $req->execute();

    echo (" =>ligne insérée<br>\n");
}

fclose($file);
?>
