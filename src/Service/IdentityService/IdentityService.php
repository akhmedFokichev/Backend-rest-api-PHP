<?php
class IdentityService
{
    private \DBMySqlProvider $provider;
    private \IdentityDataBaseSQL $dataBaseSQL;

    public function __construct(Config $config)
    {
        $this->provider = new DBMySqlProvider($config);
        $this->dataBaseSQL = new IdentityDataBaseSQL();
    }

    // private function setup()
    // {
    //     global $di;
    //     $this->identityDataBase = new \IdentityDataBase($di->dbService);
    //     $this->utilityService = $di->utilityService;

    //     $this->clientIds = $di->config->clientIds;
    //     $this->secretKey = $di->config->secretKey;
    // }


    public function registration($secretKey, $clientId, $login, $password) {

        if ($secretKey != $this->secretKey) {
            return null;
        }

        $user = $this->identityDataBase->getUser($login);
        if ($user != null) {
            return null;
        }

        $pass_hash = $this->utilityService->hash($password);
        $this->identityDataBase->addUser(0, $login, $pass_hash);

        $token = $this->login($secretKey, $clientId, $login, $password);

        return $token;
    }


    public function getUser($clientId, $login, $password) {
        // if ($secretKey != $this->secretKey) {
        //     return null;
        // }

        $sql = $this->dataBaseSQL->getUser($login);
        $user = $this->provider->getObject($sql);

        if ($user == null) {
            return null;
        }

        $pass_verify = password_verify($password, $user['pass_hash']);
        if (!$pass_verify) {
            return null;
        }

        // $token = $this->generateToken();
        // $this->identityDataBase->addSession($user['id'], $token->accessToken, $token->refreshToken, $token->expiresIn, $clientId, $secretKey);

        return $user;
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
}


class IdentityDataBaseSQL {

  public function __construct() { }

  public function getUser($login)
  {
    $sql = "SELECT * FROM `identity_users` WHERE `login` = '$login'";
    return $sql;
  }

  public function addUser($access_level, $login, $pass_hash)
  {
    $sql = "INSERT INTO `identity_users`(`access_level`, `login`, `pass_hash`, `created_at`, `updated_at`) VALUES ($access_level,'$login','$pass_hash',now(), now())";

    $this->dbService->executeSql($sql);
  }

  public function addSession($userId, $accessToken, $refreshToken, $expiresIn, $clientId, $secretKey)
  {
    $sql = "INSERT INTO `identity_session`(`user_id`, `access_token`, `refresh_token`, `expiresIn`, `client_id`, `secret_key`,`updated_at`, `created_at`) VALUES ($userId,'$accessToken','$refreshToken', $expiresIn, '$clientId', '$secretKey', now(), now())";

    $this->dbService->executeSql($sql);
  }

  public function updateSession($userId, $oldRefreshToken, $accessToken, $refreshToken, $expiresIn)
   {
    $sql = "UPDATE `identity_session` SET `access_token`='$accessToken',`refresh_token`='$refreshToken',`expiresIn`='$expiresIn',`updated_at`= now()
     WHERE `refresh_token` = '$oldRefreshToken' and `user_id` = $userId ";
     //var_dump($sql);
    $this->dbService->executeSql($sql);
  }

  public function isValidSession($accessToken, $refreshToken)
  {
    $sql = "SELECT * FROM `identity_session` WHERE `access_token` = '$accessToken' and `refresh_token` = '$refreshToken' ";
    return $this->dbService->getObject($sql);
  }

  public function getSession($accessToken)
  {
    $sql = "SELECT * FROM `identity_session` WHERE `access_token` = '$accessToken'";
    return $this->dbService->getObject($sql);
  }

  public function deleteToken($accessToken)
  {

  }
}