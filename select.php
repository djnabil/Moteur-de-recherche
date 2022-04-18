<html>
	<head>
		<title>Ma page de traitement</title>
		<script src="js/script.js"></script>
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	</head>
	<body>

	<form action="select.php" method="post" class="search-form" style="margin:auto; display:flex;max-width:300px">
		<input type="text" name="nom" placeholder="Rechercher">
		<button type="submit"><i class="fa fa-search"></i></button>
	</form>
              
<?php
	include 'indexationHTML.php';
	try
	{
		$bdd = new PDO('mysql:host=localhost;dbname=indexation_bdd;charset=utf8', 'root', '');
	}
	catch(Exception $e)
	{
		die('Erreur : ' . $e->getMessage());
	}
	if (isset($_POST['nom']))
	{
		$tab = $_POST['nom'];

		$var = strlen($tab);
		$varsur2 = $var / 2;
		$svarmot1 = substr($tab, 0, -$varsur2);
		$svarmot2 = substr($tab, -$varsur2);
		
		$href = "file:///";

		$vLettres = array();
		for ($i = 0, $vLength = strlen($tab);$i < $vLength;$i++)
		{
			$vLettres[] = $tab[$i];
		}
		sort($vLettres);
		$motOrder = implode($vLettres);
		$idv = mysqli_connect("localhost", "root", "", "indexation_bdd");
		$on = " select id FROM mot WHERE mot LIKE '$svarmot1%' OR mot LIKE '%$svarmot2' OR mot = '$tab' ";
		$resultat = mysqli_query($idv, $on);
		$rowcount = mysqli_num_rows($resultat);
		if ($rowcount == 0)
		{
	?>
		<h1  class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">Mot non trouvé !</H1> 
	<?php
		exit(0);
		}

		$query = " select * FROM mot WHERE mot = '$tab' ";

		$result = mysqli_query($idv, $query);
		$rows_number = mysqli_num_rows($result);
		
		if ($rows_number != 0)
		{
			$response = $bdd->query($query);
			while ($donnee = $response->fetch())
			{
				$res = $donnee['id'];
				//echo $res;
				

				chercher_afficher($res);
			}
			$response->closeCursor();
			exit(0);
		}
	
		$response = $bdd->query("select id FROM mot WHERE mot LIKE '$svarmot1%' OR mot  LIKE '%$svarmot2'");
		while ($donnee = $response->fetch())
		{
			$res = $donnee['id'];

			chercher_afficher($res);
		}
		$response->closeCursor();		
	}
?>
