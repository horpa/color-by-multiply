<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

if (isset($_GET['practice'])) {
    render_practice_page(handle_practice_request());
    exit;
}

render_app_page(handle_app_request());
