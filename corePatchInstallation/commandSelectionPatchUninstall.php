$scenario->setLog('========================================');
$scenario->setLog('Désinstallation du patch Core beta');
$scenario->setLog('========================================');

$corePath = __DIR__ . '/../../';
$backupSuffix = '.corePatchInstallation.bak';

$restoreFiles = array(
	'cmd.human.insert.php' => $corePath . 'desktop/modal/cmd.human.insert.php',
	'object.ajax.php' => $corePath . 'core/ajax/object.ajax.php',
	'jeeObject.class.php' => $corePath . 'core/class/jeeObject.class.php'
);

$addedFiles = array(
	'cmd.human.insert.ajax.php' => $corePath . 'core/ajax/cmd.human.insert.ajax.php'
);

try {
	/* Restauration des fichiers modifiés */
	foreach ($restoreFiles as $name => $path) {
		$backup = $path . $backupSuffix;

		if (!file_exists($backup)) {
			$scenario->setLog('[info] Aucun backup trouvé pour ' . $name);
			continue;
		}

		if (!copy($backup, $path)) {
			throw new Exception('Impossible de restaurer ' . $name);
		}

		if (!unlink($backup)) {
			throw new Exception('Impossible de supprimer le backup de ' . $name);
		}

		$scenario->setLog('[info] ' . $name . ' restauré');
	}

	/* Suppression du fichier ajouté */
	foreach ($addedFiles as $name => $path) {
		if (!file_exists($path)) {
			$scenario->setLog('[info] ' . $name . ' déjà absent');
			continue;
		}

		if (!unlink($path)) {
			throw new Exception('Impossible de supprimer ' . $name);
		}

		$scenario->setLog('[info] ' . $name . ' supprimé');
	}

	$scenario->setLog('========================================');
	$scenario->setLog('Désinstallation du patch Core beta terminée');
	$scenario->setLog('========================================');

} catch (Exception $e) {
	$scenario->setLog('[error] ' . $e->getMessage());
	throw $e;
}