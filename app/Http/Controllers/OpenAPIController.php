<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OpenAPIController extends Controller
{
    public function docs()
    {
        return view('docs.index');
    }

    public function asset($asset)
    {
        $path = storage_path('docs/' . $asset);

        abort_unless($path, 404, 'Asset not found');

        $mimeType = mime_content_type($path);

        return response()->file($path, [
            'Content-Type' => $mimeType
        ]);
    }
}
