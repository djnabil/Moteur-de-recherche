<?php

		function indxationTXT($pathUrl){
		
		
		    $separateurs = "\ ,&; -_',!.'’()?<>\n";
            $chaine_txt = file_get_contents($pathUrl);
		     //echo $chaine_txt ;
			$tab_toks = explode_bis($chaine_txt, $separateurs);
			//print_traces_tab($tab_toks);
			
			$tab_new_mots_occurrences = array_count_values ($tab_toks);
			
			
			//print_traces_tab($tab_new_mots_occurrences);
			
			echo '<thead><tr><th>Mot</th><th>Poids</th></tr></thead>';
		
			//echo'<pre>';
			//print_r($tab_new_mots_occurrences);
			//echo'</pre>';	
         
	}
	
	foreach ($data as $indice => $valeur) {
			
			echo '<tr><td>', $indice, "</td><td>", $valeur, "</td></tr>";
			//echo $indice , " = ", $valeur, '<br>';
	        		
	
		}
	
    function explode_bis($texte, $separateurs){
		
		
	$tok =  strtok($texte, $separateurs);
	if(strlen($tok) > 2)$tab_tok[] = $tok;

	while ($tok !== false) 
	{
		$tok = strtok($separateurs);
		if(strlen($tok) > 2)$tab_tok[] = $tok;
	}
	return $tab_tok;
}



function get_title($fichier_txt)
{
    $separateurs = "\ ,&; -_',!.'’()?<>\n";
    $chaine_txt = file_get_contents($fichier_txt);
    //$modele = '/<title>(.*)<\/title>/si';
	
	if()
	

    preg_match($modele, $chaine_txt, $tableau_resultat);
    $tab_titel = explode_bis($separateurs, $tableau_resultat[1]);
    $res_tit = implode(" ", $tab_titel);
    $res = trim($res_tit);

    return $res;
}






?>