/*
 * Jeedom Core Patch
 *
 * Ce script permet d'installer ou de désinstaller un patch du Core Jeedom.
 *
 * Fichiers concernés :
 * - /desktop/modal/cmd.human.insert.php
 *   Fichier Core modifié et sauvegardé avant remplacement.
 *
 * - /core/ajax/cmd.human.insert.ajax.php
 *   Nouveau fichier ajouté au Core Jeedom.
 *
 * Installation :
 * - Télécharge les fichiers patchés depuis GitHub.
 * - Vérifie leur syntaxe PHP avant installation.
 * - Sauvegarde le fichier Core original avant remplacement.
 * - Installe le fichier AJAX supplémentaire.
 * - Vérifie les fichiers après installation.
 * - Effectue automatiquement un rollback en cas d'erreur.
 *
 * Désinstallation :
 * - Restaure le fichier Core original depuis le backup.
 * - Supprime le fichier AJAX ajouté par le patch.
 * - Supprime le backup après restauration réussie.
 *
 * Le script est conçu pour permettre une installation réversible
 * du patch sans modification permanente du Core Jeedom.
 *
 * Auteur : noodom
 */

$action = "desinstaller";
// $action = "installer";

$replaceUrl = "https://raw.githubusercontent.com/noodom/jeedom-core-command-selection/refs/heads/main/desktop/modal/cmd.human.insert.php";
$replaceFile = "/var/www/html/desktop/modal/cmd.human.insert.php";
$replaceBackup = $replaceFile . ".corepatch-backup";

$addUrl = "https://raw.githubusercontent.com/noodom/jeedom-core-command-selection/refs/heads/main/core/ajax/cmd.human.insert.ajax.php";
$addFile = "/var/www/html/core/ajax/cmd.human.insert.ajax.php";

function corePatchLog($level, $message) {
    global $scenario;
    $scenario->setLog('[' . $level . '] ' . $message);
}

function corePatchDownload($url, $target) {
    $context = stream_context_create([
        "http" => [
            "timeout" => 30,
            "user_agent" => "Jeedom-Core-Patch",
            "follow_location" => true,
        ],
    ]);

    $content = @file_get_contents($url, false, $context);

    if ($content === false || trim($content) === "") {
        throw new Exception("Téléchargement impossible");
    }

    if (@file_put_contents($target, $content) === false) {
        throw new Exception("Impossible d'écrire le fichier temporaire");
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
 * ============================================================
 * DESINSTALLATION
 * ============================================================
 */

if ($action === "desinstaller") {
    corePatchLog("info", "========================================");
    corePatchLog("info", "Désinstallation du patch Core");
    corePatchLog("info", "========================================");

    /*
     * Restauration du fichier Core.
     *
     * Le fichier AJAX est traité indépendamment.
     */

    if (file_exists($replaceBackup)) {
        corePatchLog("info", "Backup trouvé : restauration de cmd.human.insert.php");

        if (!@copy($replaceBackup, $replaceFile)) {
            throw new Exception("Impossible de restaurer " . $replaceFile);
        }

        corePatchSetPermissions($replaceFile);
        corePatchCheckPhp($replaceFile);

        /*
         * Suppression du backup uniquement après
         * une restauration réussie.
         */

        if (!@unlink($replaceBackup)) {
            throw new Exception("Impossible de supprimer le backup");
        }

        corePatchLog("info", "cmd.human.insert.php restauré avec succès");
    } else {
        corePatchLog("info", "Aucun backup trouvé : aucune restauration effectuée");
    }

    /*
     * Suppression du fichier AJAX.
     *
     * Cette opération est totalement indépendante
     * de la restauration du fichier Core.
     */

    if (file_exists($addFile)) {
        corePatchLog("info", "cmd.human.insert.ajax.php trouvé : suppression");

        if (!@unlink($addFile)) {
            throw new Exception("Impossible de supprimer " . $addFile);
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
 * ============================================================
 * INSTALLATION
 * ============================================================
 */

corePatchLog("info", "========================================");
corePatchLog("info", "Installation du patch Core");
corePatchLog("info", "========================================");

$tempReplace = sys_get_temp_dir() . "/jeedom-core-patch-cmd.human.insert.php";
$tempAdd = sys_get_temp_dir() . "/jeedom-core-patch-cmd.human.insert.ajax.php";

$replaceChanged = false;
$addChanged = false;

try {
    /*
     * Téléchargement du fichier Core à remplacer
     */

    corePatchLog("info", "Téléchargement de cmd.human.insert.php");
    corePatchDownload($replaceUrl, $tempReplace);
    corePatchCheckPhp($tempReplace);
    corePatchLog("info", "cmd.human.insert.php téléchargé et valide");

    /*
     * Téléchargement du fichier AJAX
     */

    corePatchLog("info", "Téléchargement de cmd.human.insert.ajax.php");
    corePatchDownload($addUrl, $tempAdd);
    corePatchCheckPhp($tempAdd);
    corePatchLog("info", "cmd.human.insert.ajax.php téléchargé et valide");

    /*
     * Vérification du fichier Core
     */

    if (!file_exists($replaceFile)) {
        throw new Exception("Fichier Core introuvable : " . $replaceFile);
    }

    /*
     * Vérifier si le fichier Core est déjà identique
     */

    $replaceAlreadyInstalled = md5_file($replaceFile) === md5_file($tempReplace);

    /*
     * Création du backup.
     *
     * Le backup existant n'est jamais écrasé.
     */

    if (!$replaceAlreadyInstalled) {
        if (!file_exists($replaceBackup)) {
            corePatchLog("info", "Création du backup du fichier Core original");

            if (!@copy($replaceFile, $replaceBackup)) {
                throw new Exception("Impossible de créer le backup");
            }

            corePatchSetPermissions($replaceBackup);
            corePatchLog("info", "Backup créé");
        } else {
            corePatchLog("info", "Backup déjà présent : conservation du backup existant");
        }
    }

    /*
     * Remplacement du fichier Core
     */

    if (!$replaceAlreadyInstalled) {
        corePatchLog("info", "Remplacement de cmd.human.insert.php");

        if (!@copy($tempReplace, $replaceFile)) {
            throw new Exception("Impossible de remplacer " . $replaceFile);
        }

        corePatchSetPermissions($replaceFile);
        corePatchCheckPhp($replaceFile);
        $replaceChanged = true;

        corePatchLog("info", "cmd.human.insert.php remplacé avec succès");
    } else {
        corePatchLog("info", "cmd.human.insert.php déjà installé");
    }

    /*
     * Installation du fichier AJAX
     */

    $addAlreadyInstalled = file_exists($addFile) && md5_file($addFile) === md5_file($tempAdd);

    if ($addAlreadyInstalled) {
        corePatchLog("info", "cmd.human.insert.ajax.php déjà installé");
    } else {
        if (file_exists($addFile)) {
            corePatchLog("info", "cmd.human.insert.ajax.php existe déjà : remplacement");
        } else {
            corePatchLog("info", "Création de cmd.human.insert.ajax.php");
        }

        if (!@copy($tempAdd, $addFile)) {
            throw new Exception("Impossible d'installer " . $addFile);
        }

        corePatchSetPermissions($addFile);
        corePatchCheckPhp($addFile);
        $addChanged = true;

        corePatchLog("info", "cmd.human.insert.ajax.php installé avec succès");
    }

    /*
     * Nettoyage
     */

    @unlink($tempReplace);
    @unlink($tempAdd);

    corePatchLog("info", "========================================");
    corePatchLog("info", "Patch Core installé avec succès");
    corePatchLog("info", "========================================");
} catch (Exception $e) {
    corePatchLog("error", "Erreur : " . $e->getMessage());
    corePatchLog("error", "Rollback automatique en cours");

    /*
     * Restaurer le fichier Core uniquement
     * si cette exécution l'a modifié.
     */

    if ($replaceChanged && file_exists($replaceBackup)) {
        if (@copy($replaceBackup, $replaceFile)) {
            corePatchSetPermissions($replaceFile);
            corePatchLog("info", "cmd.human.insert.php restauré");
        } else {
            corePatchLog("error", "Impossible de restaurer cmd.human.insert.php");
        }
    }

    /*
     * Supprimer le fichier AJAX uniquement
     * si cette exécution l'a modifié.
     */

    if ($addChanged && file_exists($addFile)) {
        if (@unlink($addFile)) {
            corePatchLog("info", "cmd.human.insert.ajax.php supprimé");
        } else {
            corePatchLog("error", "Impossible de supprimer cmd.human.insert.ajax.php");
        }
    }

    /*
     * Nettoyage
     */

    @unlink($tempReplace);
    @unlink($tempAdd);

    corePatchLog("error", "Rollback terminé");

    throw $e;
}