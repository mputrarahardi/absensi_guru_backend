<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function registerFace(Request $request)
    {
        $request->validate([
            'file' => 'required|file|image',
        ]);

        $user = $request->user();

        $response = Http::attach(
            'file', file_get_contents($request->file('file')), $request->file('file')->getClientOriginalName()
        )->post('http://127.0.0.1:8001/register'); // FastAPI di port 8001

        $data = $response->json();

        if ($data['status'] !== 'success') {
            return response()->json($data, 400);
        }

        $user->update(['face_encoding' => $data['face_encoding']]);

        return response()->json(['status' => 'success', 'user' => $user]);
    }
}
