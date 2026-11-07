<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

require_post();
verify_csrf();
auth_logout();

redirect('/login.php');
