<?php

namespace App\Module\Identity;

class IdentityDataBase {
	
	protected $dbService;
		
    public function __construct($dbService) {
        $this->dbService = $dbService;
    }
    
    public function getUser($login) {
    
    
    }
    
    public function saveNewUser($access_level, $login, $pass_hash) {
      	$sql = "INSERT INTO `identity_users`(`access_level`, `login`, `pass_hash`, `created_at`, `updated_at`) VALUES ($access_level,'$login','$pass_hash',now(), now())";

		$this->dbService->executeSql($sql);
    }
    
    public function saveToken($userId, $accessToken, $refreshToken, $expiresIn) {
		$sql = "INSERT INTO `identity_session`(`user_id`, `access_token`, `refresh_token`, `expiresIn`, `updated_at`, `created_at`) VALUES ($userId,'$accessToken','$refreshToken',$expiresIn, now(), now())";

		$this->dbService->executeSql($sql);
    }
    
    public function deleteToken($accessToken) {
    	
    }
}