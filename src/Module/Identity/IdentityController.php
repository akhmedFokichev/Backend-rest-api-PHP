<?php

namespace App\Module\Identity;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class IdentityController {

	private \IdentityService $identityService;

	private \Profile $profile;
	// init
    public function __construct() {
      global $di;
      $this->identityService = $di->identityService;
	}
	

	// public
	
	public function registration(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        global $di;
        
      $parsedBody = $request->getBody()->getContents();
      $body = json_decode($parsedBody);
     
      $token = $this->identityService->registration($body->secret_key, $body->client_id, $body->login, $body->password);
      
	 if ($token == null) {
    	$responseError = new \ResponseError(403, "Нет доступа!", "такой логин уже существует!"); 	
    	$responseJson = json_encode($responseError->toJson());
		$response->getBody()->write($responseJson);
	 
      return $response
	   ->withHeader('Content-Type', 'application/json')
	   ->withStatus(403);
     }
     
     
	 $responseJson = json_encode($token->toJson());
	 $response->getBody()->write($responseJson);
	
	 return $response
	 ->withHeader('Content-Type', 'application/json')
	 ->withStatus(200);
    }
    
	
    public function login(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
    	
     $parsedBody = $request->getBody()->getContents();
     $body = json_decode($parsedBody);
        
     $token = $this->identityService->login($body->secret_key, $body->client_id, $body->login, $body->password);
     
     if ($token == null) {
    	$responseError = new \ResponseError(403, "Нет доступа!", "Логин или пароль неверный!"); 	
    	$responseJson = json_encode($responseError->toJson());
		$response->getBody()->write($responseJson);
	 
      return $response
	 ->withHeader('Content-Type', 'application/json')
	 ->withStatus(401);
     }
        
		
	 $responseJson = json_encode($token->toJson());
	 $response->getBody()->write($responseJson);
	
	 return $response
	 ->withHeader('Content-Type', 'application/json')
	 ->withStatus(200);
    }
    
    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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