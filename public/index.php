<?php

require_once __DIR__ . '/../app/Helpers/Database.php';
require_once __DIR__ . '/../app/Helpers/Session.php';
require_once __DIR__ . '/../app/Helpers/Router.php';

Session::start();

$action = $_GET['action'] ?? 'inicio';
dispatch($action);