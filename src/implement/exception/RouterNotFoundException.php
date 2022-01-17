<?php

namespace iflow\Router\implement\exception;

class RouterNotFoundException extends \Exception {
    public function __construct() {
        parent::__construct('404 Not-Found', 404);
    }
}