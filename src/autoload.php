<?php

// Config
require_once('../config/Config.php');

// Core
require_once('../Core/PhpCodeGen.php');

// Service
require_once('../src/Service/DBService.php');
require_once('../src/Service/UtilityService.php');
require_once('../src/Service/SessionService.php');

// DI
require_once('../src/DI/DI.php');

//Module
require_once('../src/Module/Identity/IdentityDataBase.php');
require_once('../src/Module/Identity/IdentityService.php');

require_once('../src/Module/Profile/ProfileDataBase.php');
require_once('../src/Module/Profile/ProfileService.php');

//Middleware
require_once('../src/Middleware/RequestValidMiddleware.php');
require_once('../src/Middleware/AuthMiddleware.php');
require_once('../src/Middleware/CorsMiddleware.php');


//Model
require_once('../src/Model/ResponseError.php');
require_once('../src/Model/Token.php');
require_once('../src/Model/Person.php');
require_once('../src/Model/Profile.php');