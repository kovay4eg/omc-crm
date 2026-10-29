<?php

declare(strict_types=1);

http_response_code(410);
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Type: text/plain; charset=UTF-8');

echo 'Сторінка «Мрія» тимчасово недоступна.';
