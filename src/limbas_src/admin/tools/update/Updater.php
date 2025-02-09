<?php
/**
 * @copyright Limbas GmbH <https://limbas.com>
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */
namespace admin\tools\update;

require_once(COREPATH . 'lib/db/db_wrapper.lib');
require_once(COREPATH . 'lib/db/db_' . $DBA['DB'] . '_admin.lib');
require_once(COREPATH . 'lib/include_admin.lib');
require_once(COREPATH . 'admin/tools/datasync/DatasyncClient.php');

use Database;
use DatasyncClient;
use Throwable;

class Updater
{

    /** @var array contains version of database and source */
    private static array $versions;

    /** @var array contains class models of relevant updates */
    private static array $updates;

    /**
     * Checks for update if auto-update-check is enabled in umgvar (or manualUpdate is set to true)
     * Returns:
     *  - false on error
     *  - true if current version is the latest version
     *  - latest version (e.g. "3.5") else
     * @param bool $manualUpdate ignores umgvar auto-check setting if set to true
     * @return bool|string
     */
    public static function checkNewVersionAvailable(bool $manualUpdate = false): bool|string
    {
        global $session;
        global $umgvar;

        $db = Database::get();


        // user is admin & auto-check for updates enabled
        if (!$manualUpdate && ($session['user_id'] != 1 || !$umgvar['update_check'])) {
            return false;
        }

        // return cached version
        if (!$manualUpdate and isset($session['latestVersion'])) {
            return $session['latestVersion'];
        }

        // current version
        $versions = self::getVersions();
        list ($currentMajor, $currentMinor, $currentPatch) = explode('.', $versions['source']);

        // get latest version
        $context = stream_context_create(array('http' => array('timeout' => 2)));
        $latestVersion = false;
        try {
            $latestVersion = file_get_contents("https://www.limbas.org/version-$currentMajor-$currentMinor-$currentPatch.html", false, $context);
        } catch (Throwable) {}
        if (!$latestVersion) {
            if (!$manualUpdate && $umgvar['update_check']) {
                // probably no connection -> disable update_check
                $sqlquery = "UPDATE LMB_UMGVAR SET NORM='0' WHERE FORM_NAME='update_check'";
                lmbdb_exec($db, $sqlquery) or errorhandle(lmbdb_errormsg($db), $sqlquery, $GLOBALS['action'], __FILE__, __LINE__);
            }
            $session['latestVersion'] = false;
            return false;
        }

        //version higher than currently installed?
        $newVersionAvailable = version_compare($latestVersion, $versions['source'], '>');
        if ($newVersionAvailable) {
            $session['latestVersion'] = $latestVersion;
        } else {
            $session['latestVersion'] = true;
        }
        return $session['latestVersion'];
    }


    /**
     * Get the versions of database and source
     *
     * @return array
     */
    public static function getVersions(): array
    {
        if (!empty(self::$versions)) {
            return self::$versions;
        }

        global $umgvar;

        $versionFile = COREPATH . 'lib/version.inf';

        $sourceVersion = '0.0.0';
        $databaseVersion = '0.0.0';

        // get source version
        if (file_exists($versionFile)) {
            $version = explode('.', file_get_contents($versionFile));
            $sourceVersion = $version[0] . '.' . $version[1] . '.' . $version[2];
        }
        $umgvar['version'] = $sourceVersion;

        // get database version
        $db = Database::get();
        $sql = 'SELECT REVISION,VERSION,MAJOR FROM LMB_DBPATCH ORDER BY MAJOR DESC, VERSION DESC,REVISION DESC';
        $rs = lmbdb_exec($db, $sql) or errorhandle(lmbdb_errormsg($db), $sql, 'getVersions', __FILE__, __LINE__);
        if ($rs) {
            $databaseVersion = lmbdb_result($rs, 'MAJOR') . '.' . lmbdb_result($rs, 'VERSION') . '.' . lmbdb_result($rs, 'REVISION');
        }

        $umgvar['db-version'] = $databaseVersion;

        self::$versions = [
            'source' => $sourceVersion,
            'db' => $databaseVersion
        ];

        return self::$versions;
    }


    /**
     * CHeck if source version and database version are the same or if otherwise an update is needed
     *
     * @param bool $redirect
     * @param bool $noAutoUpdate
     * @return bool|int|void
     */
    public static function checkVersion(bool $redirect = true, bool $noAutoUpdate = false)
    {
        global $umgvar;
        global $LINK;

        $versions = self::getVersions();

        $updateNeeded = version_compare($versions['source'], $versions['db'], '>');


        if ($updateNeeded && (defined('IS_SOAP') || defined('IS_WEBDAV') || defined('IS_WSDL') || defined('IS_CRON'))) {
            return $updateNeeded;
        }

        if (!$noAutoUpdate && $updateNeeded && array_key_exists('update_mode', $umgvar) && $umgvar['update_mode'] === 'background') {
            return self::runUpdate();
        }


        if ($updateNeeded && $redirect) {
            if ($LINK['setup_update']) {
                header('Location: main_admin.php?action=setup_update');
            } else {
                header('Location: main.php?action=maintenance');
            }

            exit(1);
        }

        return $updateNeeded;
    }


    /**
     * Run all available updates
     *
     * @param bool $verbose
     * @return array|bool
     */
    public static function runUpdate(bool $verbose = false): bool|array
    {
        $updateNeeded = self::checkVersion(false, true);
        if (!$updateNeeded) {
            return true;
        }
        
        self::addMsgColumn();

        self::loadUpdates();

        $success = true;

        $output = [
            'status' => false,
            'updates' => []
        ];

        /** @var Update $update */
        foreach (self::$updates as $update) {

            $updateSuccess = $update->run();

            if ($verbose) {
                $updateOutput = [
                    'version' => $update->getVersion(),
                    'status' => $updateSuccess,
                    'failedPatches' => []
                ];

                if (!$updateSuccess) {
                    $updateOutput['failedPatches'] = $update->getMissingPatches();
                }

                $output['updates'][$update->getVersion()] = $updateOutput;
            }


            if (!$updateSuccess) {
                // only continue with next minor update if previous update was successful
                $success = false;
                break;
            }
        }

        if ($verbose) {
            $output['status'] = $success;
            return $output;
        }
        return $success;
    }

    /**
     * Get all patches of an updates
     *
     * @param int $key
     * @return array
     */
    private static function getUpdatePatches(int $key = 0): array
    {
        $update = self::getUpdate($key);

        if (!$update) {
            return [];
        }

        return $update->getPatches();
    }

    /**
     * Check if whole update is completed
     *
     * @param int $key
     * @return bool
     */
    private static function getUpdateCompleted(int $key = 0): bool
    {
        $update = self::getUpdate($key);

        if (!$update) {
            return true;
        }

        return $update->completed;
    }

    /**
     * Run specific patch of specific update
     *
     * @param int $patchNr
     * @param int $updateKey
     * @return bool
     */
    public static function runPatch(int $patchNr, int $updateKey = 0): bool
    {

        $update = self::getUpdate($updateKey);

        if (!$update) {
            return true;
        }

        return $update->runPatch($patchNr);
    }

    /**
     * Get specific patch of specific update
     * @param int $patchNr
     * @param int $updateKey
     * @return mixed|null
     */
    public static function getPatch(int $patchNr, int $updateKey = 0): mixed
    {
        $update = self::getUpdate($updateKey);

        return $update?->getPatch($patchNr);

    }

    /**
     * Get specific update
     *
     * @param int $key
     * @return Update|null
     */
    private static function getUpdate(int $key = 0): ?Update
    {
        self::loadUpdates();

        if (empty(self::$updates)) {
            return null;
        }

        if (!array_key_exists($key, self::$updates)) {
            return null;
        }

        /** @var Update $update */
        return self::$updates[$key];
    }


    /**
     * Load available update classes
     *
     * @return void
     */
    private static function loadUpdates(): void
    {

        if (!empty(self::$updates)) {
            return;
        }

        $versions = self::getVersions();

        $versionParts = array_map('intval', explode('.', $versions['source']));

        $updateClasses = [];

        // zero index is always current update
        $updateClasses[0] = 'Update' . $versionParts[0] . 'm' . $versionParts[1];

        // if previous version is a minor version of current major version
        if ($versionParts[1] - 1 >= 0) {
            $updateClasses[] = 'Update' . $versionParts[0] . 'm' . ($versionParts[1] - 1);
        } elseif($versionParts[1] - 1 < 0) {
            
            $previousMajor = $versionParts[0] - 1;

            $availableUpdateFiles = array_diff(scandir(__DIR__ . '/updates' ), ['.', '..']);
            $availableUpdateFiles = array_filter($availableUpdateFiles, function($value) use ($previousMajor) {
                return str_starts_with($value, 'Update' . $previousMajor);
            });
            
            $previousMinorVersions = [];
            foreach($availableUpdateFiles as $availableUpdateFile) {
                $fileVersion = explode('m', str_replace(['Update','.php'],'',$availableUpdateFile));
                $previousMinorVersions[] = intval($fileVersion[1]);
            }

            if (!empty($previousMinorVersions)) {
                $previousMinor = max($previousMinorVersions);
                
                //previous major release
                $updateClasses[] = 'Update' . $previousMajor . 'm' . $previousMinor;
            }
            
            
        }


        
        

        foreach ($updateClasses as $key => $update) {
            $updateFile = __DIR__ . '/updates/' . $update . '.php';
            if (file_exists($updateFile)) {
                require_once $updateFile;
                $className = '\\admin\\tools\\update\\updates\\' . $update;
                self::$updates[$key] = new $className(); //new \admin\tools\update\updates\Update4m4();//
            }
        }

    }

    /**
     * Get the version number of the previous version
     *
     * @return array|false|string
     */
    private static function getPreviousVersionNumber(): bool|array|string
    {

        $update = self::getUpdate(1);

        if (!$update) {
            return false;
        }

        return $update->getVersion();
    }


    /**
     * Check if patch did run and wether it was successful
     *
     * @param int $major
     * @param int $minor
     * @param int $patch
     * @return bool|null
     */
    public static function checkPatchDidRun(int $major, int $minor, int $patch): ?bool
    {
        $db = Database::get();
        $sql = "SELECT ID, STATUS FROM LMB_DBPATCH WHERE MAJOR = $major AND VERSION = $minor AND REVISION = $patch";// AND STATUS = " . LMB_DBDEF_TRUE;
        $rs = lmbdb_exec($db, $sql);
        if (!$rs || !lmbdb_fetch_row($rs)) {
            return null;
        }

        return (bool)lmbdb_result($rs, 'STATUS');
    }

    /**
     * Insert status of a patch into database
     *
     * @param int $major
     * @param int $minor
     * @param int $patch
     * @param string $desc
     * @param bool $success
     * @param string $error
     * @return bool
     */
    public static function applyPatch(int $major, int $minor, int $patch, string $desc, bool $success, string $error = ''): bool
    {
        $db = Database::get();

        $sql = "SELECT ID FROM LMB_DBPATCH WHERE MAJOR = $major AND VERSION = $minor AND REVISION = $patch";
        $rs = lmbdb_exec($db, $sql);
        $id = null;
        if ($rs && lmbdb_fetch_row($rs)) {
            $id = lmbdb_result($rs, 'ID');
        }

        $status = (($success) ? LMB_DBDEF_TRUE : LMB_DBDEF_FALSE);


        $error = parse_db_string(lmb_substr($error, 0, 300));
        if (empty($id)) {
            $id = next_db_id('LMB_DBPATCH');
            $sql = "INSERT INTO LMB_DBPATCH (ID,MAJOR,VERSION,REVISION,DESCRIPTION,STATUS,MSG) VALUES ($id,$major,$minor,$patch,'$desc',$status,'$error')";
        } else {
            $sql = "UPDATE LMB_DBPATCH SET STATUS = $status, MSG = '$error' WHERE MAJOR = $major AND VERSION = $minor AND REVISION = $patch";
        }
        
        
        $rs = lmbdb_exec($db, $sql);
        if (!$rs) {
            return false;
        }

        return true;
    }

    /**
     * add MSG field to LMB_DBPATCH
     *
     * @return void
     */
    public static function addMsgColumn(): void
    {
        global $DBA;
        
        $db = Database::get();
        $msgColumnExists = (bool)dbf_5([$DBA['DBSCHEMA'], 'LMB_DBPATCH', 'MSG']);
        
        if (!$msgColumnExists) {
            lmbdb_exec($db, 'ALTER TABLE LMB_DBPATCH ADD MSG VARCHAR(300)');
        }
    }

    /**
     * Set a patch as done independent of status
     * @param int $patchNr
     * @param int $updateKey
     * @return bool
     */
    public static function setPatchDone(int $patchNr, int $updateKey = 0): bool
    {

        $update = self::getUpdate($updateKey);

        if (!$update) {
            return false;
        }

        $versionParts = $update->getVersion(true);

        $patch = self::getPatch($patchNr,$updateKey);
        if (!$patch) {
            return false;
        }

        return self::applyPatch($versionParts[0], $versionParts[1], $patchNr, $patch['desc'], true);

    }


    /**
     * Get all relevant version information of the system
     *
     * @return array
     */
    public static function getSystemInfo(): array
    {

        $versions = Updater::getVersions();

        return [
            'versions' => $versions,
            'updateNecessary' => Updater::checkVersion(false),
            'version' => [
                'current' => $versions['source'],
                'previous' => Updater::getPreviousVersionNumber()
            ],
            'patches' => [
                'current' => Updater::getUpdatePatches(),
                'previous' => Updater::getUpdatePatches(1)
            ],
            'completed' => [
                'current' => Updater::getUpdateCompleted(),
                'previous' => Updater::getUpdateCompleted(1)
            ]
        ];
    }

    /**
     * @param array $params
     * @param bool $isRemote
     * @return array
     */
    public static function dynsRunPatch(array $params, bool $isRemote = false): array
    {
        $patchNr = $params['patch'] ?? 0;
        $update = $params['update'] ?? 0;
        $clientId = $params['client'] ?? 0;

        $msg = '';

        if (!empty($clientId) && !$isRemote) {
            $response = Updater::runRemoteAction('runPatch', $clientId, $params);
            if ($response === false) {
                $success = 'false';
                $msg = 'Connection failed';
            } else {
                $success = $response['success'];
                $msg = $response['msg'];
            }

        } else {

            $success = Updater::runPatch($patchNr, $update);
            if (!$success) {
                $patch = Updater::getPatch($patchNr, $update);
                if ($patch) {
                    $msg = $patch['error'];
                }
            }
        }


        return compact('success', 'msg');
    }


    /**
     * @param array $params
     * @param bool $isRemote
     * @return array
     */
    public static function dynsMarkPatchAsDone(array $params, bool $isRemote = false): array
    {
        $patchNr = $params['patch'] ?? 0;
        $update = $params['update'] ?? 0;
        $clientId = $params['client'] ?? 0;

        $success = false;
        if (!empty($clientId) && !$isRemote) {
            $response = Updater::runRemoteAction('updateMarkPatchAsDone', $clientId, $params);
            if (is_array($response) && array_key_exists('success', $response)) {
                $success = $response['success'];
            }
        } else {
            $success = Updater::setPatchDone($patchNr, $update);
        }

        return compact('success');
    }


    /**
     * @param $clientId
     * @return false|mixed
     */
    public static function getRemoteSystemInfo($clientId): mixed
    {
        $client = DatasyncClient::get($clientId);

        return $client->fetchUpdateStatus();
    }


    /**
     * @param $action
     * @param $clientId
     * @param $params
     * @return array|bool
     */
    public static function runRemoteAction($action, $clientId, $params): array|bool
    {

        $client = DatasyncClient::get($clientId);

        return $client->runRemoteAction('run_system_update_action', $action, $params);
    }

    /**
     * @param $action
     * @param $params
     * @return bool|array
     */
    public static function applyRemoteAction($action, $params): bool|array
    {

        return match ($action) {
            'runPatch' => self::dynsRunPatch($params, true),
            'updateMarkPatchAsDone' => self::dynsMarkPatchAsDone($params, true),
            default => false,
        };

    }

}
