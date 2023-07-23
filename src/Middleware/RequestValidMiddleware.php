<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class RequestValidMiddleware {
	
  public function __invoke(Request $request, RequestHandler $handler): Response
    {
    	
    	$response = $handler->handle($request);
    	
    	// $contentType = $request->getHeader('Content-Type');
    	
    	// var_dump($contentType);
    	
    	// if ($contentType != 'application/json') {
    	// 	         $response = new Response();
     //   			 $response->getBody()->write('не json');
        
    	// 		 return $response
					// ->withStatus(400);
	 
    	// }
    	
    	// $authorization = $request->getHeader('Authorization');
    	
    	// var_dump($authorization);
    		
        
     //   $existingContent = (string) $response->getBody();
    
        // $response = new Response();
        // $response->getBody()->write('BEFORE' . $existingContent);
    
        return $response;
    }
}