<!doctype html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Titre de la page</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
	include 'indexationHTML.php';
	//include 'indexationTXT.php';
	//Augmentation du temps
	//d'exécution de ce script
	set_time_limit(500);

	//dossier/copus de fichers à lire
	$path  = "C:/xampp/htdocs/projet_indexation_moteur_de_recherche/docs/";
	$path2 = "C:/xampp/htdocs/projet_indexation_moteur_de_recherche/docs/";


    echo '<p><b>DÉBUT DU PROCESSUS :</b><br>';
	echo " ", date ("h:i:s") . '</p>'; 
	explorerDir($path);
    echo '<p><b>FIN DU PROCESSUS :</b><br>';
    echo " ", date ("h:i:s") . '</p>';

	function explorerDir($path) {
		$souchaine = "htm";
		$souch     = "docs";
		$s         = "/";
		$path2     = $path;    
	
		$folder = opendir($path);
		while ($url = readdir($folder)) {
			if (is_file($path . $url) == true) {
				$pathUrl = $path . $url;
				//echo $pathUrl, "<BR>";
				echo '<table>';
				//indxationTXT($pathUrl);
				indexationHTML($pathUrl);
				echo '</table>';
			} else if (is_dir($path . $url . $s))
				if (($url != ".") && ($url != "..") && ($url != "...")) {
					//if (strpos($url, $souch) !== FALSE){
					//if(is_dir("$path3.$url.$s")==false){
					$path2 = $path . $url . $s;
					explorerDir($path2);
					
				}
			}
	
		closedir($folder);
		
	}
	
	
	

	

	
	function indexationHTML($pathUrl) {
        
		
		$tit  = indexerHead($pathUrl);
		$tit1 = indexerBody($pathUrl);
		
		//$Ti = get_body($pathUrl);
		//echo  $Ti;
		//echo'<pre>';
	    //print_r($Ti);
		//echo'</pre>';
		
		$gettitle = get_title($pathUrl);
		 //echo $gettitle;
		$desc = get_meta_description($pathUrl);
		  // echo $desc;
		$meta_keywords = get_meta_keywords($pathUrl);		
	      //echo $meta_keywords; 
		 //echo str_word_count($meta_keywords); 
		
		$motinde = getmot($gettitle);
		 //echo $motinde;
		 
		$dbConnection   = mysqli_connect("localhost", "root", "", "indexation_bdd");
		$data = ajouterDOC($tit, $tit1, $pathUrl);
		
		//echo '<thead><tr><th>Mot</th><th>Poids</th></tr></thead>';
        //echo '<tr><td>,'Titre',</td><td>$gettitle</td></tr>
              //<tr><td>Description</td><td></td></tr>
              //<tr><td>NbDeMot</td><td></td></tr>';
		
       	
		echo '<tr><td >', 'Source', "</td><td>", $pathUrl, "</td></tr>", "<BR>";
		echo '<tr><td>', 'Titre', "</td><td>", $gettitle, "</td></tr>", "<BR>";
		echo '<tr><td>', 'Description', "</td><td>", $desc, "</td></tr>";
		echo '<tr><td>', 'Mot-Clé', "</td><td>",  $meta_keywords, "</td></tr>";
		
		$counter = 0;
		foreach ($data as $indice => $valeur) {
			
			//echo '<tr><td>', $indice, "</td><td>", $valeur, "</td></tr>";
			//echo $indice , " = ", $valeur, '<br>';
	        		
			$urlindex = geturl($pathUrl);
			$motindex = getmot($pathUrl);
			
			$counter++;
			
			$query    = "INSERT document_mot(id_document, id_mot , poids) VALUES ('$urlindex', '$motindex', ' $valeur')";
			mysqli_query($dbConnection, $query);
		}
		echo '<tr><td>', 'NbDeMot', "</td><td>",  $counter, "</td></tr>";
		//echo $counter;

	}
?>
</body>
</html>