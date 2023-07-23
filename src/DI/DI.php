<?php

class DI {
	public Config $config;
	
	public DBService $dbService;
	public HashService $hashService;
	public IdentityService $identityService;
	
	public function __construct(){
		$this->config = new Config();
		$this->dbService = new DBService($this->config);
		$this->hashService = new HashService($this->config);
		$this->identityService = new IdentityService($this->dbService);
    }
    
    public function initialization() {
    	$this->dbService->getConnection();
    }
    
}