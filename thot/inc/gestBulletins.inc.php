<?php
if ($etape == 'enregistrer') {
	$nb = $Thot->saveLimiteBulletins($_POST);
	$smarty->assign('message', array(
				'title'=>SAVE,
				'texte'=>"$nb enregistrement(s) effectué(s)",
				'urgence'=>'success')
				);
	}

$smarty->assign('classe',$classe);
$listeEleves = $Ecole->listeEleves($classe,'groupe');
$smarty->assign('listeEleves',$listeEleves);
$smarty->assign('listeClasses',$Ecole->listeGroupes());
$smarty->assign('selecteur','selectClasse');

if ($classe != Null) {
		$smarty->assign('listeBulletinsEleves',$Thot->listeBulletinsEleves($classe));
		$smarty->assign('NBPERIODES',NBPERIODES);
		$smarty->assign('listeBulletins',range(0,NBPERIODES));
		$smarty->assign('PERIODEENCOURS',PERIODEENCOURS);
		$smarty->assign('corpsPage','gestBulletins');
	}
	else $smarty->assign('corpsPages',Null);

?>