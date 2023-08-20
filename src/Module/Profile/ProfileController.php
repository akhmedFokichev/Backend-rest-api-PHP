<?
namespace App\Module\Storage\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProfileController
{
	private \IdentityService $identityService;

	private \Profile $profile;
	// init
    public function __construct() {
      global $di;
      $this->identityService = $di->identityService;
	}
	

    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        // your code to access items in the container... $this->container->get('');
	
		global $di;
		var_dump($di);
		
        return $response;
    }
    
     public function add(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        // your code to access items in the container... $this->container->get('');
	
		global $di;
		var_dump($di);
		
        return $response;
    }
    
     public function getImage(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        // your code to access items in the container... $this->container->get('');
	
		global $di;
		var_dump($di);
		
        return $response;
    }
}