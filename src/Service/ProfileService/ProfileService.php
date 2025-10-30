<?php

class ProfileService {
    
	private \ProfileDataBase $profileDataBase;

	public $profile;

    public function __construct() { }
    
   private function setup() {
   	    global $di;
    	$this->profileDataBase = new \ProfileDataBase($di->dbService);
   }
    
    
    public function auth($authorization) {
    	// var_dump($authorization)
    	
    	// $this->person = new Person();
    	
    	// return $this->person;
    }
    
}


class ProfileServiceSQL {
	
	protected $dbService;
		
    public function __construct($dbService) {
        $this->dbService = $dbService;
    }
    
    public function getUser($login) {
    	$sql = "SELECT * FROM `identity_users` WHERE `login` = '$login'";
    	return $this->dbService->getObject($sql);
    }
    
    public function addUser($access_level, $login, $pass_hash) {
      	$sql = "INSERT INTO `identity_users`(`access_level`, `login`, `pass_hash`, `created_at`, `updated_at`) VALUES ($access_level,'$login','$pass_hash',now(), now())";

		$this->dbService->executeSql($sql);
    }
    
    public function deleteToken($accessToken) {
    	
    }
}