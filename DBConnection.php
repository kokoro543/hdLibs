<?php
/**
 * Class giup nay ket noi den database
 * 
 * @author: Hoang Dang
 * @date
 * @version
 */
namespace App\Libs;
use App\Config;
use Exception;
use PDO;

class DbConnection{
    protected $usename;
    protected $password;
    protected $host;
    protected $database;
    protected $tableName = "users";
    protected $queryParams = [];
    protected static $connectionInstance = null;


    public function __construct()
    {
       $dbConfig = Config::getInstance()->getDBConfig();
       $this->connect($dbConfig);
    }

    /**
     * 
     * @return new PDOs
     */
    public function connect(array $config = null){
        if(self::$connectionInstance == null){
            try{
                $this->host = $config['host']?? null;
                $this->database = $config['database']?? null;
                $this->username = $config['username']?? null;
                $this->password = $config['password']?? null;
                self::$connectionInstance = new PDO(
                    'mysql:host='.$this->host.';dbname='.$this->database,
                    $this->username,
                    $this->password
                );
                self::$connectionInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }catch(Exception $error){
                echo "ERROR: The programm can't connect to databases. ". $error->getMessage();
                die();
            }
        }
        return self::$connectionInstance;
    }

    /**
     * execute a query and prevent query injection
     * 
     * @param type $sql
     * @param param=[]
     * @return type
     */
    public function query($sql, $param=[]){
        $q = self::$connectionInstance->prepare($sql);
        if(is_array($param) && $param){
            $q->execute($param);
        }else{
            $q->execute();
        }
        return $q;
    }

    /**
     * build conditions for a query
     * 
     * @return instance DBConnection
     */
    public function buildQueryParams($params){
        $default = [
            "select"    =>  "",
            "where"     =>  "",
            "other"     =>  "",
            "params"    =>  "",
            "fields"    =>  "",
            "values"    =>  "",
        ];
        //$params[]
        $this->queryParams = array_merge($default, $params);
        return $this;
    }

    public function builConditions($conditions){
        if(trim($conditions)){
            return "where ". $conditions;
        }
        return "";
    }

    /*public function builConditions(){
        $conditions = $this->queryParams['query']??'';
        if(trim($conditions)){
            $cString = "where ";
        }
    }*/


    public function select(){
        $sql = "select ".$this->queryParams["select"]
                . " from ".$this->tableName 
                .$this->builConditions($this->queryParams["where"])
                . " ".$this->queryParams["other"];
        //$sql = "select :select from :tableName ".$this->builConditions($this->queryParams["where"]);
        $query = $this->query($sql,$this->queryParams);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectOne(){
        $this->queryParams["other"] = "limit 1";
        $data = $this->select();
        if($data){
            return $data[0];
        }
        return [];
    }

    public function insert(){
        $sql = "insert into " . $this->tableName . " " . $this->queryParams['fields'];
        $result = $this->query($sql, $this->queryParams['values']);
        if($result){
            return self::$connectionInstance->lastInsertId();
        }else{
            return false;
        } 
    }

    public function update(){
        $sql = "update ".$this->tableName. " set ".$this->queryParams["value"]. " ". 
                $this->builConditions($this->queryParams["where"]). " ". $this->queryParams["other"];
        return $this->query($sql);
    }

    public function delete(){
        $sql = "delete ". $this->tableName .
                $this->builConditions($this->queryParams["where"])." ".$this->queryParams["other"];
        return $this->query($sql);
    }

    
}   