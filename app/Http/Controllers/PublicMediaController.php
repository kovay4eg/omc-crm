<?php

namespace App\Http\Controllers;

use App\Support\MediaStorage;
use Symfony\Component\HttpFoundation\Response;

final class PublicMediaController extends Controller
{
    public function __invoke(string $path): Response
    {
        abort_unless(MediaStorage::isManagedPath($path), 404);

        return MediaStorage::response($path);
    }
}
