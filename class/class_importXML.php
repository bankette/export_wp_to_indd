<?php
require_once("class_classJM.php");
require_once("class_contexte.php");

class ImportXML extends ClassJM{


  protected $xml_chemin;
  protected $contexte;
  protected $oChapitre;
  
  
  private $baliseHtml = array(
                    "SUP" => "sup",
                    "SUB" => "sub",
                    "INF" => "inf",
  );
  
  private $baliseAutoFermante = array(
                    "br" => "br"
  );
  
  private $baliseAIgnorer = array();
  
  private $baliseContenu = array("item"
  );
  
  private $baliseNonConsidere = array(
                            "REF_INTERNES"
  );

  function __construct($argument = -1){
    if($argument!=-1){
      $this->createNewXml($argument);
    }    
    $this->contexte = new Contexte();
  }
  
  
  public function getDateExecution(){
    return $this->xml_execution_date;
  }
  
  public function getNomFichier(){
    return basename($this->getCheminXml());
  }
  
  public function getCheminXml(){
    return $this->xml_chemin;
  }
  
  public function getNomDossierTravail(){
    return str_replace($this->getNomFichier(),'',$this->getCheminXml());
  }
  
  public function getIdPartenaire(){
    return $this->xml_idPartenaire;
  }
  
  public function getIdHachette(){
    return $this->xml_idHachette;
  }
  
  public function getNomPartenaire(){
    return $this->oPartenaire->getNom();
  }
  
  public function getDossierImages(){
    return $this->getNomDossierTravail().$this->getDossierImagesRelatif();
  }
  
  public function getDossierImagesRelatif(){
    return 'Images/';
  }
  
  public function getDossierVisuels(){
    return $this->getNomDossierTravail().'Visuels/';
  }
  
  public function getDossierPdf(){
    return $this->getNomDossierTravail().'PDF/';
  }
  
  public function setDateExecution($value){
    $this->xml_execution_date = $value;
  }
  
  public function setIdPartenaire($value){
    $this->xml_idPartenaire = $value;
  }
  
  public function setIdHachette($value){
    $this->xml_idHachette = $value;
  }
  
  public function setCheminXml($value){
    $this->xml_chemin = $value;
  }
  
  


  /***********************************
   * 
   *      GESTION XML
   * 
   ***********************************/

  
  function traiteXml(){
    // Inutile ici, mais permet d'ajouter des actions avant et apr�s l'�xecution du XML 
    // sans polluer la fonction lectureXML

    return $this->lectureXml();
  }

   protected function createNewXml($lien){
    if(is_file($lien)){
      $this->setCheminXml($lien);
    }else{
      echo "Erreur dans le lien ".$lien;
    }
  }
  
  function lectureXml(){
    $dom = new DomDocument();
    if(!$dom->load($this->getCheminXml())){
      return false;
    }
    $elements = $dom->getElementsByTagName('channel');
    $element = $elements->item(0);
    $retour =   $this->traiteBalise($element, false);
    return $retour;
  }

  private function traiteBalise($noeud, $withBaliseEntourante = true){
    // On r�cup�re le nom de la balise
    $nomBalise = $noeud->nodeName;
    $nomFonction = 'balise'.ucfirst(str_replace(":","",$nomBalise));
    //echo 'Nom balise : '.$nomBalise.'<br/>';
    //echo '<pre>';print_r(callstack());echo '</pre>';
    // On r�cup�re les attributs de la balise
    $attr = array();
    if($noeud->hasAttributes()){
      $attributes = $noeud->attributes;
      foreach ($attributes as $index => $domobj)
      {
          $attr[$domobj->name]=$domobj->value;
      }
    }
    // On ajoute le nom de cette balise au contexte
    $this->contexte->AjouterAuDessusPile($nomBalise, $attr);
    // Il faut savoir que le texte contenu dans une balise est consid�rer comme un fils
    // (c'est inclus dans la balise #text du DOM)
    // Donc on ne rentrera pas dans le if ci dessous uniquement si l'on a que du texte ou une balise auto-fermante
    if($noeud->hasChildNodes()){
        // On regarde si on a un traitement sp�cial pour cette balise
        // Pour cr�er une gestion sp�cifique il suffit de cr�er une function baliseNomDeLaBalise()
        // Attention le retour des functions sp�cifiques doit etre un tableau avec une valeur de d�but et de fin, le contenu sera g�r� avec la suite
        if (method_exists($this,$nomFonction)){
          $resultat = $this->$nomFonction($noeud);
          $accumulateur = $resultat['debut'];
          $finAccumulateur =$resultat['fin'];
          // Ceci permet aux fonctions de g�rer elle m�me leur fils.
          // A ce moment la il faut les ignorer ici cars ils ont d�ja �taient trait�s.
          if(isset($resultat['gereLesNoeudsFils']) && $resultat['gereLesNoeudsFils']){
            $ignorer = true;
          }
        }elseif(in_array($nomBalise, array_flip($this->baliseHtml))){
            // On g�re les balises html simple tel que le bold, l'ital....
            $accumulateur = '<'.$this->baliseHtml[$nomBalise].'>';
            $finAccumulateur = '</'.$this->baliseHtml[$nomBalise].'>';
        }elseif(in_array($nomBalise, $this->baliseNonConsidere)){
            // Balise qui doivent �tre ignor�e, on ne fait rien....
            $accumulateur = '';
            $finAccumulateur = '';
        }elseif(in_array($nomBalise, $this->baliseAIgnorer)){
            // Balise qui doivent �tre ignor�e, on ne fait rien....
            $accumulateur = '';
            $finAccumulateur = '';
            $ignorer = true;
        }else{
           // Sinon on cr� un div avec la class = au nom de la balise
            $accumulateur = '';
            $finAccumulateur = '';
            $ignorer = true;
        }
        $enfants_niv1 = $noeud->childNodes;
        if(!isset($ignorer) || $ignorer===false){
          foreach($enfants_niv1 as $enfant) // Pour chaque enfant, on v�rifie�
          {
            //echo '------------- Nom enfant : '.$enfant->nodeName.'<br/>';
             $accumulateur .= $this->traiteBalise($enfant);
          }
        }
        $accumulateur .= $finAccumulateur;
        $retour = $accumulateur;
      }else{
        // Ici on est dans le cas de texte simple ou de balise auto-fermante
        if (method_exists($this,$nomFonction)){
          $retourTemp = $this->$nomFonction($noeud);
          $retour = $retourTemp['debut'].$retourTemp['fin'];
        }elseif(in_array($nomBalise, $this->baliseAutoFermante)){
            $retour = '<'.$this->baliseAutoFermante[$nomBalise].'/>';
        }else{
          $retour = $this->textForXml($noeud->nodeValue);
        }
      }
      
    // On retire le nom de cette balise au contexte
    $this->contexte->retirerDernierPile();
    // Retour
    return $retour;
  }
  
  private function baliseChannel($noeud){
    $retour['debut'] = '<ASCII-WIN>'.chr(10).'<Version:6>';
    $retour['fin'] = '';
    $retour['gereLesNoeudsFils'] = false;
    //print_r($retour).'<br/>';
    return $retour;
  }
    private function baliseTitle($noeud){
        if($this->contexte->isInContexte("item")){
            $retour['debut'] = '<ParaStyle:Titre>';
            $retour['fin'] = '';
            $retour['gereLesNoeudsFils'] = false;
        }else{
            $retour['debut'] = '';
            $retour['fin'] = '';
            $retour['gereLesNoeudsFils'] = true;
        }

        //print_r($retour).'<br/>';
        return $retour;
    }

    private function baliseItem($noeud){
        $retour['debut'] = '';
        $retour['fin'] = '';
        $retour['gereLesNoeudsFils'] = false;
        //print_r($retour).'<br/>';
        return $retour;
    }

    private function balisePubDate($noeud){

        if($this->contexte->isInContexte("item")){
            $retour['debut'] = '<ParaStyle:SousTitre>Le ';
            $retour['fin'] = '';
            $retour['gereLesNoeudsFils'] = false;
        }else{
            $retour['debut'] = '';
            $retour['fin'] = '';
            $retour['gereLesNoeudsFils'] = true;
        }

        //print_r($retour).'<br/>';
        return $retour;
    }

    private function baliseDccreator($noeud){
        if($this->contexte->isInContexte("item")){
            $retour['debut'] = '<ParaStyle:SousTitre>Le ';
            $retour['fin'] = '';
            $retour['gereLesNoeudsFils'] = false;
        }else{
            $retour['debut'] = '';
            $retour['fin'] = '';
            $retour['gereLesNoeudsFils'] = true;
        }
        //print_r($retour).'<br/>';
        return $retour;
    }
    private function baliseContentencoded($noeud){
        if($this->contexte->isInContexte("item")){
            $retour['debut'] = '<ParaStyle:Texte>Le ';
            $retour['fin'] = '';
            $retour['gereLesNoeudsFils'] = false;
        }else{
            $retour['debut'] = '';
            $retour['fin'] = '';
            $retour['gereLesNoeudsFils'] = true;
        }
        //print_r($retour).'<br/>';
        return $retour;
    }


  

  
  private function getContenuBalise($noeud, $baliseEntourante = false){
    $enfants_niv1 = $noeud->childNodes;
    $acc = '';
    foreach($enfants_niv1 as $enfant) // Pour chaque enfant, on v�rifie�
    {
      $acc .= $this->traiteBalise($enfant, false);
    }
    return $acc;
  }




 
 
}
?>