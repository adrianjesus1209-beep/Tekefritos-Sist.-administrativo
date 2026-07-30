<?php

class LandingController {
    public static function index() {
        Session::start();
        require_once __DIR__ . '/../../public/views/landing/index.php';
    }
}