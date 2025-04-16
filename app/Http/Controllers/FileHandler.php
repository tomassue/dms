<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;

class FileHandler extends Controller
{
    public function viewFile(Request $request, $id)
    {
        try {
            // Check if the URL is valid (signed URL)
            if (! $request->hasValidSignature()) {
                abort(403, 'Invalid or expired URL');
            }

            // Retrieve the file from the database
            $file = File::findOrFail($id);

            // Return the file as a response
            return response($file->file)
                ->header('Content-Type', $file->type) // Set the mime type (image/png, image/jpeg, application/pdf, etc.)
                ->header('Content-Disposition', 'inline'); // 'inline' tells the browser to display it in a new tab

        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 404);
        }
    }
}
