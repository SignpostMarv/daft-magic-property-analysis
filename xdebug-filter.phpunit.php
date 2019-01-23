<?php

declare(strict_types=1);
if (
    ! \function_exists('xdebug_set_filter') ||
    ! defined('XDEBUG_FILTER_CODE_COVERAGE') ||
    ! defined('XDEBUG_PATH_WHITELIST')
) {
    return;
}

\xdebug_set_filter(
    constant('XDEBUG_FILTER_CODE_COVERAGE'),
    constant('XDEBUG_PATH_WHITELIST'),
    [
        __DIR__ . '/src/',
        __DIR__ . '/Tests/DefinitionAssistant.php',
    ]
);
