<?php
    $_ROOT = dirname(__FILE__, 3);
    define("ROOT", dirname(__FILE__, 3));
    require_once ROOT .'/core/lib/responser.php';
    require_once ROOT .'/core/logs/systemLog.php';
    
    class databaseController{
        
        private $connectionObject;

        public function __construct($connectionObject) {
            $this->connectionObject = (!($connectionObject instanceof PDO)) ? NULL : $connectionObject;
        }

        public function insert($table, $data) {
            if($this->connectionObject == NULL) {
                return responser::systemResponse(400, 'Wrong connection Object', null);
            }

            $fields = array_keys($data);
            $placeholders = array_map(function($field) { return ':'.$field; }, $fields);
            $query = 'INSERT INTO '.$table.' ('.implode(',', $fields).') VALUES ('.implode(',', $placeholders).')';
            $statement = $this->prepare($query, $data, "insert");
            return ($statement["status"] == 200) ? 
            responser::systemResponse(200, 'Success insertion', ["id"=>$this->connectionObject->lastInsertId()]) :
            $statement;
        }

        public function select($table, $data) {
            if ($this->connectionObject == NULL) {
                return responser::systemResponse(400, 'Wrong connection Object', null);
            }
            $field = key($data);
            $query = "SELECT * FROM $table WHERE $field = :$field";
            $statement = $this->prepare($query, $data, "select");
            if($statement["status"] == 200) {
                $rows = $statement["data"]->fetchAll(PDO::FETCH_ASSOC);
                $rows = array_map(function ($row) {
                    return $row;
                }, $rows);
                if (empty($rows)) {
                    return responser::systemResponse(404, "Query is ok, but didn't find data", null);
                }
                return responser::systemResponse(200, "Success selected", $rows);
            } 
            else {
                return $statement;
            }
        }
        
        public function update($table, $data) {
            if ($this->connectionObject == NULL) {
                return responser::systemResponse(400, 'Wrong connection Object', null);
            }
            $lastElement = end($data);
            $lastField = key($data);
            array_pop($data);
            $fields = array_map(function ($field) {
                return "$field = :$field";
            }, array_keys($data));
            $data[$lastField] = $lastElement;
            $query = "UPDATE $table SET " . implode(', ', $fields) . " WHERE $lastField = :$lastField";
            $statement = $this->prepare($query, $data, "update");
            return ($statement["status"] == 200) ? 
            responser::systemResponse(200, 'Success update', $lastElement) :
            $statement;
        }
        
        public function delete($table, $data) {
            if ($this->connectionObject == NULL) {
                return responser::systemResponse(400, 'Wrong connection Object');
            }
            $field = key($data);
            $query = "DELETE FROM $table WHERE $field = :$field";
            $statement = $this->prepare($query, $data, "delete");
            return ($statement["status"] == 200) ? 
            responser::systemResponse(200, 'Success deleted', NULL) :
            $statement;
        }        

        
        protected function prepare($query, $data, $action){
            try{
                $statement = $this->connectionObject->prepare($query);
                if($action == "insert"){
                    foreach ($data as $field => $value) {
                        $statement->bindValue(':'.$field, $value);
                    }
                }
                if($statement->execute($data)) {
                    return responser::systemResponse(200, "Success prepared and executed", $statement);
                }
                else{
                    return responser::systemResponse(400, "Execution error", $statement);
                }
            }
            catch(Exception $e){
                systemLog::report('Database controller error: ' .$e, 'database-' .$action);
                return responser::systemResponse(400, "Error: " . $e, NULL);
            }
        } 
    }

?>