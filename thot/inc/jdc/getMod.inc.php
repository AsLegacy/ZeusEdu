<?php

require_once '../../../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
session_start();

if (!(isset($_SESSION[APPLICATION]))) {
    echo "<script type='text/javascript'>document.location.replace('".BASEDIR."');</script>";
    exit;
}

$User = $_SESSION[APPLICATION];
$acronyme = $User->getAcronyme();

$module = $Application->getModule(3);

require_once INSTALL_DIR."/$module/inc/classes/classJdc.inc.php";
$Jdc = new Jdc();

$listePeriodes = $Jdc->lirePeriodesCours();
$categories = $Jdc->categoriesTravaux();

$id = isset($_POST['id']) ? $_POST['id'] : Null;

if ($id != Null) {
    if ($id != $Jdc->verifIdProprio($id, $acronyme))
        die('Cette note au JDC ne vous appartient pas');

    $travail = $Jdc->getTravail($id);
    $destinataire = $travail['destinataire'];
    $type = $travail['type'];
    // deux cas particuliers de destinataires
    switch ($type) {
        case 'cours':
            $coursGrp = $travail['destinataire'];
            $infos = $User->listeCoursProf();
            break;
        // case 'eleve':
        //     $matricule = $travail['destinataire'];
        //     require_once INSTALL_DIR.'/inc/classes/classEleve.inc.php';
        //     $infos = Eleve::staticGetDetailsEleve($matricule);
        //     break;
        // default:
        //     $infos = Null;
        //     break;
    }
    $lblDestinataire = $Jdc->getLabel($type, $destinataire, $infos);

    require_once(INSTALL_DIR."/smarty/Smarty.class.php");
    $smarty = new Smarty();
    $smarty->template_dir = "../../templates";
    $smarty->compile_dir = "../../templates_c";

    $smarty->assign('categories',$categories);
    $smarty->assign('listePeriodes', $listePeriodes);

    $smarty->assign('startDate', $travail['startDate']);
    $smarty->assign('heure', $travail['heure']);
    $smarty->assign('type', $travail['type']);
    $smarty->assign('destinataire', $travail['destinataire']);

    $smarty->assign('lblDestinataire', $lblDestinataire);

    $smarty->assign('travail',$travail);
    $smarty->display('jdc/modal/modalEdit.tpl');
    }
