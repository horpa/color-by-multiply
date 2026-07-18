<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

if (isset($_GET['preview'])) {
    render_worksheet_preview((string) $_GET['preview']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_worksheet'])) {
    handle_delete_worksheet_post();
}

if (isset($_GET['student_guide'])) {
    render_student_guide_page(handle_student_guide_request());
    exit;
}

if (isset($_GET['practice'])) {
    render_practice_page(handle_practice_request());
    exit;
}

if (isset($_GET['w'])) {
    render_saved_worksheet_page(handle_saved_worksheet_request());
    exit;
}

render_app_page(handle_app_request());
