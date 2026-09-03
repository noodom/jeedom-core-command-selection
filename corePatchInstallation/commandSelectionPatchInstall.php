$scenario->setLog('========================================');
$scenario->setLog('Installation du patch Core beta');
$scenario->setLog('========================================');

$baseUrl = 'https://raw.githubusercontent.com/noodom/jeedom-core-command-selection/refs/heads/beta';
$corePath = __DIR__ . '/../../';
$backupSuffix = '.corePatchInstallation.bak';

$replaceFiles = array(
	'cmd.human.insert.php' => array(
		'url' => $baseUrl . '/desktop/modal/cmd.human.insert.php',
		'path' => $corePath . 'desktop/modal/cmd.human.insert.php'
	)
);

$addFiles = array(
	'cmd.human.insert.ajax.php' => array(
		'url' => $baseUrl . '/core/ajax/cmd.human.insert.ajax.php',
		'path' => $corePath . 'core/ajax/cmd.human.insert.ajax.php'
	)
);

$patches = array(
	'object.ajax.php' => array(
		'path' => $corePath . 'core/ajax/object.ajax.php',
		'check' => "if (init('action') == 'getUISelectListDetails') {",
		'anchor' => "if (init('action') == 'getSummaryHtml') {",
		'insert' => <<<'PHP'
	if (init('action') == 'getUISelectListDetails') {
		if (!isConnect('admin')) {
			throw new Exception(__('401 - Accès non autorisé', __FILE__));
		}
		ajax::success(jeeObject::getUISelectListDetails(init('none')));
	}

PHP
	),
	'jeeObject.class.php' => array(
		'path' => $corePath . 'core/class/jeeObject.class.php',
		'check' => 'public static function getUISelectListDetails(',
		'anchor' => 'public static function fullData(',
		'insert' => <<<'PHP'
	public static function getUISelectListDetails($_none = true) {
		$allObject = self::buildTree(null, false);
		$objects = array();

		if ($_none) {
			$objects[] = array(
				'id' => '',
				'name' => __('Aucun', __FILE__),
				'level' => 0,
				'icon' => ''
			);
		}

		foreach ($allObject as $object) {
			$objects[] = array(
				'id' => (string) $object->getId(),
				'name' => $object->getName(),
				'level' => (int) $object->getConfiguration('parentNumber', 0),
				'icon' => $object->getDisplay('icon')
			);
		}

		return $objects;
	}

PHP
	)
);

$modifiedFiles = array();
$createdFiles = array();
$backupsCreated = array();
$tmpFiles = array();

try {
	$validatePhp = function($path) {
		exec('php -l ' . escapeshellarg($path) . ' 2>&1', $output, $code);
		if ($code !== 0) {
			throw new Exception('Erreur PHP dans ' . $path . ' : ' . implode("\n", $output));
		}
	};

	$backup = function($path, $name) use (&$backupsCreated, $backupSuffix, $scenario) {
		$file = $path . $backupSuffix;
		if (file_exists($file)) {
			return;
		}
		if (!copy($path, $file)) {
			throw new Exception('Impossible de créer le backup de ' . $name);
		}
		$backupsCreated[] = $file;
		$scenario->setLog('[info] Backup créé : ' . $name);
	};

	$download = function($url, $path) use (&$tmpFiles, $validatePhp) {
		$tmp = $path . '.corePatchInstallation.tmp';
		$data = @file_get_contents($url);

		if ($data === false || $data === '') {
			throw new Exception('Téléchargement impossible : ' . $url);
		}

		if (file_put_contents($tmp, $data) === false) {
			throw new Exception('Impossible d\'écrire : ' . $tmp);
		}

		$tmpFiles[] = $tmp;
		$validatePhp($tmp);

		return $tmp;
	};

	/* Téléchargement */
	foreach (array_merge($replaceFiles, $addFiles) as $name => $file) {
		$scenario->setLog('[info] Téléchargement de ' . $name);
		$download($file['url'], $file['path']);
		$scenario->setLog('[info] ' . $name . ' téléchargé et valide');
	}

	/* Remplacements */
	foreach ($replaceFiles as $name => $file) {
		$path = $file['path'];
		$tmp = $path . '.corePatchInstallation.tmp';

		if (!file_exists($path)) {
			throw new Exception('Fichier introuvable : ' . $path);
		}

		if (md5_file($path) === md5_file($tmp)) {
			$scenario->setLog('[info] ' . $name . ' déjà à jour');
			continue;
		}

		$backup($path, $name);

		if (!copy($tmp, $path)) {
			throw new Exception('Impossible de remplacer ' . $name);
		}

		$modifiedFiles[] = $path;
		$validatePhp($path);
		$scenario->setLog('[info] ' . $name . ' remplacé');
	}

	/* Ajout / mise à jour */
	foreach ($addFiles as $name => $file) {
		$path = $file['path'];
		$tmp = $path . '.corePatchInstallation.tmp';

		if (file_exists($path)) {
			if (md5_file($path) === md5_file($tmp)) {
				$scenario->setLog('[info] ' . $name . ' déjà à jour');
				continue;
			}

			$backup($path, $name);

			if (!copy($tmp, $path)) {
				throw new Exception('Impossible de mettre à jour ' . $name);
			}

			$modifiedFiles[] = $path;
		} else {
			if (!copy($tmp, $path)) {
				throw new Exception('Impossible d\'installer ' . $name);
			}

			$createdFiles[] = $path;
		}

		$validatePhp($path);
		$scenario->setLog('[info] ' . $name . ' installé');
	}

	/* Patches ciblés */
	foreach ($patches as $name => $patch) {
		$path = $patch['path'];

		if (!file_exists($path)) {
			throw new Exception('Fichier introuvable : ' . $path);
		}

		$content = file_get_contents($path);

		if (strpos($content, $patch['check']) !== false) {
			$scenario->setLog('[info] ' . $name . ' : patch déjà présent');
			continue;
		}

		if (strpos($content, $patch['anchor']) === false) {
			throw new Exception('Ancre introuvable dans ' . $path);
		}

		if ($name === 'object.ajax.php' && strpos($content, "if (init('action') == 'getUISelectList') {") === false) {
			throw new Exception('Action getUISelectList introuvable dans ' . $path);
		}

		if ($name === 'jeeObject.class.php' && strpos($content, 'public static function getUISelectList(') === false) {
			throw new Exception('Méthode getUISelectList introuvable dans ' . $path);
		}

		$backup($path, $name);

		$pos = strpos($content, $patch['anchor']);
		$content = substr($content, 0, $pos) . $patch['insert'] . substr($content, $pos);

		if (file_put_contents($path, $content) === false) {
			throw new Exception('Impossible d\'écrire ' . $path);
		}

		$modifiedFiles[] = $path;
		$validatePhp($path);
		$scenario->setLog('[info] Patch appliqué : ' . $name);
	}

	/* Validation finale */
	foreach (array_merge($replaceFiles, $addFiles, $patches) as $file) {
		$validatePhp($file['path']);
	}

	foreach ($tmpFiles as $tmp) {
		@unlink($tmp);
	}

	$scenario->setLog('========================================');
	$scenario->setLog('Installation du patch Core beta terminée');
	$scenario->setLog('========================================');

} catch (Exception $e) {
	$scenario->setLog('[error] ' . $e->getMessage());
	$scenario->setLog('[error] Rollback automatique en cours');

	foreach ($tmpFiles as $tmp) {
		@unlink($tmp);
	}

	foreach (array_reverse($modifiedFiles) as $path) {
		$bak = $path . $backupSuffix;
		if (file_exists($bak) && copy($bak, $path)) {
			$scenario->setLog('[info] Restauré : ' . basename($path));
		}
	}

	foreach (array_reverse($createdFiles) as $path) {
		if (file_exists($path)) {
			@unlink($path);
			$scenario->setLog('[info] Supprimé : ' . basename($path));
		}
	}

	$scenario->setLog('[error] Rollback terminé');

	throw $e;
}