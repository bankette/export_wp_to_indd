<?php
$header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title></title>
  <link href="style.css" rel="stylesheet" type="text/css" />
  </head>
  <body>';
$footer = '</body>
</html>';
echo $header;
include ("include/definitions.php");
include("class/class_importXML.php");

$oXML = new ImportXML();
$oXML->viderDossier(chemin_site."Temp/");
$dirname = chemin_site.'In/';
$dir = opendir($dirname);
$retour = array();
while($file = readdir($dir)) {
    if($file != '.' && $file != '..' && !is_dir($dirname.$file))
    {
        if(strToLower(substr($file,-4))=='.xml'){
            $listeFichierXML[] = $dirname.$file;
        }
    }
}
mkdir(chemin_temp.'html/');
$arbo = array();
foreach($listeFichierXML as $file){
    $oXML->setCheminXml($file);
    $oChapitre = $oXML->traiteXml($file);
    $acc = $header.$oChapitre->getHTML().$footer;
    $cheminFichierHtml = chemin_temp.'html/'.$oChapitre->getRubrique().'.html';
    if(is_file($cheminFichierHtml))
        unlink($cheminFichierHtml);
    $monfichier = fopen($cheminFichierHtml, 'w+');
    fputs($monfichier, $acc);
    fclose($monfichier);
    $oChapitre->setLienHtml($cheminFichierHtml);
    $arbo[$oChapitre->getPartie()][$oChapitre->getRubrique()] = $oChapitre;
    foreach($arbo as $key=>$array){
        asort($array);
        $arboTemp[$key] = $array;
    }
    $arbo = $arboTemp;
}

$accPrinc = "<h1>Sommaire</h1>";
$miniIndex = "<h1 id='sommairePrincipal'>Sommaire</h1>";

foreach($arbo as $nom=>$arrayOChapitre){

    $miniIndex .= "<h2><span class='picto'>".Chapitre::getPolicePartieByNumber(substr($nom,0,3))."</span><a class='niveau1' href = 'sommaire".substr($nom,0,3).".html'>".$nom."</a></h2>";
    $accPrinc .= "<h2><span class='picto'>".Chapitre::getPolicePartieByNumber(substr($nom,0,3))."</span><a class='niveau1' href = 'sommaire".substr($nom,0,3).".html'>".$nom."</a></h2>";
    $cheminFichierHtmlSommaire = chemin_temp.'html/sommaire'.substr($nom,0,3).'.html';
    if(is_file($cheminFichierHtmlSommaire))
        unlink($cheminFichierHtmlSommaire);
    $monfichier = fopen($cheminFichierHtmlSommaire, 'w+');
    $acc = $header;
    $acc .= "<h1><span class='picto'>".Chapitre::getPolicePartieByNumber(substr($nom,0,3))."</span>".$nom."</h1>";
    //echo '<pre>';print_r($arrayOChapitre);echo '</pre>';
    foreach($arrayOChapitre as $oChapitre){
        $accPrinc .= "<p><a class='niveau2' href = '".basename($oChapitre->getLienHtml())."'>".$oChapitre->getRubrique().' - '.$oChapitre->getTitre()."</a></p>";
        $acc .= "<p><a class='niveau2' href = '".basename($oChapitre->getLienHtml())."'>".$oChapitre->getRubrique().' - '.$oChapitre->getTitre()."</a></p>";
    }
    $acc .= $footer;
    fputs($monfichier, $acc);
    fclose($monfichier);
}

$miniIndex .= '<div class="sautDePage"></div>';
$accPrinc = $header.$miniIndex.$accPrinc;
$accPrinc .= $footer;
$cheminFichierHtmlSommairePrinc = chemin_temp.'html/sommaire.html';
if(is_file($cheminFichierHtmlSommairePrinc))
    unlink($cheminFichierHtmlSommairePrinc);
$monfichierPrinc = fopen($cheminFichierHtmlSommairePrinc, 'w+');
fputs($monfichierPrinc, $accPrinc);
fclose($monfichierPrinc);
//$oChapitre->setLienHtml($cheminFichierHtml);


$oEpub = new Epub('epubTest', chemin_temp.'html/', 'E:/wamp/www/conversion_cleon/In/Images/');
$oEpub->setArborescence($arbo);
$oEpub->genererEpub();

echo 'OK';

echo $footer;
?>
