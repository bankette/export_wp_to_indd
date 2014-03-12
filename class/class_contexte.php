<?php
require_once("class_classJM.php");
class Contexte extends ClassJM{
  protected $contexte;
  protected $contexteNumero;
  protected $ordre;
  
  public function __construct(){
    $this->contexte[-1] = "##Niveau 0##";
    $this->ordre[-1] = 0;
    $this->contexteNumero = -1;
  }
  public function AjouterAuDessusPile($nomBalise, $attribut = array()){
    $this->contexteNumero ++;
    $this->contexte[$this->contexteNumero]['nom'] = $nomBalise;
    $this->contexte[$this->contexteNumero]['attribut'] = $attribut;
    $this->ordre[$this->contexteNumero-1] ++;
    $this->ordre[$this->contexteNumero] = 0;
  }
  
  public function retirerDernierPile(){
    unset($this->contexte[$this->contexteNumero]);
    unset($this->ordre[$this->contexteNumero]);
    $this->contexteNumero --;
  }
  
  public function getFullContexte(){
    return $this->contexte;
  }
  
  public function getFullOrdre(){
    return $this->ordre;
  }
  
  public function getLastContexte(){
    return $this->contexte[$this->contexteNumero];
  }
  
  public function getLastContexteWithName($nomBalise){
    $retour = $this->getAllContexteWithName($nomBalise);
    if(isset($retour[0]))
      return $retour[0];
    else
      return false;
  }
  
  public function getAllContexteWithName($nomBalise){
    // renvoi un tableau ordonné du plus plus fin vers le plus large
    // Attention ne pas modifier car utilisé ailleurs dans cette fonction
    $i = $this->contexteNumero;
    $retour = array();
    while($i >= 0){
      if($this->contexte[$i]['nom'] == $nomBalise){
        $retour[] = $this->contexte[$i];
      }
      $i--;
    }
    return $retour;
  }
  
  public function isInContexte($nomBalise){
    if(!is_array($nomBalise)){
      $nomBaliseTemp[] = $nomBalise;
      $nomBalise = $nomBaliseTemp;
    }
    $retour = false;
    foreach($nomBalise as $nom){
        $tab = $this->getAllContexteWithName($nom);
        if(count($tab)>0){
          $retour = true;
          break;
        }
    }
    return $retour;
  }
  
  public function getNiveau(){
    return $this->contexteNumero;
  }
  public function getOrdre(){
    return $this->ordre[$this->contexteNumero-1];
  }

}

?>