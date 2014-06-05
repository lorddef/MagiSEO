<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MagiSEO/site/PHP/Constantes.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MagiSEO/site/PHP/SSHConstances.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MagiSEO/site/PHP/ErrorConstantes.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MagiSEO/site/PHP/Object/Cache.class.php';

header("Content-Type: text/plain");

function getStateInstall() {
    $step = Cache::read(PATH_ROOT_WEBSITE . "/cache/install");
    $msg = "";
    switch ($step)
    {
        case INSTALL_SERVER_STEP_ERROR:
            $msg = "Error";
            break;
        case INSTALL_SERVER_STEP_INIT :
            $msg = "Initialisation";
            break;
        case INSTALL_SERVER_STEP_GENERATE_KEY:
            $msg = "Generate Key";
            break;
        case INSTALL_SERVER_STEP_COMPRESSING_SCRIPTS : 
            $msg = "Compressing scripts";
            break;
        case INSTALL_SERVER_STEP_UPLOADING_SCRIPTS :
            $msg = "Uploading scripts";
            break;
        case INSTALL_SERVER_STEP_INSTALLING_SOFTWARES :
            $msg = "Installing softwares";
            break;
        case INSTALL_SERVER_STEP_SECURING_SSH :
            $msg = "Securise SSH";
            break;
        case INSTALL_SERVER_STEP_GETTING_SERVER_INFOS :
            $msg = "Getting server informations";
            break;
        case INSTALL_SERVER_STEP_DONE :
            $msg = "Install Done";
            break;
    }
    
    echo $step . "/" . $msg . "/" . INSTALL_SERVER_STEP_DONE;
}

if (isset($_POST["nameRequest"])) {
    if ($_POST["nameRequest"] == "getStateInstall")
	getStateInstall();
}
