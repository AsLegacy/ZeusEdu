<?php

require_once '../config.inc.php';
include INSTALL_DIR.'/inc/entetes.inc.php';

// ----------------------------------------------------------------------------
//
require_once 'inc/classes/classPresences.inc.php';
$Presences = new Presences();
$acronyme = $user->getAcronyme();

$smarty->assign('lesCours', $user->listeCoursProf());

$etape = isset($_REQUEST['etape']) ? $_REQUEST['etape'] : null;

// les photos sont-elles visibles?
$photosVis = isset($_COOKIE['photosVis']) ? $_COOKIE['photosVis'] : null;
$smarty->assign('photosVis', $photosVis);

switch ($action) {
    case 'admin':
        include 'inc/gestAdmin.inc.php';
        break;
    case 'presences':
        include 'inc/gestPresences.inc.php';
        break;
    case 'listes':
        include 'inc/gestListes.inc.php';
        break;
    case 'signalements':
        include 'inc/signalements.inc.php';
        break;
    case 'preferences':
        include 'inc/preferences.inc.php';
        break;
    default:
        include 'inc/gestPresences.inc.php';
        break;
    }

//
// ----------------------------------------------------------------------------
$smarty->assign('INSTALL_DIR', INSTALL_DIR);
$smarty->assign('executionTime', round($chrono->stop(), 6));

$smarty->display('index.tpl');
