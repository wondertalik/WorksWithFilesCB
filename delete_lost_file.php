<?php
define('ROOT_DIR', dirname(__FILE__));

require_once(ROOT_DIR . "/include/config.php");
require_once(ROOT_DIR . "/include/constants.php");
require_once(ROOT_DIR . "/include/mysql_connect.php");
require_once(ROOT_DIR . "/include/sql_functions.php");
require_once(ROOT_DIR . "/include/functions1.php");
require_once(ROOT_DIR . "/include/functions2.php");
require_once(ROOT_DIR . "/bitmaster/dp/ua/util/DelLostFilesCB.php");


$delete = new \bitmaster\dp\ua\util\DelLostFilesCB(ROOT_DIR);
//Производим поиск полей в кб типа файл или изображение
$delete->getFieldList();
//удаляем Не существуют на диске, но существуют в базе
$delete->cleanLostDB();