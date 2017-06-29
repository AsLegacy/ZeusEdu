<?php

/*
 * class Ades
 */
class Ades
{
    private $listeTypesFaits;
    private $listeChamps;
    private $typesRetenues;
    /*
     * __construct
     * @param
     */
    public function __construct()
    {
        $this->listeTypesFaits = $this->getListeTypesFaits();
        $this->listeChamps = $this->lireDescriptionChamps();
        $this->typesRetenues = $this->getTypesRetenues();
    }

    /**
     * liste des utilisateurs du module ADES.
     *
     * @param
     *
     * @return array
     */
    public function adesUsersList($module)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT '.PFX."profs.acronyme, CONCAT(prenom,' ',nom) as nomPrenom, userStatus ";
        $sql .= 'FROM '.PFX.'profs ';
        $sql .= 'JOIN '.PFX.'profsApplications ON ('.PFX.'profsApplications.acronyme = '.PFX.'profs.acronyme) ';
        $sql .= 'WHERE '.PFX."profsApplications.application = '$module' AND userStatus != 'none' ";
        $sql .= 'ORDER BY userStatus, nom, prenom ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $acronyme = $ligne['acronyme'];
                $nomPrenom = $ligne['nomPrenom'];
                $status = $ligne['userStatus'];
                $liste[$acronyme] = array('nomPrenom' => $nomPrenom, 'status' => $status);
            }
        }
        Application::deconnexionPDO($connexion);

        return $liste;
    }

     /**
      * return liste de tous les types de faits avec leur description (champs nécessaires).
      *
      * @param
      *
      * @return array
      */
    public function getListeTypesFaits()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT type, titreFait, couleurFond, couleurTexte, typeRetenue, ordre, champ ';
        $sql .= ' FROM '.PFX.'adesTypesFaits AS adtf ';
        $sql .= 'JOIN '.PFX.'adesChampsFaits AS adcf ON adtf.type = adcf.typeFait ';
        $sql .= 'ORDER BY ordre ';

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $type = $ligne['type'];
                $champ = $ligne['champ'];
                if (!isset($liste[$type])) {
                    unset($ligne['champ']);
                    $liste[$type] = $ligne;
                }
                $liste[$type]['listeChamps'][] = $champ;
            }
        }
        Application::deconnexionPDO($connexion);

        return $liste;
    }

     /**
      * renvoie la liste des types de faits inutilisés et qui peuvent donc éventuellement être supprimés
      * de la base de données
      *
      * @param void
      *
      * @return array
      */
     public function getTypesFaitsInutilises(){
         // liste des identifiants des types de faits
         $listeTypes = array_keys($this->getListeTypesFaits());
         // établir la liste des faits réellement utilisés, classés par identifiant du type
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         $sql = 'SELECT DISTINCT type ';
         $sql .= 'FROM '.PFX.'adesFaits ';
         $requete = $connexion->prepare($sql);
         $resultat = $requete->execute();
         $listeUtilises = array();
         if ($resultat) {
             $requete->setFetchMode(PDO::FETCH_ASSOC);
             while ($ligne = $requete->fetch()) {
                 $type = $ligne['type'];
                 $listeUtilises[$type] = $ligne['type'];
             }
         }

         Application::deconnexionPDO($connexion);

         $listeInutiles = array_diff($listeTypes, $listeUtilises);
         return $listeInutiles;

     }

     /**
      * suppression d'un type de fait de la table des types de faits
      *
      * @param $type : l'identifiant du type de faits
      *
      * @return int : nombre d'effacement (0 ou 1)
      */
     public function delTypeFaits($type){
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         $sql = 'DELETE FROM '.PFX.'adesTypesFaits ';
         $sql .= 'WHERE type=:type ';
         $requete = $connexion->prepare($sql);
         $requete->bindParam(':type', $type, PDO::PARAM_INT);
         $resultat = $requete->execute();

         // suppression des mentions dans la table des relations faits => champs
         $sql = 'DELETE FROM '.PFX.'adesChampsFaits ';
         $sql .= 'WHERE typeFait=:typeFait ';
         $requete = $connexion->prepare($sql);
         $requete->bindParam(':typeFait', $type, PDO::PARAM_INT);
         $resultat = $requete->execute();

         Application::deconnexionPDO($connexion);

         return $resultat;
     }

     /**
      * Enregistre les informations sur un type de fait à partir du formulaire d'édition
      *
      * @param array $form (formulaire templates/fait/formEditTypeFAit.tpl)
      *
      * @return array
      */
     public function saveTypeFait($form) {
         $nb = $this->saveDataFait($form);
         $nb = $this->saveChampsFait($form);

         return $form;
     }

     /**
      * Enregistre les informations sur un **nouveau** type de fait à partir du formulaire d'édition
      *
      * @param array $form (formulaire templates/fait/formEditTypeFAit.tpl)
      *
      * @return array $form (le formulaire en entrée)
      */
     public function saveNewTypeFait($form){
         $typeRetenue = ($form['typeRetenue'] == -1) ? 1: 0;
         if ($typeRetenue == 1)
            $typeRetenue = $this->getNextTypeRetenue();
            else $typeRetenue = 0;
        $type = $this->getNextTypeFait();
        $ordre = $this->getNextOrdreTypeFait();

        $form['typeRetenue'] = $typeRetenue;
        $form['type'] = $type;
        $form['ordre'] = $ordre;

        return $this->saveTypeFait($form);
     }

     /**
      * Enregistre les informations "texte" sur un type de fait à partir du formulaire d'édition
      *
      * @param array : $form (formulaire templates/fait/formEditTypeFAit.tpl)
      *
      * @return int : nombre d'enregistrements (0 ou 1)
      */
    public function saveDataFait ($form) {
        $type = $form['type'];
        $titreFait = $form['titreFait'];
        $color = $form['color'];
        $background = $form['background'];
        $ordre = $form['ordre'];
        $typeRetenue = $form['typeRetenue'];

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'adesTypesFaits ';
        $sql .= 'SET type=:type, titreFait=:titreFait, couleurFond=:background, couleurTexte=:color, ';
        $sql .= 'typeRetenue=:typeRetenue, ordre=:ordre ';
        $sql .= 'ON DUPLICATE KEY UPDATE ';
        $sql .= 'titreFait=:titreFait, couleurFond=:background, couleurTexte=:color, ';
        $sql .= 'typeRetenue=:typeRetenue, ordre=:ordre ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':type', $type, PDO::PARAM_INT);
        $requete->bindParam(':titreFait', $titreFait, PDO::PARAM_STR, 25);
        $requete->bindParam(':background', $background, PDO::PARAM_STR, 7);
        $requete->bindParam(':color', $color, PDO::PARAM_STR, 7);
        $requete->bindParam(':typeRetenue', $typeRetenue, PDO::PARAM_INT);
        $requete->bindParam(':ordre', $ordre, PDO::PARAM_INT);

       $resultat = $requete->execute();

        Application::deconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * Enregistre les différents champs associés à un fait disciplinaire à partir du formulaire d'édition
     *
     * @param array : $form (formulaire templates/fait/formEditTypeFAit.tpl)
     *
     * @return int : nombre d'enregistrements
     */
    public function saveChampsFait($form) {
        $typeFait = $form['type'];
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT IGNORE INTO '.PFX.'adesChampsFaits ';
        $sql .= 'SET champ=:champ, typeFait=:typeFait ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':typeFait', $typeFait, PDO::PARAM_INT);
        $listeChamps = $form['champs'];

        $resultat = 0;
        foreach ($listeChamps as $wtf => $unChamp) {
            $requete->bindParam(':champ', $unChamp, PDO::PARAM_STR);
            $resultat += $requete->execute();
        }

        $listeChampsString = "'".implode("','", $listeChamps)."'";
        $sql = 'DELETE FROM '.PFX.'adesChampsFaits ';
        $sql .= 'WHERE typeFait=:typeFait AND champ NOT IN ('.$listeChampsString.') ';

        $requete = $connexion->prepare($sql);
        $requete->bindParam(':typeFait', $typeFait, PDO::PARAM_INT);

        $resultat -= $requete->execute();

        Application::deconnexionPDO($connexion);

        return $resultat;
    }


    /**
     * renvoie la liste des types de faits existants.
     *
     * @param
     *
     * @return array
     */
    public function getTypesFaits()
    {
        return $this->listeTypesFaits;
    }
    /**
     * retrouve les différents types de retenues disponibles depuis la liste des types de faits.
     *
     * @param
     *
     * @return array
     */
    public function getTypesRetenues()
    {
        $typesFaits = $this->listeTypesFaits();
        $liste = array();
        $liste2 = array();
        foreach ($typesFaits as $type => $data) {
            $typeRetenue = $data['typeRetenue'];
            if ($typeRetenue != '0') {
                $liste[$typeRetenue] = $data;
            }
        }

        return $liste;
    }

    /**
     * Lecture de la description des champs dans la BD.
     *
     * @param
     *
     * @return array
     */
    public function lireDescriptionChamps()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT * FROM '.PFX.'adesChamps ';
        $sql .= 'ORDER BY champ ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $champ = $ligne['champ'];
                $ligne['contextes'] = explode(',', $ligne['contextes']);
                $liste[$champ] = $ligne;
            }
        }
        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie la liste des types de faits de l'objet.
     *
     * @param
     *
     * @return array;
     */
    public function listeTypesFaits()
    {
        return $this->listeTypesFaits;
    }

    /**
     * renvoie la description des champs de la BD.
     *
     * @param
     *
     * @return array
     */
    public function listeChamps()
    {
        return $this->listeChamps;
    }

    /**
     * renvoie la description des différents types de retenues existantes.
     *
     * @param
     *
     * @return array
     */
    public function listeTypesRetenues()
    {
        return $this->$typesRetenues;
    }

    /**
     * renvoie la liste des champs pour décrire un fait disciplinaire.
     *
     * @param int $type
     *
     * @return array
     */
    public function getFaitByType($type)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT type, titreFait, couleurFond, couleurTexte, typeRetenue, ordre, champ ';
        $sql .= 'FROM '.PFX.'adesTypesFaits AS adtf ';
        $sql .= 'JOIN '.PFX.'adesChampsFaits AS adcf ON adtf.type = adcf.typeFait ';
        $sql .= 'WHERE type=:type ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':type', $type, PDO::PARAM_INT);
        $resultat = $requete->execute();

        $tableauFait = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $champ = $ligne['champ'];
                if (!isset($tableauFait['listeChamps'])) {
                    unset($ligne['champ']);
                    $tableauFait = $ligne;
                }
                $tableauFait['listeChamps'][] = $champ;
            }
        }
        Application::deconnexionPDO($connexion);

        return $tableauFait;
    }

    /**
     * Création d'un nouveau type de fait disciplinaire à partir de rien
     *
     * @param bool $retenue : s'agit-il d'une retenue?
     *
     * @return array
     */
    public function createNewTypeFait ($retenue) {
        $newFait = array(
            'type' => Null,
            'titreFait' => '',
            'couleurFond' => Null,
            'couleurTexte' => Null,
            // ce sera une retenue?
            'typeRetenue' => ($retenue == 1) ? -1 : 0,
            'ordre' => Null,
            'listeChamps' => array_keys($this->getChampsObligatoires($retenue))
        );

        return $newFait;
    }

    /**
     * retourne la fait disciplinaire dont on donne l'ordre d'apparition dans la fiche
     *
     * @param int $ordre
     *
     * @return array
     */
    public function getFaitByOrdre($ordre){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT type, titreFait, couleurFond, couleurTexte, typeRetenue, ordre, champ ';
        $sql .= 'FROM '.PFX.'adesTypesFaits AS adtf ';
        $sql .= 'JOIN '.PFX.'adesChampsFaits AS adcf ON adtf.type = adcf.typeFait ';
        $sql .= 'WHERE ordre=:ordre ';
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':ordre', $ordre, PDO::PARAM_INT);
        $resultat = $requete->execute();

        $tableauFait = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $champ = $ligne['champ'];
                if (!isset($tableauFait['listeChamps'])) {
                    unset($ligne['champ']);
                    $tableauFait = $ligne;
                }
                $tableauFait['listeChamps'][] = $champ;
            }
        }
        Application::deconnexionPDO($connexion);

        return $tableauFait;
    }

    /**
     * échange l'ordre de deux types de faits disciplinaires fournis en paramètres
     *
     * @param array $fait1
     * @param array $fait2
     *
     * @return void
     */
    public function echanger($fait1, $fait2) {
        $ordre1 = $fait1['ordre']; $type1 = $fait1['type'];
        $ordre2 = $fait2['ordre']; $type2 = $fait2['type'];

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'UPDATE '.PFX.'adesTypesFaits ';
        $sql .= 'SET ordre=:ordre ';
        $sql .= 'WHERE type=:type ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':ordre', $ordre1, PDO::PARAM_INT);
        $requete->bindParam(':type', $type2, PDO::PARAM_INT);
        $resultat = $requete->execute();

        $requete->bindParam(':ordre', $ordre2, PDO::PARAM_INT);
        $requete->bindParam(':type', $type1, PDO::PARAM_INT);
        $resultat += $requete->execute();

        Application::deconnexionPDO($connexion);

        return $resultat;
    }


    /**
     * réindexe l'ordre des types de faits pour obtenir un pas de 1 entre les items
     * nécessaire pour éviter les trous après effacement d'un fait disciplinaire
     *
     * @param void
     *
     * @result void
     */
    public function reIndexTypesFAits() {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT * FROM '.PFX.'adesTypesFaits ';
        $sql .= 'ORDER BY ordre ';
        $requete = $connexion->prepare($sql);
        $resultat = $requete->execute();
        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $liste = $requete->fetchall();
        }
        $sql = 'UPDATE '.PFX.'adesTypesFaits ';
        $sql .= 'SET ordre=:ordre WHERE type=:type ';
        $requete = $connexion->prepare($sql);
        
        foreach ($liste as $n => $dataTypeFait) {
            $type = $dataTypeFait['type'];
            $requete->bindParam(':ordre', $n, PDO::PARAM_INT);
            $requete->bindParam(':type', $type, PDO::PARAM_INT);
            $resultat = $requete->execute();
            $n++;
        }

        Application::DeconnexionPDO($connexion);
    }

    /**
     * renvoie la structure nécessaire à l'établissment du formulaire d'édition d'un fait disciplinaire
     * sans les données.
     *
     * @param int $type
     *
     * @return array
     */
    public function prototypeFait($type)
    {
        if (!isset($type)) {
            die('no type');
        }
        $structure = $this->getFaitByType($type);
        $listeChamps = "'".implode("','", $structure['listeChamps'])."'";

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT champ, label, contextes, typeDate, typeChamp, size, maxlength, colonnes,lignes, classCSS ';
        $sql .= 'FROM '.PFX.'adesChamps ';
        $sql .= "WHERE champ IN ($listeChamps) ";

        $resultat = $connexion->query($sql);
        $prototype = array('structure' => $structure, 'champs' => array());
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $champ = $ligne['champ'];
                $prototype['champs'][$champ] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $prototype;
    }

    /**
     * retourne la liste des champs obligatoires pour décrire un fait
     * si $retenue = true => on ajoute les champs obligatoires pour la retenue
     *
     * @param int $retenue (le type de retenue ou 0 si pas une retenue)
     *
     * @return array
     */
    public function getChampsObligatoires ($retenue){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT champ, label, typeChamp ';
        $sql .= 'FROM '.PFX.'adesChamps ';
        $sql .= 'WHERE obligatoire = 1 ';
        if ($retenue > 0)
            $sql .= 'OR retenue = 1 ';

        $requete = $connexion->prepare($sql);
        $resultat = $requete->execute();
        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $champ = $ligne['champ'];
                $liste[$champ] = $ligne;
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne la liste des champs disponibles pour la description d'un fait
     * si $retenue = 0, alors on ne sélectionne pas les champs "retenue"
     */
    public function getListeChamps($retenue){
        $listeChamps = $this->listeChamps();
        if ($retenue > 0)
            return $listeChamps;
            else {
                foreach ($listeChamps as $champ => $dataChamp) {
                    if ($dataChamp['retenue'] == 1)
                        unset($listeChamps[$champ]);
                }
                return $listeChamps;
            }
        }

    /**
     * retourne la plus haute valeur de l'ordre d'un type de fait dans la fiche disciplinaire
     *
     * @param void
     *
     * @return int
     */
    public function getNextOrdreTypeFait() {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT MAX(ordre) AS max FROM '.PFX.'adesTypesFaits ';
        $requete = $connexion->prepare($sql);
        $resultat = $requete->execute();
        $max = null;
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
            $max = $ligne['max'] + 1;
        }

        Application::DeconnexionPDO($connexion);

        return $max;
    }

    /**
     * retourne le premier identifiant "typeFait" possible pour un nouveau fait disciplinaire
     *
     * @param void
     *
     * @return int
     */
    public function getNextTypeFait () {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT MAX(type) AS max FROM '.PFX.'adesTypesFaits ';
        $requete = $connexion->prepare($sql);
        $resultat = $requete->execute();
        $max = null;
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
            $max = $ligne['max'] + 1;
        }

        Application::DeconnexionPDO($connexion);

        return $max;
    }

    /**
     * retourne le premier identifiant "typeRetenue" possible pour un nouveau type de retenue
     *
     * @param void
     *
     * @return int
     */
    public function getNextTypeRetenue () {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT MAX(typeRetenue) AS max FROM '.PFX.'adesTypesFaits ';
        $requete = $connexion->prepare($sql);
        $resultat = $requete->execute();
        $max = null;
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
            $max = $ligne['max'] + 1;
        }

        Application::DeconnexionPDO($connexion);

        return $max;
    }

    /**
     * retourne la liste des retenues disponibles du type spécifié.
     *
     * @param int $type
     * @param $affiche : seulement les retenues marquées "à afficher"
     * @param $anneeEnCours : seulement les retenues du duo d'années civiles en cours
     *
     * @return array
     */
    public function listeRetenues($typeRetenue, $affiche = true, $anneeEnCours = true)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT type, idretenue, dateRetenue, heure, duree, local, places, affiche ';
        $sql .= 'FROM '.PFX.'adesRetenues ';
        $sql .= "WHERE type='$typeRetenue' ";
        if ($affiche == true) {
            $sql .= "AND affiche = 'O' ";
        }
        if ($anneeEnCours == true) {
            $annees = explode('-', ANNEESCOLAIRE);
            $sql .= "AND (substr(dateRetenue,1,4) = $annees[0] OR substr(dateRetenue,1,4) = $annees[1]) ";
        }
        $sql .= 'ORDER BY dateRetenue, heure ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $idretenue = $ligne['idretenue'];
                $ligne['jourSemaine'] = Application::jourSemaineMySQL($ligne['dateRetenue']);
                $ligne['occupation'] = 0;
                $liste[$idretenue] = $ligne;
            }
        }
        $listeIdRetenue = implode(',', array_keys($liste));

        $sql = 'SELECT idretenue, COUNT(*) as occupation ';
        $sql .= 'FROM '.PFX.'adesFaits ';
        $sql .= "WHERE idretenue IN ($listeIdRetenue) ";
        $sql .= 'GROUP BY idretenue ';
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $listeOccupation = $resultat->fetchall();
            foreach ($listeOccupation as $wtf => $data) {
                $idretenue = $data['idretenue'];
                $liste[$idretenue]['occupation'] = $data['occupation'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne les caractéristiques de la retenue $idretenue: date, heure, durée, local.
     *
     * @param $idretenue
     *
     * @return array : caractéristiques de la retenue
     */
    public function detailsRetenue($idretenue)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT type, dateRetenue, heure, duree, local, places ';
        $sql .= 'FROM '.PFX.'adesRetenues ';
        $sql .= "WHERE idretenue = '$idretenue' ";
        $resultat = $connexion->query($sql);
        $retenue = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $retenue = $resultat->fetch();
            // $retenue['dateRetenue'] = Application::datePHP($retenue['dateRetenue']);
        }
        Application::DeconnexionPDO($connexion);

        return $retenue;
    }

    /**
     * renvoie la liste des idRetenues pour l'élève dont on fournit le matricule.
     *
     * @param int $matricule
     *
     * @return array
     */
    public function getListeRetenues($matricule)
    {
        // recherche de toutes les retenues dans la table des faits disciplinaires
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idretenue ';
        $sql .= 'FROM '.PFX.'adesFaits ';
        $sql .= "WHERE matricule = '$matricule' AND idretenue != '' ";
        $sql .= 'ORDER BY ladate ';
        $resultat = $connexion->query($sql);
        $listeRetenues = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $idretenue = $ligne['idretenue'];
                $listeRetenues[] = $idretenue;
            }
        }

        // recherche des détails pratiques concernant ces retenues dans la table des retenues
        $listeRetenuesString = implode(',', $listeRetenues);
        $sql = 'SELECT type, idretenue, dateRetenue, heure, duree, local ';
        $sql .= 'FROM '.PFX.'adesRetenues ';
        $sql .= "WHERE idRetenue IN ($listeRetenuesString) ";
        $sql .= 'ORDER BY type, dateRetenue, heure ';

        $resultat = $connexion->query($sql);
        $listeRetenues = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $idretenue = $ligne['idretenue'];
                $ligne['dateRetenue'] = Application::datePHP($ligne['dateRetenue']);
                $listeRetenues[$idretenue] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeRetenues;
    }

    /**
     * enregistre un élément de la banque de textes de ADES.
     *
     * @param int    $idTexte : id éventuel du texte dans la table
     * @param string $user    : propriétaire éventuel du texte
     * @param bool   $free    : texte partage (1) ou pas (0)
     * @param string $texte   : texte à enregistrer
     */
    public function saveTexte($idTexte, $user, $free, $texte, $champ)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'adesTextes ';
        $sql .= "SET idTexte = '$idTexte', user='$user', free='$free', texte='$texte', champ='$champ' ";
        $sql .= "ON DUPLICATE KEY UPDATE user='$user', free='$free', texte='$texte', champ='$champ' ";
        $resultat = $connexion->exec($sql);
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * suppression d'un texte de la banque de textes de ADES.
     *
     * @param $id
     *
     * @return $nb : nombre de modifications dans la BD (normalement 1)
     */
    public function delTexte($id)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'adesTextes ';
        $sql .= "WHERE idTexte='$id' ";
        $resultat = $connexion->exec($sql);
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * retourne la liste des mémos de l'utilisateur et des mémos libres.
     *
     * @param string $acronyme : abréviation de l'utilisateur
     *
     * @return array
     */
    public function listeMemos($acronyme)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idTexte, user, champ, free, texte ';
        $sql .= 'FROM '.PFX.'adesTextes ';
        $sql .= "WHERE user = '$acronyme' OR free='1' ";
        $sql .= 'ORDER BY champ, texte ';

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $champ = $ligne['champ'];
                $id = $ligne['idTexte'];
                $liste[$champ][$id] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * liste des élèves inscrits à une retenue dont on indique l'identifiant.
     *
     * @param $idretenue
     *
     * @return array
     */
    public function listeElevesRetenue($idretenue)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT travail, materiel, nom, prenom, groupe, professeur,'.PFX.'adesFaits.matricule ';
        $sql .= 'FROM '.PFX.'adesFaits ';
        $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'adesFaits.matricule) ';
        $sql .= "WHERE idretenue = '$idretenue' ";
        $sql .= 'ORDER BY nom, prenom, groupe ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $ligne['photo'] = Ecole::photo($matricule);
                $liste[$matricule] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * recherche les caractéristiques d'un fait disciplinaire dont on fournit le $idfait.
     *
     * @param $idfait
     *
     * @return array
     */
    public function infosFait($idfait)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT '.PFX.'adesFaits.type, matricule, ladate, motif, professeur, idretenue, travail, materiel, ';
        $sql .= 'sanction, nopv, qui, typeRetenue, titreFait ';
        $sql .= 'FROM '.PFX.'adesFaits ';
        $sql .= 'JOIN '.PFX.'adesTypesFaits	ON ('.PFX.'adesTypesFaits.type = '.PFX.'adesFaits.type) ';
        $sql .= 'WHERE idfait =:idfait ';

        $requete = $connexion->prepare($sql);
        $requete->bindParam(':idfait', $idfait, PDO::PARAM_INT);

        $resultat = $requete->execute();
        $infoFaits = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $infosFait = $requete->fetchAll();
        }
        Application::DeconnexionPDO($connexion);

        return $infosFait[0];
    }

    /**
     * caractéristiques de la retenue dont on fournit le idretenue.
     *
     * @param $idretenue
     *
     * @return array
     */
    public function infosRetenue($idretenue)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT '.PFX.'adesRetenues.type, idretenue, dateRetenue, heure, duree, local, places, titrefait, ';
        $sql .= '(SELECT COUNT(*) FROM '.PFX.'adesFaits ';
        $sql .= "WHERE idretenue='$idretenue') AS occupation ";
        $sql .= 'FROM '.PFX.'adesRetenues ';
        $sql .= 'JOIN '.PFX.'adesTypesFaits ON ('.PFX.'adesTypesFaits.typeRetenue = '.PFX.'adesRetenues.type) ';
        $sql .= "WHERE idretenue = '$idretenue' ";

        $resultat = $connexion->query($sql);
        $info = null;
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $infos = $resultat->fetch();
            $infos['jourSemaine'] = Application::jourSemaineMySQL($infos['dateRetenue']);
            $infos['dateRetenue'] = Application::datePHP($infos['dateRetenue']);
        }
        Application::DeconnexionPDO($connexion);

        return $infos;
    }

    /**
     * caractéristiques d'une retenue dont on fournit le type
     * informations venant de adesTypesFaits.
     *
     * @param $type : le type de fait disciplinaire
     *
     * @return array
     */
    // public function infosRetenueType($type)
    // {
    //     $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
    //     $sql = 'SELECT type, titreFait, couleurFond, couleurTexte, typeRetenue, ordre, listeChamps ';
    //     $sql .= 'FROM '.PFX.'adesTypesFaits ';
    //     $sql .= "WHERE typeRetenue = '$type' ";
    //     $info = null;
    //     $resultat = $connexion->query($sql);
    //     if ($resultat) {
    //         $resultat->setFetchMode(PDO::FETCH_ASSOC);
    //         $infos = $resultat->fetch();
    //     }
    //     Application::DeconnexionPDO($connexion);
    //
    //     return $infos;
    // }
    public function infosRetenueType($type)
        {
            $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'SELECT type, titreFait, couleurFond, couleurTexte, typeRetenue, ordre ';
            $sql .= 'FROM '.PFX.'adesTypesFaits ';
            $sql .= 'WHERE typeRetenue =:type ';
            $requete = $connexion->prepare($sql);

            $requete->bindParam(':type', $type, PDO::PARAM_INT);
            $info = array();
            $resultat = $requete->execute();
            if ($resultat) {
                $requete->setFetchMode(PDO::FETCH_ASSOC);
                $infos = $requete->fetch();
            }
            Application::DeconnexionPDO($connexion);

            return $infos;
        }

    public function utf8($argument)
    {
        return utf8_decode($argument);
    }

    /**
     * renvoie la liste des champs qui doivent apparaître dans un "contexte" donné pour chaque élément d'un fait disciplinaire.
     *
     * @param $contexte : string
     *
     * @return array
     */
    // public function champsInContexte($contexte)
    // {
    //     $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
    //     // recherche la liste de tous les types de faits existants avec les champs qui les décrivent
    //     $sql = 'SELECT type, listeChamps ';
    //     $sql .= 'FROM '.PFX.'adesTypesFaits ';
    //     $resultat = $connexion->query($sql);
    //     $listeFaits = array();
    //     if ($resultat) {
    //         while ($ligne = $resultat->fetch()) {
    //             $type = $ligne['type'];
    //             $ligne['listeChamps'] = str_replace(' ', '', $ligne['listeChamps']);
    //             $listeFaits[$type] = explode(',', $ligne['listeChamps']);
    //         }
    //     }
    //     // recherche de tous les champs à exposer dans le contexte d'apparition
    //     $sql = 'SELECT champ ';
    //     $sql .= 'FROM '.PFX.'adesChamps ';
    //     $sql .= "WHERE LOCATE('$contexte', contextes) > 0 ";
    //     $resultat = $connexion->query($sql);
    //     $listeChamps = array();
    //     if ($resultat) {
    //         while ($ligne = $resultat->fetch()) {
    //             $listeChamps[] = $ligne['champ'];
    //         }
    //     }
    //     // parcours de la liste des faits existants et de leur description
    //     foreach ($listeFaits as $type => $lesChamps) {
    //         // pour chaque fait, on ne conserve que les champs qui figurent dans la "liste des champs à exposer"
    //         $listeFaits[$type] = array_intersect($listeFaits[$type], $listeChamps);
    //     }
    //     Application::DeconnexionPDO($connexion);
    //
    //     return $listeFaits;
    // }
    public function champsInContexte($contexte)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        // recherche la liste de tous les types de faits existants avec les champs qui les décrivent
        $sql = 'SELECT type, champ ';
        $sql .= 'FROM '.PFX.'adesTypesFaits AS adtf ';
        $sql .= 'JOIN '.PFX.'adesChampsFaits AS adcf ON adtf.type = adcf.typeFait ';
        $requete = $connexion->prepare($sql);

        $resultat = $requete->execute();
        $listeFaits = array();
        if ($resultat) {
            while ($ligne = $requete->fetch()) {
                $type = $ligne['type'];
                $listeFaits[$type][] = $ligne['champ'];
            }
        }
        // recherche de tous les champs à exposer dans le contexte d'apparition
        $sql = 'SELECT champ ';
        $sql .= 'FROM '.PFX.'adesChamps ';
        $sql .= "WHERE LOCATE('$contexte', contextes) > 0 ";
        $resultat = $connexion->query($sql);
        $listeChamps = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $listeChamps[] = $ligne['champ'];
            }
        }
        // parcours de la liste des faits existants et de leur description
        foreach ($listeFaits as $type => $lesChamps) {
            // pour chaque fait, on ne conserve que les champs qui figurent dans la "liste des champs à exposer"
            $listeFaits[$type] = array_intersect($listeFaits[$type], $listeChamps);
        }
        Application::DeconnexionPDO($connexion);

        return $listeFaits;
    }
    /**
     * renvoie une table de correspondance entre les noms et les titres des champs à exposer.
     *
     * @param
     *
     * @return array
     */
    public function titreChamps()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT champ, label ';
        $sql .= 'FROM '.PFX.'adesChamps ';
        $resultat = $connexion->query($sql);
        $listeChamps = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $champ = $ligne['champ'];
                $listeChamps[$champ] = $ligne['label'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeChamps;
    }

    /**
     * renvoie les statistiques sur les différents types de faits entre les dates précisées
     * pour les élèves demandés (la liste des élèves est fabriquée ailleurs).
     *
     * @param $listeEleves : liste des élèves concernés
     * @param $debut : date de début
     * @param $fin : date de fin
     *
     * @return array
     */
    public function statistiques($listeEleves, $debut, $fin)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $debut = Application::dateMysql($debut);
        $fin = Application::dateMysql($fin);
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT COUNT(*) AS nbFaits, af.type, titreFait, couleurFond, couleurTexte ';
        $sql .= 'FROM '.PFX.'adesFaits AS af ';
        $sql .= 'JOIN '.PFX.'adesTypesFaits AS tf ON (tf.type = af.type) ';
        $sql .= "WHERE matricule IN ($listeElevesString) AND af.ladate > '$debut' AND af.ladate < '$fin' ";
        $sql .= 'GROUP BY type ORDER BY type';
        $resultat = $connexion->query($sql);
        $statistiques = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $type = $ligne['type'];
                $statistiques[$type] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $statistiques;
    }

    /**
     * renvoie Les fiches disciplinaires de la liste d'élèves indiquée avec uniquement les champs demandés.
     *
     * @param $listeEleves : liste des élèves concernés
     * @param $debut : date de début
     * @param $fin : date de fin
     *
     * @return array
     */
    public function fichesDisciplinaires($listeEleves, $debut, $fin)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $debut = Application::dateMysql($debut);
        $fin = Application::dateMysql($fin);

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT af.idFait, af.type, af.*, titreFait, ae.groupe AS classe, ';
        $sql .= 'dateRetenue, local, heure, duree, ap.nom, ap.prenom, ap.sexe ';
        $sql .= 'FROM '.PFX.'adesFaits AS af ';
        $sql .= 'JOIN '.PFX.'eleves AS ae ON (ae.matricule = af.matricule ) ';
        $sql .= 'JOIN '.PFX.'adesTypesFaits AS atf ON (atf.type = af.type) ';
        $sql .= 'LEFT JOIN '.PFX.'adesRetenues AS ar ON (ar.idretenue = af.idretenue) ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS ap ON (ap.acronyme = af.professeur) ';
        $sql .= "WHERE af.matricule IN ($listeElevesString) AND af.ladate >= '$debut' AND af.ladate <= '$fin' ";
        $sql .= "ORDER BY classe, REPLACE(REPLACE(REPLACE(ae.nom, ' ', ''),'''',''),'-',''), ae.prenom, type, ladate ";

        $resultat = $connexion->query($sql);
        $listeFiches = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $ligne['ladate'] = Application::datePHP($ligne['ladate']);
                if ($ligne['nom'] != null) {
                    if ($ligne['sexe'] == 'M') {
                        $ligne['professeur'] = 'M. '.$ligne['nom'];
                    } else {
                        $ligne['professeur'] = 'Mme '.$ligne['nom'];
                    }
                }
                if (isset($ligne['dateRetenue'])) {
                    $ligne['dateRetenue'] = Application::datePHP($ligne['dateRetenue']);
                }
                $classe = $ligne['classe'];
                $matricule = $ligne['matricule'];
                $type = $ligne['type'];
                $idFait = $ligne['idFait'];
                $listeFiches[$classe][$matricule][$type][$idFait] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeFiches;
    }

    /**
     * inverse le mode d'affichage des retenues dans leur liste; une retenue affichée devient cachée et inversement
     * renvoie un string indiquant le mode d'affichage ("O" ou "N").
     *
     * @param $idRetenue
     *
     * @return
     */
    public function toggleAffichageRetenue($idRetenue, $visible)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'UPDATE '.PFX.'adesRetenues ';
        $sql .= "SET affiche = '$visible' ";
        $sql .= "WHERE idRetenue = '$idRetenue' ";
        $resultat = $connexion->exec($sql);
        Application::DeconnexionPDO($connexion);

        return $visible;
    }

    /**
     * compile au format smarty un modèle de fichier contenant des ##MOTS## en majuscules.
     *
     * @param $string : la chaîne entrée
     *
     * @return string
     */
    public function compileTemplate($string, $dicoZeus, $dicoAdes)
    {
        $nb = preg_match_all('/(##[A-Z:a-z]*##)/', $string, $matches);
        foreach ($matches[1] as $wtf => $input) {
            if (in_array($input, $dicoZeus)) {
                $output = '{$'.substr($input, 2, -2).'}';
                $string = str_replace($input, $output, $string);
            }
            if (in_array($input, $dicoAdes)) {
                $output = '{$'.substr($input, 2, -2).'}';
                $string = str_replace($input, $output, $string);
            }
        }

        return $string;
    }

    /**
     * retourne la liste des envois par mail aux parents pour l'élève dont on fournit le matricule.
     *
     * @param $matricule
     *
     * @return array
     */
    public function sentMails($matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, idfait, date, destinataire ';
        $sql .= 'FROM '.PFX.'adesSent ';
        $sql .= "WHERE matricule='$matricule' ";
        $sql .= 'ORDER BY date ASC ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $liste[] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne la liste des envois par idFait pour un élève dont on fournit le matricule.
     *
     * @param $matricule
     *
     * @return array
     */
    public function sentByIdFait($matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idfait, date, destinataire, de.nomResp, dep.nomPere, dem.nomMere ';
        $sql .= 'FROM '.PFX.'adesSent AS sent ';
        $sql .= 'LEFT JOIN '.PFX.'eleves AS de ON de.courriel = sent.destinataire ';
        $sql .= 'LEFT JOIN '.PFX.'eleves AS dep ON dep.mailPere = sent.destinataire ';
        $sql .= 'LEFT JOIN '.PFX.'eleves AS dem ON dem.mailMere = sent.destinataire ';
        $sql .= "WHERE de.matricule='$matricule' ";
        $sql .= 'ORDER BY date ASC ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $idFait = $ligne['idFait'];
                $liste[$idFait][] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne un tableau avec la largeur des champs pour l'impression tenant compte de la largeur de la PAGE.
     *
     * @param $pageWidth
     * @param $descriptionChamps
     *
     * @return array
     */
    public function fieldWidth($PAGEWIDTH, $listeTypesFaits, $descriptionChamps)
    {
        $width = array();
        // recension des tailles d'impression des champs
        foreach ($listeTypesFaits as $idTypeFait => $dataTypeFait) {
            // $type = $dataTypeFait['titreFait'];
            $listeChamps = $dataTypeFait['listeChamps'];
            foreach ($listeChamps as $fieldId => $unChamp) {
                if (in_array('tableau', $descriptionChamps[$unChamp]['contextes'])) {
                    $width[$idTypeFait][$unChamp] = $descriptionChamps[$unChamp]['printWidth'];
                }
            }
        }
        // calcul de la longueur totale pour chaque fait
        $totalWidth = array();
        foreach ($width as $idTypeFait => $longueurs) {
            $totalWidth[$idTypeFait] = array_sum($longueurs);
        }
        // calculs de la taille de chaque champ pour chaque type de fait
        $echelles = array();
        foreach ($listeTypesFaits as $idType => $dataTypeFait) {
            $listeChamps = $dataTypeFait['listeChamps'];
            foreach ($listeChamps as $fieldId => $unChamp) {
                if (in_array('tableau', $descriptionChamps[$unChamp]['contextes'])) {
                    $original = $descriptionChamps[$unChamp]['printWidth'];
                    $echelles[$idType][$unChamp] = round($descriptionChamps[$unChamp]['printWidth'] * $PAGEWIDTH / $totalWidth[$idType]);
                }
            }
        }

        return $echelles;
    }
}
