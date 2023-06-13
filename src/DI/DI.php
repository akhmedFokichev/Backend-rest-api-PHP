<?php
require_once('../config/Config.php');
require_once('../src/Service/DataBase.php');

class DI {
	public Config $config;
	private DataBase $dataBase;
	
	public function __construct(){
		$this->config = new Config();
		$this->dataBase = new DataBase($this->config);
    }
    
    public function initialization() {
    	$this->dataBase->getConnection();
    }
}