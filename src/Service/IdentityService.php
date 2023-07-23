<?php

class IdentityService {
	
    private $dbService;
    public $person;

    public function __construct(DBService $dbService) {
        $this->dbService = $dbService;
    }
    
    public function auth($authorization) {
    	// var_dump($authorization)
    	
    	$this->person = new Person();
    	
    	return $this->person;
    }
    
    

    public function login($login, $pass) {
    	global $di;
    	$identityDataBase = new IdentityDataBase($this-->dbService);
    	$hashService = $di->hashService;
    
		$value = $hashService->hash($pass);
		
		$accessToken = $this->generateToken();
		$refreshToken = $this->generateToken();
		$expiresIn = strtotime("+1 day");		
		
		
	 	$identityDataBase->saveToken(1, $accessToken, $refreshToken, $expiresIn);
			
		return $accessToken;
    }
    
}