<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMiddleware
{

	public function __invoke(Request $request, RequestHandler $handler): Response
	{
		global $di;
		$identityService = $di->identityService;

		$method = $request->getMethod();

		$response = $handler->handle($request);
		$route = $request->getUri()->getPath();

		// пропускаем авториазция в info
		if ($route == '/') {
			return $response;
		}

		// пропускаем авториазция в  identity
		if (str_contains($route, '/identity/')) {
			return $response;
		}

		$authorization = $request->getHeaderLine('Authorization');
		$session = $identityService->auth($authorization);

		if (is_null($session)) {
			$response = new Response();
			$response->getBody()->write('нет доступа');
			return $response->withStatus(401);
		}

		return $response;
	}
}