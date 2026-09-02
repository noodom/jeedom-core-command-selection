/*
 * Jeedom Core Patch
 *
 * Installation / désinstallation du patch de sélection des commandes humaines.
 *
 * Fichiers :
 * - /desktop/modal/cmd.human.insert.php
 * - /core/ajax/cmd.human.insert.ajax.php
 * - /core/ajax/object.ajax.php
 * - /core/class/jeeObject.class.php
 */

$action = "installer";
// $action = "desinstaller";

$files = [
    [
        "url" => "https://raw.githubusercontent.com/noodom/jeedom-core-command-selection/refs/heads/beta/desktop/modal/cmd.human.insert.php",
        "file" => "/var/www/html/desktop/modal/cmd.human.insert.php",
        "backup" => "/var/www/html/desktop/modal/cmd.human.insert.php.corepatch-backup",
        "name" => "cmd.human.insert.php",
        "replace" => true
    ],
    [
        "url" => "https://raw.githubusercontent.com/noodom/jeedom-core-command-selection/refs/heads/beta/core/ajax/cmd.human.insert.ajax.php",
        "file" => "/var/www/html/core/ajax/cmd.human.insert.ajax.php",
        "name" => "cmd.human.insert.ajax.php",
        "replace" => false
    ],
    [
        "url" => "https://raw.githubusercontent.com/noodom/jeedom-core-command-selection/refs/heads/beta/core/ajax/object.ajax.php",
        "file" => "/var/www/html/core/ajax/object.ajax.php",
        "backup" => "/var/www/html/core/ajax/object.ajax.php.corepatch-backup",
        "name" => "object.ajax.php",
        "replace" => true
    ],
    [
        "url" => "https://raw.githubusercontent.com/noodom/jeedom-core-command-selection/refs/heads/beta/core/class/jeeObject.class.php",
        "file" => "/var/www/html/core/class/jeeObject.class.php",
        "backup" => "/var/www/html/core/class/jeeObject.class.php.corepatch-backup",
        "name" => "jeeObject.class.php",
        "replace" => true
    ]
];

function corePatchLog($level, $message) {
    global $scenario;
    $scenario->setLog('[' . $level . '] ' . $message);
}

function corePatchDownload($url, $target) {
    $context = stream_context_create(["http" => ["timeout" => 30, "user_agent" => "Jeedom-Core-Patch", "follow_location" => true]]);
    $content = @file_get_contents($url, false, $context);
    if ($content === false || trim($content) === "") {
        throw new Exception("Téléchargement impossible : " . $url);
    }
    if (@file_put_contents($target, $content) === false) {
        throw new Exception("Impossible d'écrire le fichier temporaire : " . $target);
    }
}

function corePatchCheckPhp($file) {
    $output = [];
    $returnCode = 0;
    exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $returnCode);
    if ($returnCode !== 0) {
        throw new Exception("Syntaxe PHP invalide : " . implode(" ", $output));
    }
}

function corePatchSetPermissions($file) {
    @chown($file, "www-data");
    @chgrp($file, "www-data");
    @chmod($file, 0644);
}

if (!in_array($action, ["installer", "desinstaller"], true)) {
    throw new Exception("Action invalide : utiliser installer ou desinstaller");
}

/*
 * DESINSTALLATION
 */
if ($action === "desinstaller") {
    corePatchLog("info", "========================================");
    corePatchLog("info", "Désinstallation du patch Core");
    corePatchLog("info", "========================================");

    foreach ($files as $item) {
        if (!empty($item["replace"])) {
            if (file_exists($item["backup"])) {
                corePatchLog("info", "Backup trouvé : restauration de " . $item["name"]);
                if (!@copy($item["backup"], $item["file"])) {
                    throw new Exception("Impossible de restaurer " . $item["file"]);
                }
                corePatchSetPermissions($item["file"]);
                corePatchCheckPhp($item["file"]);
                if (!@unlink($item["backup"])) {
                    throw new Exception("Impossible de supprimer le backup " . $item["backup"]);
                }
                corePatchLog("info", $item["name"] . " restauré avec succès");
            } else {
                corePatchLog("info", "Aucun backup trouvé pour " . $item["name"] . " : aucune restauration effectuée");
            }
        }
    }

    $ajaxFile = $files[1]["file"];
    if (file_exists($ajaxFile)) {
        corePatchLog("info", "cmd.human.insert.ajax.php trouvé : suppression");
        if (!@unlink($ajaxFile)) {
            throw new Exception("Impossible de supprimer " . $ajaxFile);
        }
        corePatchLog("info", "cmd.human.insert.ajax.php supprimé avec succès");
    } else {
        corePatchLog("info", "cmd.human.insert.ajax.php déjà absent");
    }

    corePatchLog("info", "========================================");
    corePatchLog("info", "Désinstallation terminée");
    corePatchLog("info", "========================================");
    return;
}

/*
 * INSTALLATION
 */
corePatchLog("info", "========================================");
corePatchLog("info", "Installation du patch Core beta");
corePatchLog("info", "========================================");

$tempFiles = [];
$changedFiles = [];

try {
    foreach ($files as $index => $item) {
        $temp = sys_get_temp_dir() . "/jeedom-core-patch-" . $item["name"];
        $tempFiles[$index] = $temp;

        corePatchLog("info", "Téléchargement de " . $item["name"]);
        corePatchDownload($item["url"], $temp);
        corePatchCheckPhp($temp);
        corePatchLog("info", $item["name"] . " téléchargé et valide");

        if (!file_exists($item["file"]) && !empty($item["replace"])) {
            throw new Exception("Fichier Core introuvable : " . $item["file"]);
        }
    }

    foreach ($files as $index => $item) {
        $temp = $tempFiles[$index];

        if (!empty($item["replace"])) {
            $alreadyInstalled = md5_file($item["file"]) === md5_file($temp);

            if ($alreadyInstalled) {
                corePatchLog("info", $item["name"] . " déjà installé");
                continue;
            }

            if (!file_exists($item["backup"])) {
                corePatchLog("info", "Création du backup de " . $item["name"]);
                if (!@copy($item["file"], $item["backup"])) {
                    throw new Exception("Impossible de créer le backup de " . $item["name"]);
                }
                corePatchSetPermissions($item["backup"]);
                corePatchLog("info", "Backup créé");
            } else {
                corePatchLog("info", "Backup déjà présent : conservation du backup existant");
            }

            corePatchLog("info", "Remplacement de " . $item["name"]);
            if (!@copy($temp, $item["file"])) {
                throw new Exception("Impossible de remplacer " . $item["file"]);
            }
            corePatchSetPermissions($item["file"]);
            corePatchCheckPhp($item["file"]);
            $changedFiles[] = $index;
            corePatchLog("info", $item["name"] . " remplacé avec succès");
        } else {
            $alreadyInstalled = file_exists($item["file"]) && md5_file($item["file"]) === md5_file($temp);

            if ($alreadyInstalled) {
                corePatchLog("info", $item["name"] . " déjà installé");
                continue;
            }

            corePatchLog("info", file_exists($item["file"]) ? $item["name"] . " existe déjà : remplacement" : "Création de " . $item["name"]);
            if (!@copy($temp, $item["file"])) {
                throw new Exception("Impossible d'installer " . $item["file"]);
            }
            corePatchSetPermissions($item["file"]);
            corePatchCheckPhp($item["file"]);
            $changedFiles[] = $index;
            corePatchLog("info", $item["name"] . " installé avec succès");
        }
    }

    foreach ($tempFiles as $temp) {
        @unlink($temp);
    }

    corePatchLog("info", "========================================");
    corePatchLog("info", "Patch Core beta installé avec succès");
    corePatchLog("info", "========================================");
} catch (Exception $e) {
    corePatchLog("error", "Erreur : " . $e->getMessage());
    corePatchLog("error", "Rollback automatique en cours");

    foreach ($changedFiles as $index) {
        $item = $files[$index];

        if (!empty($item["replace"])) {
            if (file_exists($item["backup"]) && @copy($item["backup"], $item["file"])) {
                corePatchSetPermissions($item["file"]);
                corePatchLog("info", $item["name"] . " restauré");
            } else {
                corePatchLog("error", "Impossible de restaurer " . $item["name"]);
            }
        } elseif (file_exists($item["file"])) {
            if (@unlink($item["file"])) {
                corePatchLog("info", $item["name"] . " supprimé");
            } else {
                corePatchLog("error", "Impossible de supprimer " . $item["name"]);
            }
        }
    }

    foreach ($tempFiles as $temp) {
        @unlink($temp);
    }

    corePatchLog("error", "Rollback terminé");
    throw $e;
}