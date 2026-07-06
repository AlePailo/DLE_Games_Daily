<?php declare(strict_types = 1);

namespace App\Controller;

use App\Core\SessionManager;

abstract class BaseController {
    public function __construct(
        protected SessionManager $sessionManager
    ) {}
}