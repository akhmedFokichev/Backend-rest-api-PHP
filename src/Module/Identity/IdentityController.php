<?php

namespace App\Module\Identity;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class IdentityController {

	private \HashService $hashService;
	private \IdentityService $identityService;

	// init
    public function __construct() {
      global $di;

      $this->hashService = $di->hashService;
      $this->identityService = $di->identityService;
	}
	

	// public
    public function login(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        global $di;
        
        $this->identityService->login();
        
        $parsedBody = $request->getBody()->getContents();
        
        $json = json_decode($parsedBody);
        
		// $this->authLogin('aaaaa', 'ssssss');
		
		// var_dump($json->login);
		
	$profile = new \Profile();
		
	 $payload = json_encode($profile->toJson());

	 $response->getBody()->write($payload);
	
	 return $response
	 ->withHeader('Content-Type', 'application/json')
	 ->withStatus(400);
	 
    }
    
    public function add(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        global $di;
        
		$this->authLogin('aaaaa', 'ssssss');
		
        return $response;
    }
    
    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        global $di;
        
		$this->authLogin('aaaaa', 'ssssss');
		
        return $response;
    }
    
    public function refresh(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        global $di;
        
		$this->authLogin('aaaaa', 'ssssss');
		
        return $response;
    }

    
    // private

    
    private function newUser($access_level, $login, $pass) {
    	global $di;
    	$hashService = $di->hashService;
    	
    	$access_level = 2;
    	$passHashed = $hashService->hash($pass);
    	$identityDataBase->saveNewUser($access_level, $login, $passHashed);
    }
    
    private function generateToken() {
    	global $di;
    	$hashService = $di->hashService;
    	    	
    	$value1 = $this->generateRandomString(25);
    	$value2 = date('d.m.Y H:i:s');
    	
    	$token = $hashService->hash($value1).$hashService->hash($value2);
    	    
        return $token;  	
    }
    
    private function generateRandomString($length = 30) {
    	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    	$charactersLength = strlen($characters);
    	$randomString = '';
    	for ($i = 0; $i < $length; $i++) {
    	  $randomString .= $characters[random_int(0, $charactersLength - 1)];
    	}
       return $randomString;
    }
    
}