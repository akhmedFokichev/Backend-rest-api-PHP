<?php

class DI {
    public Config $config;
    
    public DBService $dbService;
    
    public SessionService $sessionService;
    
    public UtilityService $utilityService;
    public IdentityService $identityService;
    public ProfileService $profileService;
    
    public function __construct(){
        $this->config = new Config();
        $this->dbService = new DBService($this->config);
        $this->utilityService = new UtilityService($this->config);
        $this->sessionService = new SessionService();
        
        $this->identityService = new IdentityService();
        $this->profileService = new ProfileService();
    }
    
    public function initialization() {
        $this->dbService->getConnection();
    }
    
}
