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