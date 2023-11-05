<?php

class HashService {
	
    private $hashKey;

    public function __construct(Config $config) {
        $this->hashKey = $config->hashKey;
    }
    
    public function hash($value) {
		
		// TODO починить работу  password_hash + options
		// $cost = $this->hashKey;
		
		$hash = password_hash($value, PASSWORD_BCRYPT);

		return $hash;
	}
	
}