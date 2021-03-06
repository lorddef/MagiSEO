<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MagiSEO/site/PHP/Object/User.class.php';
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MagiSEO/site/PHP/DAO/DAO.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MagiSEO/site/PHP/Object/Server.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MagiSEO/site/PHP/DAO/ReportDAO.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MagiSEO/site/PHP/DAO/VMDAO.class.php';

class ServerDAO extends DAO {
	
	static function insertServer($IPV4, $username, $password, $keysshpath) { // MANQUE BEAUCOUP D'INFO A SAVE
            $bdd = parent::ConnectionBDD();

            $req = $bdd->prepare('INSERT INTO server_slave (IPV4, username, password, keysshpath) VALUES(:IPV4, :username, :password, :keysshpath)');
            if (!$req->execute(array(
                            'IPV4' => $IPV4,
                            'username' => $username,
                            'password' => $password,
                            'keysshpath' => $keysshpath
                    ))) {
                            ReportDAO::insertReport(new Report(0, $_SESSION['user']->getId(), "", "REQUEST SQL FAILED", "ServerDAO::insertServer()", REPORTING_TYPE_INTERNAL_ERROR, date("Y-m-d H:i:s")));
                            return false;
                    }
            return $bdd->lastInsertId();
	}

        static function insertServerInfo($idServer, $sizeDiskCurrentMB, $sizeDiskTotalMB, $sizeRAMCurrentKB, $sizeRAMTotalKB, $nbCPUUsed, $nbCPUTotal) {
            $bdd = parent::ConnectionBDD();
            
            $req = $bdd->prepare('INSERT INTO server_information (idserver, disk_max_size, disk_current_size, nb_max_proc, nb_current_proc, flash_max_size, flash_current_size) VALUES(:idserver, :disk_max_size, :disk_current_size, :nb_max_proc, :nb_current_proc, :flash_max_size, :flash_current_size)');
            if (!$req->execute(array(
                            'idserver' => $idServer,
                            'disk_max_size' => $sizeDiskTotalMB,
                            'disk_current_size' => $sizeDiskCurrentMB,
                            'nb_max_proc' => $nbCPUTotal,
                            'nb_current_proc' => $nbCPUUsed,
                            'flash_max_size' => $sizeRAMTotalKB,
                            'flash_current_size' => $sizeRAMCurrentKB
                    ))) {
                            ReportDAO::insertReport(new Report(0, $_SESSION['user']->getId(), "", "REQUEST SQL FAILED", "ServerDAO::insertServer()", REPORTING_TYPE_INTERNAL_ERROR, date("Y-m-d H:i:s")));
                            return false;
                    }
            return $bdd->lastInsertId();
        }
        
        static function deleteServer($IPV4) {
            $bdd = parent::ConnectionBDD();

            $req = $bdd->prepare('DELETE slave, info FROM server_slave AS slave INNER JOIN server_information AS info ON slave.id=info.idserver WHERE IPV4=:IPV4');
            if (!$req->execute(array(
                            'IPV4' => $IPV4
                    ))) {
                            ReportDAO::insertReport(new Report(0, $_SESSION['user']->getId(), "", "REQUEST SQL FAILED", "ServerDAO::deleteServer()", REPORTING_TYPE_INTERNAL_ERROR, date("Y-m-d H:i:s")));
                            return false;
                    }
            return true;
        }
        
        static function updateServerBasicServer($id, $IPV4, $name, $username, $password) {
            $bdd = parent::ConnectionBDD();
            
            $req = $bdd->prepare('UPDATE server_slave SET ipv4=:IPV4, name=:name, username=:username, password=:password WHERE id=:id');
            if (!$req->execute(array(
				'IPV4' => $IPV4,
                                'name' => $name,
				'username' => $username,
				'password' => $password,
                                'id' => $id
			))) {
				ReportDAO::insertReport(new Report(0, $_SESSION['user']->getId(), "", "REQUEST SQL FAILED", "ServerDAO::updateServerBasicServer()", REPORTING_TYPE_INTERNAL_ERROR), date("Y-m-d H:i:s"));
				return false;
			}
		return true;
        }
        
        
        
        static function getListSlaveServer() {
            $bdd = parent::ConnectionBDD();
	
            $reponse = $bdd->query('SELECT sslave.*, sinfo.disk_max_size, sinfo.disk_current_size, 
                                                            sinfo.nb_max_proc, sinfo.nb_current_proc, sinfo.flash_max_size, sinfo.flash_current_size 
                                                            FROM server_slave sslave 
                                                            INNER JOIN server_information sinfo ON sslave.id = sinfo.idserver');
            $listServer = array();
            while ($data = $reponse->fetch()) {
                    $server = new Server($data);
                    $listServer[] = $server;	
            }
            $reponse->closeCursor();
            foreach ($listServer as $server) {
                $server->setFlashCurrentSize(VMDAO::getVMsRamUsedByIdServer($server->getId()) + $server->getFlashCurrentSize());
            }
            return $listServer;
        }
        
        static function getNewSlaveServer() {
            $bdd = parent::ConnectionBDD();
            
            $reponse = $bdd->query('SELECT sslave.*, sinfo.disk_max_size, sinfo.disk_current_size, 
                                                            sinfo.nb_max_proc, sinfo.nb_current_proc, sinfo.flash_max_size, sinfo.flash_current_size 
                                                            FROM server_slave sslave 
                                                            INNER JOIN server_information sinfo ON sslave.id = sinfo.idserver ORDER BY sslave.id DESC LIMIT 1');
            $server;
            if ($data = $reponse->fetch())
                $server = new Server($data);
            $reponse->closeCursor();
            return $server;
        }
        
        static function getKeySSHPathServerByIP($ip) {
            $bdd = parent::ConnectionBDD();
            
            $ret = false;
            $reponse = $bdd->query('SELECT keysshpath FROM server_slave WHERE IPV4='. $bdd->quote($ip) .'');
            if ($data = $reponse->fetch())
                $ret = $data['keysshpath'];
            $reponse->closeCursor();
            return $ret;
        }
        
        static function getServerById($id) {
            $bdd = parent::ConnectionBDD();
            
            $ret = false;
            $reponse = $bdd->query('SELECT sslave.*, sinfo.disk_max_size, sinfo.disk_current_size, 
                                                            sinfo.nb_max_proc, sinfo.nb_current_proc, sinfo.flash_max_size, sinfo.flash_current_size 
                                                            FROM server_slave sslave 
                                                            INNER JOIN server_information sinfo ON sslave.id = sinfo.idserver WHERE sslave.id='. $bdd->quote($id) .'');
            $server = null;
            if ($data = $reponse->fetch())
                $server = new Server($data);
            $reponse->closeCursor();
            $server->setFlashCurrentSize(VMDAO::getVMsRamUsedByIdServer($server->getId()) + $server->getFlashCurrentSize());
            return $server;
        }
        
        static function getServerByIP($ip) {
            $bdd = parent::ConnectionBDD();
            
            $ret = false;
            $reponse = $bdd->query('SELECT sslave.*, sinfo.disk_max_size, sinfo.disk_current_size, 
                                                            sinfo.nb_max_proc, sinfo.nb_current_proc, sinfo.flash_max_size, sinfo.flash_current_size 
                                                            FROM server_slave sslave 
                                                            INNER JOIN server_information sinfo ON sslave.id = sinfo.idserver WHERE sslave.IPV4='. $bdd->quote($ip) .'');
            $server = null;
            if ($data = $reponse->fetch())
                $server = new Server($data);
            $reponse->closeCursor();
            $server->setFlashCurrentSize(VMDAO::getVMsRamUsedByIdServer($server->getId()) + $server->getFlashCurrentSize());
            return $server;
        }
        
        static function isServerExist($IPV4) {
            $bdd = parent::ConnectionBDD();
            
            $reponse = $bdd->query("SELECT COUNT(id) AS NB FROM server_slave WHERE ipv4=\"$IPV4\"");
            
            if ($data = $reponse->fetch())
                return intval($data['NB']) > 0 ? true : false;
            return false;
        }
}

?>