<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    // ⬇️ Injecte les variables MONGODB_URL / MONGODB_DB depuis Platform.sh
    require_once dirname(__DIR__).'/config/bootstrap_platform.php';

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
