<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title></title>
  <link href="style.css" rel="stylesheet" type="text/css" />
  </head>
  <body>
 <?php
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
mkdir(chemin_temp.'xml_ref/');
$arbo = array();
foreach($listeFichierXML as $file){
    $oXML->setCheminXml($file);
    echo "traitement de : ".$file;
    $oXML->traiteXml($file);

}
?>
  </body>
</html>