<?php

class IdentityService
{

	private \HashService $hashService;
	private \IdentityDataBase $identityDataBase;

	private $clientIds;
	private $secretKey;

	public $user;

	public function __construct()
	{
	}

	private function setup()
	{
		global $di;
		$this->identityDataBase = new \IdentityDataBase($di->dbService);
		$this->hashService = $di->hashService;

		$this->clientIds = $di->config->clientIds;
		$this->secretKey = $di->config->secretKey;
	}


	public function auth($authorization)
	{
		// var_dump($authorization)

		// $this->person = new Person();

		// return $this->person;
	}


	public function registration($secretKey, $clientId, $login, $password)
	{
		$this->setup();

		if ($secretKey != $this->secretKey) {
			return null;
		}

		$user = $this->identityDataBase->getUser($login);
		if ($user != null) {
			return null;
		}

		$pass_hash = $this->hashService->hash($password);
		$this->identityDataBase->addUser(0, $login, $pass_hash);

		$token = $this->login($secretKey, $clientId, $login, $password);

		return $token;
	}


	public function login($secretKey, $clientId, $login, $password)
	{
		$this->setup();

		if ($secretKey != $this->secretKey) {
			return null;
		}

		$user = $this->identityDataBase->getUser($login);
		if ($user == null) {
			return null;
		}

		$pass_verify = password_verify($password, $user['pass_hash']);
		if (!$pass_verify) {
			return null;
		}

		$this->user = $user;

		$token = $this->generateToken();
		$this->identityDataBase->addSession($user['id'], $token->accessToken, $token->refreshToken, $token->expiresIn, $clientId, $secretKey);

		return $token;
	}

	public function refresh($login, $refreshToken)
	{
		$this->setup();

		$user = $this->identityDataBase->getUser($login);
		if ($user == null) {
			return null;
		}

		$token = $this->generateToken();
		$this->identityDataBase->updateSession($user['id'], $refreshToken, $token->accessToken, $token->refreshToken, $token->expiresIn);

		$session = $this->identityDataBase->isValidSession($token->accessToken, $token->refreshToken);
		if ($session == null) {
			return null;
		}

		return $token;
	}

	private function generateRandomString($length = 20)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[random_int(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	private function generateToken()
	{

		$dateHash = $this->hashService->hash(date('Y/m/d H:i:s'));
		$accessTokenHash = $this->hashService->hash($this->generateRandomString(24));
		$refreshTokenHash = $this->hashService->hash($this->generateRandomString(18));

		$accessToken = $dateHash . "w" . $accessTokenHash;
		$refreshToken = $refreshTokenHash . "q" . $dateHash;
		$expiresIn = strtotime("+1 day");

		$token = new Token($accessToken, $refreshToken, $expiresIn);

		return $token;
	}
}