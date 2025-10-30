<?php

namespace App\Controller\Identity;

use IdentityInteractor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class IdentityController
{
	private \IdentityInteractor $interactor;

	private \Profile $profile;
	// init
	public function __construct()
	{
		global $di;
		$this->interactor = new IdentityInteractor($di);
	}

	// public

	public function info(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		global $di;

		$appName = $di->config->appName;
		$version = $di->config->version;
		$data = '{"app":"' . $appName . '","version":"' . $version . '"}';
		$response->getBody()->write($data);

		return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus(200);
	}


	public function registration(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		global $di;

		return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus(403);

		// $parsedBody = $request->getBody()->getContents();
		// $body = json_decode($parsedBody);

		// $token = $this->identityService->registration($body->secret_key, $body->client_id, $body->login, $body->password);

		// if ($token == null) {
		// 	$responseError = new \ResponseError(403, "Нет доступа!", "такой логин уже существует!");
		// 	$responseJson = json_encode($responseError->toJson());
		// 	$response->getBody()->write($responseJson);

		// 	return $response
		// 		->withHeader('Content-Type', 'application/json')
		// 		->withStatus(403);
		// }

		// $responseJson = json_encode($token->toJson());
		// $response->getBody()->write($responseJson);

		// return $response
		// 	->withHeader('Content-Type', 'application/json')
		// 	->withStatus(200);
	}


	public function login(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		return $this->interactor->login($request, $response);
	}

	public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		global $di;

		$this->authLogin('aaaaa', 'ssssss');

		return $response;
	}

	public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		global $di;

		$this->authLogin('aaaaa', 'ssssss');

		return $response;
	}

	public function refresh(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		global $di;

		$parsedBody = $request->getBody()->getContents();
		$body = json_decode($parsedBody);

		$token = $this->identityService->refresh($body->login, $body->refreshToken);;

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

}