<?php

class Token {
	public $accessToken = "";
	public $refreshToken = "";
	public $expiresIn = 0;
	
	public function __construct($accessToken, $refreshToken, $expiresIn) {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresIn = $expiresIn;
    }
	    
	public function toJson() {
		return array(
			'accessToken' => $this->accessToken,
			'refreshToken' => $this->refreshToken,
			'expiresIn' => $this->expiresIn
		);
	}
}