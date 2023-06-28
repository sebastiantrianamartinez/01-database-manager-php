<?php
    define("ROOT", dirname(__FILE__, 3));
    require_once ROOT .'/core/lib/responser.php';
    require_once ROOT .'/core/database/databaseController.php';
    require_once ROOT .'/core/logs/systemLog.php';
    
    class localConnection extends databaseController{

        private $databaseDsn;
        private $databaseUser;
        private $databasePassword;
        private $databaseSettings;

        public function __construct(){
            $jsonConfig = file_get_contents(ROOT .'/config/config.json');
            $stconfig = json_decode($jsonConfig, true);
            $this->databaseDsn = "mysql:" . $stconfig["dsn"]["host"] .";dbname=" .$stconfig["dsn"]["name"];
            $this->databaseUser = $stconfig["dsn"]["user"];
            $this->databasePassword = file_get_contents(ROOT .'/core/keys/database.key');
            $this->databaseSettings = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false, 
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
        }

        public function open(){
            try {
                $localConnection = new PDO($this->databaseDsn, $this->databaseUser, $this->databasePassword, $this->databaseSettings);
                parent::__construct($localConnection);
                return responser::systemResponse(200, "Success connection", $localConnection);
            } 
            catch(PDOException $e) {
                systemLog::systemReport("Fatal error " .(string)$e, "database-localconnection");
                return responser::systemResponse(400, "Unsuccess connection", ["exception" => (string)$e]);
            }
        }

    }