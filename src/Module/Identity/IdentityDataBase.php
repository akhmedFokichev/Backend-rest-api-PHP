<?php


class IdentityDataBase
{

  protected $dbService;

  public function __construct($dbService)
  {
    $this->dbService = $dbService;
  }

  public function getUser($login)
  {
    $sql = "SELECT * FROM `identity_users` WHERE `login` = '$login'";
    return $this->dbService->getObject($sql);
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

  public function deleteToken($accessToken)
  {

  }
}