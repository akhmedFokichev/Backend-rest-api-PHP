<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class RequestValidMiddleware
{

  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    $method = $request->getMethod();
    $contentType = $request->getHeaderLine('Content-Type');

    if ($method === "GET") {
      return $handler->handle($request);
    }

    if ($method === "POST") {
      if ($contentType == 'application/json') {
        return $handler->handle($request);

      } else {
        $response = new Response();
        $response->getBody()->write('error Content-Type');
        return $response->withStatus(400);
      }
    }

    $response = new Response();
    $response->getBody()->write('Method Not Allowed');
    return $response->withStatus(400);
  }
}