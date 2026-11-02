<?php
// Vercel entry point. Static assets are served by Vercel (see vercel.json);
// every other request is handed to the same router used by `php -S`.
require dirname(__DIR__) . '/public/router.php';
