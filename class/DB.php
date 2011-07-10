<?php
class DB {
    private static $_objInstance=null;
    private $_objConnection;
    private $_user='root';
    private $_password='root';
    private $_host='localhost';
    private $_db='ausbildungsnachweise';
    
    private $_result;
    
    private function __construct() {
        $this->_objConnection = new mysqli($this->_host, $this->_user, $this->_password);
		$this->_objConnection->query("SET NAMES 'utf8'");
        $this->_objConnection->select_db($this->_db);
    }
    
    public function getConnection()
    {
        return $this->_objConnection;
    }
    
    public function setConnection($obj)
    {
        $this->_objConnection=$obj;
    }
    
    public static function getInstance()
    {
        if (self::$_objInstance === NULL) {
            self::$_objInstance = new self();
        }
        return self::$_objInstance;
    }
    
    public static function affected_rows()
    {
        return self::getInstance()->getConnection()->affected_rows;
    }
    
    public static function num_rows($result=null)
    {
        if (is_null($result)) {
            return self::getInstance()->getConnection()->num_rows;
        } else {
            return $result->num_rows;
        }
    }
    
    public static function getResult()
    {
        return self::getInstance()->_result;
    }
    
    public static function query($query)
    {
        return self::getInstance()->_result=self::getInstance()->getConnection()->query($query);
    }
    
    public static function fetch_assoc($result)
    {
        $return=array();
        while($row=$result->fetch_assoc()) {
            $return[]=$row;
        }
        return $return;
    }
}