<?
class IdentityInteractor {

    private \JWTService $jwtService;
    private \IdentityService $identityService;

    public function __construct(DI $di) { 
        $this->jwtService = $di->getJWTService();
        $this->identityService = $di->getIdentityService();
    }

    public function auth($authorization)
    {
        $accessToken = substr($authorization, 7);
        $session = $this->identityDataBase->getSession($accessToken);
        if ($session == null) {
            return null;
        }

        return $session;
    }

    public function login($request, $response) {
        $parsedBody = $request->getBody()->getContents();
		$body = json_decode($parsedBody);

        $user = $this->identityService->getUser($body->client_id, $body->login, $body->password);
        if ($user == null) {
            	return $response
				->withHeader('Content-Type', 'application/json')
				->withStatus(401);
        }

        $token = $this->jwtService->generateToken();

		// if ($token == null) {
		// 	$responseError = new \ResponseError(403, "Нет доступа!", "Логин или пароль неверный!");
		// 	$responseJson = json_encode($responseError->toJson());
		// 	$response->getBody()->write($responseJson);

		// 	return $response
		// 		->withHeader('Content-Type', 'application/json')
		// 		->withStatus(401);
		// }


		$responseJson = json_encode($token->toJson());
		$response->getBody()->write($responseJson);


        // $this->setup();

        // // if ($secretKey != $this->secretKey) {
        // //     return null;
        // // }

        // $user = $this->identityService->getUser($login);
        // if ($user == null) {
        //     return null;
        // }

        // $pass_verify = password_verify($password, $user['pass_hash']);
        // if (!$pass_verify) {
        //     return null;
        // }

        // $this->user = $user;

        // $token = $this->generateToken();
        // $this->identityDataBase->addSession($user['id'], $token->accessToken, $token->refreshToken, $token->expiresIn, $clientId, $secretKey);

        // return $token;

        return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus(200);
    }
}