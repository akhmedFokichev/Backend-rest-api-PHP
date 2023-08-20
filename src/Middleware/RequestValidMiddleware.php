<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class RequestValidMiddleware {
	
  public function __invoke(Request $request, RequestHandler $handler): Response {
    	
    $contentType = $request->getHeaderLine('Content-Type');
    if ($contentType != 'application/json') {
      $response = new Response();
      $response->getBody()->write('error Content-Type');
      
     return $response->withStatus(400);
   	}
    
    	
     	$response = $handler->handle($request);
    	
    
        // $response = new Response();
        // $response->getBody()->write('BEFORE' . $existingContent);
    
       return $response;
    }
}