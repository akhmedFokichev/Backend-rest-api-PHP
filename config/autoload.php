<?php

// Config
require_once('../config/Config.php');

// Service
require_once('../src/Service/DBService.php');
require_once('../src/Service/HashService.php');
require_once('../src/Service/IdentityService.php');


// DI
require_once('../src/DI/DI.php');

//Module
require_once('../src/Module/Identity/IdentityDataBase.php');

//Middleware
require_once('../src/Middleware/RequestValidMiddleware.php');
require_once('../src/Middleware/AuthMiddleware.php');


//Model
require_once('../src/Model/ResponsError.php');
require_once('../src/Model/Person.php');
require_once('../src/Model/Profile.php');
