<?php

declare(strict_types=1);

/**
 * Copy to app/config.local.php for local overrides.
 *
 * Set admin_key to a long random secret. With ?key=YOUR_SECRET on the
 * library page, Delete buttons appear and deletes are allowed.
 */
return [
    'admin_key' => '',
];
