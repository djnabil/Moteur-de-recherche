<?php
function sort_text($str)
{
    $str = strtolower($str);
    $str = trim($str);
    $str = preg_replace("#\n|\t|\r#", " ", $str);
    $str = preg_replace("#;#", " ", $str);
    $str = str_replace(",", " ", $str);
    $str = str_replace(".", " ", $str);

    $emptyWordsList = fopen('listmots.txt', 'rb');
    $tabEmptyWords = fgets($emptyWordsList);
    fclose($emptyWordsList);

    $pieces = explode(",", $tabEmptyWords);
    $t = str_replace($pieces, " ", $str);
    $t = trim($t);

    $separateurs = " &;:-_',!.'’()}{|?=+<>\n";
    $tab_mots_html = explode_bis($separateurs, $t);

    $tab_mots_occurrences = array_count_values($tab_mots_html);

    return $tab_mots_occurrences;
}

function get_title($fichier_html)
{
    $separateurs = "\ ,&; -_',!.'’()?<>\n";
    $chaine_html = file_get_contents($fichier_html);
    $modele = '/<title>(.*)<\/title>/si';

    preg_match($modele, $chaine_html, $tableau_resultat);
    $tab_titel = explode_bis($separateurs, $tableau_resultat[1]);
    $res_tit = implode(" ", $tab_titel);
    $res = trim($res_tit);

    return $res;
}

function trierdesc($fic)
{
    $separateurs = "\ ,&; -_',!.'’()<>?\n";
    $tab_titel = explode_bis($separateurs, $fic);
    $res_tit = implode(" ", $tab_titel);
    $res = trim($res_tit);
    return $res;
}

function get_meta_description($nomdefichierHTML)
{
    $tableau_associatif_metas = get_meta_tags($nomdefichierHTML);
	if (array_key_exists('description', $tableau_associatif_metas)) {
		return $tableau_associatif_metas['description'];
	}
    return '';
}

function get_body($fichier_html)
{
    $chaine_html = file_get_contents($fichier_html);
    $modele = '/<body>(.*)<\/body>/si';
    preg_match($modele, $chaine_html, $tableau_resultat);
    return $tableau_resultat[1];
}

function get_meta_keywords($nomdefichierHTML)
{
    $tableau_associatif_metas = get_meta_tags($nomdefichierHTML);
    return $tableau_associatif_metas['keywords'];
}

function explode_bis($separateurs, $concatenation)
{
    $tok = strtok($concatenation, $separateurs);
    if (strlen($tok) > 2) $tab_mots_html[] = $tok;
    while ($tok !== false)
    {
        $tok = strtok($separateurs);
        if (strlen($tok) > 2) $tab_mots_html[] = $tok;
    }
    return $tab_mots_html;
}

function print_tab($tab_mots_html)
{
    foreach ($tab_mots_html as $indice => $valeur) echo $indice, " = ", $valeur, '<br>';
}

function getredandance($str)
{
    $id = mysqli_connect("localhost", "root", "", "indexation_bdd");
    $con = " select redondance FROM redondanceindex WHERE redondance = '$str'";
    $resultat = mysqli_query($id, $con);
    $rowcount = mysqli_num_rows($resultat);

    if ($rowcount == 0)
    {
        $co = "INSERT redondanceindex(id, redondance)VALUES ('', '$str')";
        mysqli_query($id, $co);
        $cnn = " select id FROM redondanceindex WHERE redondance = '$str'";
        $result = mysqli_query($id, $cnn);
        $ligne = mysqli_fetch_array($result);
    }
    else
    { // actions }
        $cn = " select id FROM redondanceindex WHERE redondance = '$str'";
        $result = mysqli_query($id, $cn);
        $ligne = mysqli_fetch_array($result);
    }
    return $ligne['id'];
}

function indexerHead($nomdefichierHTML)
{
    //1- Extraction des éléments du head à indexer
    $titre = get_title($nomdefichierHTML);
    $description = get_meta_description($nomdefichierHTML);
    $keywords = get_meta_keywords($nomdefichierHTML);

    //2- Texte du HEAD à INDEXER
    $texte_head = $titre . " " . $description . " " . $keywords;
    $text0 = replaceSpecialChar($texte_head);
    $tab_tite0 = utf8_decode($text0);

    $tab_tite0 = trim($tab_tite0);
    $tit = sort_text($texte_head);

    return $tit;
}

function indexerBody($nomdefichierHTML)
{

/*	
    //Extraction de la chaine HTML BODY
    $texte_html_body = get_body($nomdefichierHTML);
    //Suppression des script : javascript par exemple
    strip_scripts($texte_html_body);
    //Suppresion du formatage HTML
    $texte_body = strip_tags($texte_html_body);
    //3- Traitement du texte BODY  : Tokenisation, filtrage, occurrences
    $separateurs = " ,!.'’()  /";
    //$texte = strtolower($texte_body);
    $text0 = replaceSpecialChar($texte_body);
    $tab_tite0 = utf8_decode($text0);
    $tab_tite0 = str_replace(array('&nbsp;') , '', $tab_tite0);

    $body = sort_text($tab_tite0);
    return $body;
	
*/
	
	$texte_file_html = file_get_contents("$nomdefichierHTML");
    $texte_file_html = mb_convert_encoding($texte_file_html, 'HTML-ENTITIES', "UTF-8");

        $modele = "/<body[^>]*>(.*?)<\/body>/si";

        $texte_file_html = strip_scripts($texte_file_html);

        preg_match($modele, $texte_file_html, $tab_resultats);

        // La chaine HTML body
        $texte_html_body = $tab_resultats[0];

        // Suppresion du formatage HTML
        $texte_body = strip_tags($texte_html_body);

        $resultat = sort_text($texte_body);
        return $resultat;
	
	

	
}
	// Enlève les balises script au sein du body
	function strip_scripts($chaine_html) {
		$modele = "/<script[^>](.*?)<\/script>/si";
		$chaine_html = preg_replace($modele, "", $chaine_html);

		return $chaine_html;
	}


//function strip_scripts($chaine_html)
//{

//    preg_replace($modele, '', $chaine_html);
//}

function ajouterDOC($tit, $tit1, $url)
{
    $data = [];
    $ti = multiplicationHead($tit);
    $fusionTableau = array_merge_recursive($ti, $tit1);

    foreach ($fusionTableau as $indice => $vaeur)
    {
        $id = mysqli_connect("localhost", "root", "", "indexation_bdd");
        if (is_array($vaeur))
        {

            $vaeur = array_sum($vaeur);

        }
        $data += ["$indice" => $vaeur];
        $urlindex = geturl($url);
        $motindex = getmot($indice);
    }
    return $data;

}

function multiplicationHead($tit)
{
    $func = function ($value)
    {
        return $value * 1.5;
    };

    foreach ($tit as $indice => $valeur)
    {
        $ti = array_map($func, $tit);

    }
    return $ti;
}
function getmot($vv)
{
    $id = mysqli_connect("localhost", "root", "", "indexation_bdd");
    $con = " select mot FROM mot WHERE mot = '$vv'";
    $resultat = mysqli_query($id, $con);
	$rowcount = 0;
	$ligne['id'] = "";
	if($resultat != null) {
		$rowcount = mysqli_num_rows($resultat);
	}	
    
    if ($rowcount == 0)
    {
        $co = "INSERT mot(id, mot)VALUES ('', '$vv')";
        mysqli_query($id, $co);
        $cnn = " select id FROM mot WHERE mot = '$vv'";
        $toto = mysqli_query($id, $cnn);
		if($toto != null) {
			$ligne = mysqli_fetch_array($toto);
		}
    }
    else
    { // actions }
        $cn = " select id FROM mot WHERE mot = '$vv'";
        mysqli_query($id, $cn);
        $toto = mysqli_query($id, $cn);
		if($toto != null) {
			$ligne = mysqli_fetch_array($toto);
		}
    }
    return $ligne['id'];
}

function geturl($vv)
{
    $id = mysqli_connect("localhost", "root", "", "indexation_bdd");
    $con = " select document FROM document WHERE document = '$vv'";
    $resultat = mysqli_query($id, $con);
    $rowcount = mysqli_num_rows($resultat);

    if ($rowcount == 0)
    {
        $titre = get_title($vv);
        $description = get_meta_description($vv);
        $key = get_meta_keywords($vv);
        $descriptionKeyworld = $description . " " . $key;
        $text0 = replaceSpecialChar($descriptionKeyworld);
        $tab_tite0 = utf8_decode($text0);

        $descriptionKeyworld1 = trierdesc($tab_tite0);

        $co = "INSERT document(id, document, titre, description)VALUES ('', '$vv', '$titre', '$descriptionKeyworld1')";
        mysqli_query($id, $co);
        $cnn = " select id FROM document WHERE document = '$vv'";
        $toto = mysqli_query($id, $cnn);
        $ligne = mysqli_fetch_array($toto);
        // $ligne=mysqli_num_rows($toto);
        
    }
    else
    { // actions }
        $cn = " select id FROM document WHERE document = '$vv'";
        $toto = mysqli_query($id, $cn);
        $ligne = mysqli_fetch_array($toto);
    }
    return $ligne['id'];
}

function genererNuage($data = array() , $minFontSize = 10, $maxFontSize = 36)
{
    $tab_colors = array(
        "#3087F8",
        "#7F814E",
        "#EC1E85",
        "#14E414",
        "#9EA0AB",
        "#9EA414"
    );

    $minimumCount = min(array_values($data));
    $maximumCount = max(array_values($data));
    $spread = $maximumCount - $minimumCount;
    $cloudHTML = '';
    $cloudTags = array();

    $spread == 0 && $spread = 1;
    //Mélanger un tableau de manière aléatoire
    srand((float)microtime() * 1000000);
    $mots = array_keys($data);
    shuffle($mots);

    foreach ($mots as $tag)
    {
        $count = $data[$tag];

        //La couleur aléatoire
        $color = rand(0, count($tab_colors) - 1);

        $size = $minFontSize + ($count - $minimumCount) * ($maxFontSize - $minFontSize) / $spread;
        $cloudTags[] = '<a style="font-size: ' . floor($size) . 'px' . '; color:' . $tab_colors[$color] . '; " title="Rechercher le tag ' . $tag . '" href="rechercher.php?q=' . urlencode($tag) . '">' . $tag . '</a>';
    }
    return join("\n", $cloudTags) . "\n";
}

function chercher_afficher($res)
{
    $href = "file:///";
    $bdd = new PDO('mysql:host=localhost;dbname=indexation_bdd;charset=utf8', 'root', '');
    $cns = " select * FROM document_mot WHERE id_mot = '$res' ORDER BY poids DESC";
    $reponse = $bdd->query($cns);

    while ($donnees = $reponse->fetch())
    {
        $ress = $donnees['id_document'];
        $cnst = " select * FROM document WHERE id = '$ress' ";
        $rep = $bdd->query($cnst);
        while ($donn = $rep->fetch())
        {
            echo "<br />\n";
?>
	
	<h3>
		<strong style=color:blue;> <a href="<?php echo $href.$donn['document']; ?>" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6"><?php echo $donn['titre']; ?></a></strong> 
		<em>
			<?php echo $donn['description']; ?> <br />
		</em> 
		<em>
			<?php echo $donnees['poids']; ?>
		</em>
	</h3>
	
	<?php 
		$t_head=indexerHead($donn['document']);
		$t_body=indexerBody($donn['document']);
		$data=ajouterDOC($t_head,$t_body,$donn['document']);
	?>
	<button id="a1" type="button" onclick="toggle_show('<?php echo $donn['document']; ?>')" >nuage des mots</button>

    <div id="<?php echo $donn['document'] ?>" style="display:none" >
       <?php echo genererNuage( $data ); ?>
    </div>
	       
   <?php
		}
		$rep->closeCursor();	 
	}
	$reponse->closeCursor();
}

function replaceSpecialChar($str)
{
  $ch0 = array(
    "œ" => "oe",
    "Œ" => "OE",
    "æ" => "ae",
    "Æ" => "AE",
    "À" => "A",
    "Á" => "A",
    "Â" => "A",
    "à" => "A",
    "Ä" => "A",
    "Å" => "A",
    "à" => "a",
    "á" => "a",
    "â" => "a",
    "à" => "a",
    "ä" => "a",
    "å" => "a",
    "Ç" => "C",
    "ç" => "c",
    "Ð" => "D",
    "È" => "E",
    "É" => "E",
    "Ê" => "E",
    "Ë" => "E",
    "è" => "e",
    "é" => "e",
    "ê" => "e",
    "ë" => "e",
    "&#275;" => "e",
    "&#277;" => "e",
    "&#279;" => "e",
    "&#281;" => "e",
    "&#283;" => "e",
    "&#7865;" => "e",
    "&#7867;" => "e",
    "&#7869;" => "e",
    "&#7871;" => "e",
    "&#7873;" => "e",
    "&#7875;" => "e",
    "&#7877;" => "e",
    "&#7879;" => "e",
    "&#284;" => "G",
    "&#286;" => "G",
    "&#288;" => "G",
    "&#290;" => "G",
    "&#285;" => "g",
    "&#287;" => "g",
    "&#289;" => "g",
    "&#291;" => "g",
    "&#292;" => "H",
    "&#294;" => "H",
    "&#293;" => "h",
    "&#295;" => "h",
    "Ì" => "I",
    "Í" => "I",
    "Î" => "I",
    "Ï" => "I",
    "&#296;" => "I",
    "&#298;" => "I",
    "&#300;" => "I",
    "&#302;" => "I",
    "&#304;" => "I",
    "&#463;" => "I",
    "&#7880;" => "I",
    "&#7882;" => "I",
    "&#308;" => "J",
    "&#309;" => "j",
    "&#310;" => "K",
    "&#311;" => "k",
    "&#313;" => "L",
    "&#315;" => "L",
    "&#317;" => "L",
    "&#319;" => "L",
    "&#321;" => "L",
    "&#314;" => "l",
    "&#316;" => "l",
    "&#318;" => "l",
    "&#320;" => "l",
    "&#322;" => "l",
    "Ñ" => "N",
    "&#323;" => "N",
    "&#325;" => "N",
    "&#327;" => "N",
    "ñ" => "n",
    "&#324;" => "n",
    "&#326;" => "n",
    "&#328;" => "n",
    "&#329;" => "n",
    "Ò" => "O",
    "Ó" => "O",
    "Ô" => "O",
    "Õ" => "O",
    "Ö" => "O",
    "Ø" => "O",
    "&#332;" => "O",
    "&#334;" => "O",
    "&#336;" => "O",
    "&#416;" => "O",
    "&#465;" => "O",
    "&#510;" => "O",
    "&#7884;" => "O",
    "&#7886;" => "O",
    "&#7888;" => "O",
    "&#7890;" => "O",
    "&#7892;" => "O",
    "&#7894;" => "O",
    "&#7896;" => "O",
    "&#7898;" => "O",
    "&#7900;" => "O",
    "&#7902;" => "O",
    "&#7904;" => "O",
    "&#7906;" => "O",
    "ò" => "o",
    "ó" => "o",
    "ô" => "o",
    "õ" => "o",
    "ö" => "o",
    "ø" => "o",
    "&#333;" => "o",
    "&#335;" => "o",
    "&#337;" => "o",
    "&#417;" => "o",
    "&#466;" => "o",
    "&#511;" => "o",
    "&#7885;" => "o",
    "&#7887;" => "o",
    "&#7889;" => "o",
    "&#7891;" => "o",
    "&#7893;" => "o",
    "&#7895;" => "o",
    "&#7897;" => "o",
    "&#7899;" => "o",
    "&#7901;" => "o",
    "&#7903;" => "o",
    "&#7905;" => "o",
    "&#7907;" => "o",
    "ð" => "o",
    "&#340;" => "R",
    "&#342;" => "R",
    "&#344;" => "R",
    "&#341;" => "r",
    "&#343;" => "r",
    "&#345;" => "r",
    "&#346;" => "S",
    "&#348;" => "S",
    "&#350;" => "S",
    "&#347;" => "s",
    "&#349;" => "s",
    "&#351;" => "s",
    "&#354;" => "T",
    "&#356;" => "T",
    "&#358;" => "T",
    "&#355;" => "t",
    "&#357;" => "t",
    "&#359;" => "t",
    "Ù" => "U",
    "Ú" => "U",
    "Û" => "U",
    "Ü" => "U",
    "&#360;" => "U",
    "&#362;" => "U",
    "&#364;" => "U",
    "&#366;" => "U",
    "&#368;" => "U",
    "&#370;" => "U",
    "&#431;" => "U",
    "&#467;" => "U",
    "&#469;" => "U",
    "&#471;" => "U",
    "&#473;" => "U",
    "&#475;" => "U",
    "&#7908;" => "U",
    "&#7910;" => "U",
    "&#7912;" => "U",
    "&#7914;" => "U",
    "&#7916;" => "U",
    "&#7918;" => "U",
    "&#7920;" => "U",
    "ù" => "u",
    "ú" => "u",
    "û" => "u",
    "ü" => "u",
    "&#361;" => "u",
    "&#363;" => "u",
    "&#365;" => "u",
    "&#367;" => "u",
    "&#369;" => "u",
    "&#371;" => "u",
    "&#432;" => "u",
    "&#468;" => "u",
    "&#470;" => "u",
    "&#472;" => "u",
    "&#474;" => "u",
    "&#476;" => "u",
    "&#7909;" => "u",
    "&#7911;" => "u",
    "&#7913;" => "u",
    "&#7915;" => "u",
    "&#7917;" => "u",
    "&#7919;" => "u",
    "&#7921;" => "u",
    "&#372;" => "W",
    "&#7808;" => "W",
    "&#7810;" => "W",
    "&#7812;" => "W",
    "&#373;" => "w",
    "&#7809;" => "w",
    "&#7811;" => "w",
    "&#7813;" => "w",
    "Ý" => "Y",
    "&#374;" => "Y",
    "?" => "Y",
    "&#7922;" => "Y",
    "&#7928;" => "Y",
    "&#7926;" => "Y",
    "&#7924;" => "Y",
    "ý" => "y",
    "ÿ" => "y",
    "&#375;" => "y",
    "&#7929;" => "y",
    "&#7925;" => "y",
    "&#7927;" => "y",
    "&#7923;" => "y",
    "&#377;" => "Z",
    "&#379;" => "Z",
    "&iexcl;" => "¡",
    "&cent;" => "¢",
    "&pound;" => "£",
    "&curren;" => "¤",
    "&yen" => "¥",
    "&brvbar;" => "¦",
    "&sect;" => "§",
    "&uml;" => "¨",
    "&copy;" => "©",
    "&ordf;" => "ª",
    "&laquo;" => "«",
    "&not;" => "¬",
    "&shy;" => "­",
    "&reg;" => "®",
    "&masr;" => "¯",
    "&deg;" => "°",
    "&plusmn;" => "±",
    "&sup2;" => "²",
    "&sup3;" => "³",
    "&acute;" => "'",
    "&micro;" => "µ",
    "&para;" => "¶",
    "&middot;" => "·",
    "&cedil;" => "¸",
    "&sup1;" => "¹",
    "&ordm;" => "º",
    "&raquo;" => "»",
    "&frac14;" => "¼",
    "&frac12;" => "½",
    "&frac34;" => "¾",
    "&iquest;" => "¿",
    "&Agrave;" => "À",
    "&Aacute;" => "Á",
    "&Acirc;" => "Â",
    "&Atilde;" => "Ã",
    "&Auml;" => "Ä",
    "&Aring;" => "Å",
    "&Aelig" => "Æ",
    "&Ccedil;" => "Ç",
    "&Egrave;" => "È",
    "&Eacute;" => "É",
    "&Ecirc;" => "Ê",
    "&Euml;" => "Ë",
    "&Igrave;" => "Ì",
    "&Iacute;" => "Í",
    "&Icirc;" => "Î",
    "&Iuml;" => "Ï",
    "&eth;" => "Ð",
    "&Ntilde;" => "Ñ",
    "&Ograve;" => "Ò",
    "&Oacute;" => "Ó",
    "&Ocirc;" => "Ô",
    "&Otilde;" => "Õ",
    "&Ouml;" => "Ö",
    "&times;" => "×",
    "&Oslash;" => "Ø",
    "&Ugrave;" => "Ù",
    "&Uacute;" => "Ú",
    "&Ucirc;" => "Û",
    "&Uuml;" => "Ü",
    "&Yacute;" => "Ý",
    "&thorn;" => "Þ",
    "&szlig;" => "ß",
    "&agrave;" => "à",
    "&aacute;" => "á",
    "&acirc;" => "â",
    "&atilde;" => "ã",
    "&auml;" => "ä",
    "&aring;" => "å",
    "&aelig;" => "æ",
    "&ccedil;" => "ç",
    "&egrave;" => "è",
    "&eacute;" => "é",
    "&ecirc;" => "ê",
    "&euml;" => "ë",
    "&igrave;" => "ì",
    "&iacute;" => "í",
    "&icirc;" => "î",
    "&iuml;" => "ï",
    "&eth;" => "ð",
    "&ntilde;" => "ñ",
    "&ograve;" => "ò",
    "&oacute;" => "ó",
    "&ocirc;" => "ô",
    "&otilde;" => "õ",
    "&ouml;" => "ö",
    "&divide;" => "÷",
    "&oslash;" => "ø",
    "&ugrave;" => "ù",
    "&uacute;" => "ú",
    "&ucirc;" => "û",
    "&uuml;" => "ü",
    "&yacute;" => "ý",
    "&thorn;" => "þ",
    "&yuml;" => "ÿ"
  );
  $str = strtr($str, $ch0);
  return $str;
}
