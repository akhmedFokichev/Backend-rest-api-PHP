<?php

class HashService {
	
    private $hashKey;

    public function __construct(Config $config) {
        $this->hashKey = $config->hashKey;
    }
    
    public function hash($string) {
	
		$pwd_peppered = hash_hmac("sha256", $string, $this->hashKey);
	
		$pwd_hashed = password_hash($pwd_peppered, PASSWORD_BCRYPT);
	
		return $pwd_hashed;

	 }
}