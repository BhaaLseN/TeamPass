<?php
/**
 * Teampass - a collaborative passwords manager.
 * ---
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * ---
 * @project   Teampass
 * @version   3.0.3
 * @file      upgrade_run_3.0.php
 * ---
 * @author    Nils Laumaillé (nils@teampass.net)
 * @copyright 2009-2023 Teampass.net
 * @license   https://spdx.org/licenses/GPL-3.0-only.html#licenseText GPL-3.0
 * ---
 * @see       https://www.teampass.net
 */

set_time_limit(600);


require_once '../sources/SecureHandler.php';
session_name('teampass_session');
session_start();
error_reporting(E_ERROR | E_PARSE);
$_SESSION['CPM'] = 1;

//include librairies
require_once '../includes/language/english.php';
require_once '../includes/config/include.php';
require_once '../includes/config/settings.php';
require_once '../sources/main.functions.php';
require_once '../includes/libraries/Tree/NestedTree/NestedTree.php';
require_once 'tp.functions.php';
require_once 'libs/aesctr.php';
require_once '../includes/config/tp.config.php';

// Get the encrypted password
define('DB_PASSWD_CLEAR', defuse_return_decrypted(DB_PASSWD));

/*
//Build tree
$tree = new Tree\NestedTree\NestedTree(
    $pre . 'nested_tree',
    'id',
    'parent_id',
    'title'
);
*/

// DataBase
// Test DB connexion
$pass = DB_PASSWD_CLEAR;
$server = DB_HOST;
$pre = DB_PREFIX;
$database = DB_NAME;
$port = DB_PORT;
$user = DB_USER;

if (mysqli_connect(
    $server,
    $user,
    $pass,
    $database,
    $port
)) {
    $db_link = mysqli_connect(
        $server,
        $user,
        $pass,
        $database,
        $port
    );
} else {
    $res = 'Impossible to get connected to server. Error is: ' . addslashes(mysqli_connect_error());
    echo '[{"finish":"1", "msg":"", "error":"Impossible to get connected to server. Error is: ' . addslashes(mysqli_connect_error()) . '!"}]';
    mysqli_close($db_link);
    exit();
}

// Load libraries
require_once '../includes/libraries/protect/SuperGlobal/SuperGlobal.php';
$superGlobal = new protect\SuperGlobal\SuperGlobal();


//--->BEGIN 3.0.1

// Ensure admin user is ready
mysqli_query(
    $db_link,
    "UPDATE ".$pre."users 
    SET is_ready_for_usage = 1, otp_provided = 1 
    WHERE id = 1"
);

//---<END 3.0.1


// Save timestamp
$tmp = mysqli_num_rows(mysqli_query($db_link, "SELECT * FROM `" . $pre . "misc` WHERE type = 'admin' AND intitule = 'upgrade_timestamp'"));
if (intval($tmp) === 0) {
    mysqli_query(
        $db_link,
        "INSERT INTO `" . $pre . "misc` (`type`, `intitule`, `valeur`) VALUES ('admin', 'upgrade_timestamp', ".time().")"
    );
} else {
    mysqli_query(
        $db_link,
        "UPDATE `" . $pre . "misc` SET valeur = ".time()." WHERE type = 'admin' AND intitule = 'upgrade_timestamp'"
    );
}

//---< END 3.0.X upgrade steps

// Close connection
mysqli_close($db_link);

// Finished
echo '[{"finish":"1" , "next":"", "error":""}]';