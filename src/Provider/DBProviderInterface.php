<?php
interface DBProviderInterface {
  public function executeSql($sql);
}