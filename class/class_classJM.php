<?php


class ClassJM {

function no_special_character($chaine){
 
    //  les accents
    $chaine=trim($chaine);
    $chaine= strtr($chaine,"�����������������������������������������������������","aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn");
 
    //  les carac�tres sp�ciaux (aures que lettres et chiffres en fait)
    $chaine = preg_replace('/([^.a-z0-9]+)/i', '-', $chaine);
    $chaine = strtolower($chaine);
 
    return $chaine;
 
}

function viderDossier($dossier_traite){
   
  $repertoire = opendir($dossier_traite); // On d�finit le r�pertoire dans lequel on souhaite travailler.
   
  while (false !== ($fichier = readdir($repertoire))) // On lit chaque fichier du r�pertoire dans la boucle.
  {
  $chemin = $dossier_traite."/".$fichier; // On d�finit le chemin du fichier � effacer.
   
  // Si le fichier n'est pas un r�pertoire�
  if ($fichier != ".." AND $fichier != "." AND !is_dir($chemin))
   {
      unlink($chemin); // On efface.
   }elseif($fichier != ".." AND $fichier != "." AND is_dir($chemin)){
      $this->supprimerDossier($chemin);
   }
  }
  
  closedir($repertoire);

}

function supprimerDossier($dossier_traite){
  $this->viderDossier($dossier_traite);
  rmdir($dossier_traite);
}

function chmod_Dir($dossier_traite){
   
  $repertoire = opendir($dossier_traite); // On d�finit le r�pertoire dans lequel on souhaite travailler.
   
  while (false !== ($fichier = readdir($repertoire))) // On lit chaque fichier du r�pertoire dans la boucle.
  {
  $chemin = $dossier_traite."/".$fichier; // On d�finit le chemin du fichier � effacer.
   
  // Si le fichier n'est pas un r�pertoire�
  if ($fichier != ".." AND $fichier != "." AND !is_dir($chemin))
   {
      chmod($chemin, 0777); 
   }elseif($fichier != ".." AND $fichier != "." AND is_dir($chemin)){
      $this->chmod_Dir($chemin);
   }
  }
  chmod($dossier_traite, 0777);
  closedir($repertoire);
}

function copy_dir($dir2copy,$dir_paste) 
{    
  if (is_dir($dir2copy)) 
  {  
    if ($dh = opendir($dir2copy)) 
    {                     
      while(($file = readdir($dh)) !== false) 
      {                          
        if (!is_dir($dir_paste)) mkdir($dir_paste,0777);                           
        if(is_dir($dir2copy.$file) && $file != '..'  && $file != '.') 
          copy_dir ( $dir2copy.$file.'/' , $dir_paste.$file.'/');                                     
        elseif($file != '..'  && $file != '.') copy ( $dir2copy.$file , $dir_paste.$file );
      }
      closedir($dh);
    }               
  }    
}

function createThumbs( $pathToImages, $pathToThumbs, $thumbWidth )
{
  $this->resize_upload($pathToImages, str_replace(basename($pathToThumbs), '', $pathToThumbs), "150x150", false);
}

function createImageForWeb( $pathToImages, $pathToThumbs)
{
  $this->resize_upload($pathToImages, str_replace(basename($pathToThumbs), '', $pathToThumbs), "315x315", false);
}

function callstack() {

  $retval = "callstack:\n";
  $backtrace = debug_backtrace();
  for ($idx = count($backtrace) - 1; $idx > 0; $idx--) {
    $item = $backtrace[$idx];
    if (isset($item['file']))
      $file = sprintf('%s[%03d]', pathinfo($item['file'], PATHINFO_FILENAME), $item['line']);
    else
      $file = 'eval()';
    if (isset($item['class']))
      $func = $item['class'] . '->' . $item['function'];
    else
      $func = $item['function'];
    $retval .= sprintf("\t%-32s\t%s()\n", $file, $func);
  }
  return $retval;

} 

function textForXml($text){

  return utf8_decode($text);
}
    #----------------------------------------------------
    # redimensionne une image en une seule taille
    #----------------------------------------------------
    function resize_upload($nom_image, $chemin,$taille_image_upload="75x56",$minimum=true)
    {
        $taux_mini=90;
       
        // D�finition de la largeur et de la hauteur maximale
        $t=split("x",$taille_image_upload);
        //echo $taille_image_upload;
        if($t[0]!="") $width = $t[0];
        if($t[1]!="") $height = $t[1];

        // Cacul des nouvelles dimensions
        $dim_img = getimagesize($nom_image);
        $width_orig=$dim_img[0];
        $height_orig=$dim_img[1];
       
        //Ajoute si besoin le chemin_site devant
        $chemin=chemin_site.str_replace(chemin_site,"",$chemin);
       
        if($width_orig > $width || $height_orig > $height)
        {
           
           
            if ($width_orig<=$width && $height_orig<=$height)
            {
                $width_finale=$width_orig;
                $height_finale=$height_orig;
            }
            elseif($width_orig>$width && $height_orig<=$height)
            {
                $width_finale=$width;
                $height_finale=$width*$height_orig/$width_orig;
            }
            elseif($width_orig<=$width && $height_orig>$height)
            {
                $width_finale=$height*$width_orig/$height_orig;
                $height_finale=$height;
            }
            elseif($width_orig>$width && $height_orig>$height)
            {
                $rapport1=$width_orig/$width;
                $rapport2=$height_orig/$height;
                $width_finale=$width_orig/(max($rapport1,$rapport2));
                $height_finale=$height_orig/(max($rapport1,$rapport2));
            }
           
            //echo " taille finale = ".$width_finale." x ".$height_finale;
            // Redimensionnement
           
            $image_p = imagecreatetruecolor($width_finale, $height_finale);
            if (ereg("(.jpg)$",strtolower($nom_image)))
                $src_img = imagecreatefromjpeg($nom_image);
            elseif(ereg("(.png)$",strtolower($nom_image)))
                $src_img = imagecreatefrompng($nom_image);
            elseif(ereg("(.gif)$",strtolower($nom_image)))
                $src_img = imagecreatefromgif($nom_image);
            imagecopyresampled($image_p, $src_img, 0, 0, 0, 0, $width_finale, $height_finale, $width_orig, $height_orig);
            imagejpeg($image_p,$chemin.basename($nom_image),$taux_mini);

        }
        else if ($minimum==false)
        {
            $width_finale=$width_orig;
            $height_finale=$height_orig;
            $image_p = imagecreatetruecolor($width_finale, $height_finale);
            if (ereg("(.jpg)$",strtolower($nom_image)) || ereg("(.jpeg)$",strtolower($nom_image)))
                $src_img = imagecreatefromjpeg($nom_image);
            elseif(ereg("(.png)$",strtolower($nom_image)))
                $src_img = imagecreatefrompng($nom_image);
            elseif(ereg("(.gif)$",strtolower($nom_image)))
                $src_img = imagecreatefromgif($nom_image);
            imagecopyresampled($image_p, $src_img, 0, 0, 0, 0, $width_finale, $height_finale, $width_orig, $height_orig);
            imagejpeg($image_p,$chemin.basename($nom_image),$taux_mini);
        }
        return $nom_image;
    }
}//Fin de class
?>