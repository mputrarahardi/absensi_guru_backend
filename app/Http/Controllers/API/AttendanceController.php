<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AttendanceController extends Controller
{
    public function verifyFace(Request $request)
    {
        $request->validate([
            'file' => 'required|file|image',
        ]);

        $user = $request->user();

        if (!$user->face_encoding) {
            return response()->json([
                'status' => 'error',
                'message' => 'Face not registered'
            ], 400);
        }

        // Kirim request ke FastAPI
        $response = Http::attach(
            'file',
            file_get_contents($request->file('file')->getRealPath()),
            $request->file('file')->getClientOriginalName()
        )->post('http://127.0.0.1:8001/verify', [
            'known_encoding' => json_encode($user->face_encoding),
        ]);

        $data = $response->json();

        // Safety check
        if (!isset($data['status']) || !isset($data['match']) || !isset($data['distance'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid response from Face Recognition service',
                'raw' => $data
            ], 500);
        }

        if ($data['status'] !== 'success') {
            return response()->json($data, 400);
        }

        // Simpan attendance hanya jika match sukses
        $attendance = null;
        if ($data['match']) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => now()->toDateString(),
                'check_in' => now(),
                'match' => $data['match'],
                'distance' => $data['distance'],
                'status' => 'success',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'match' => $data['match'],
            'distance' => $data['distance'],
            'attendance' => $attendance
        ]);
    }

    public function history(Request $request)
    {
        $attendances = $request->user()->attendances()->orderByDesc('date')->get();
        return response()->json($attendances);
    }
}
