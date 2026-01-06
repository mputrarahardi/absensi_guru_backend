<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{
    /**
     * Get settings jadwal dari cache atau default value
     */
    private function getScheduleSettings()
    {
        return [
            'check_in_time' => Cache::get('check_in_time', '07:00'),
            'check_out_time' => Cache::get('check_out_time', '17:00'),
            'check_in_before_minutes' => (int) Cache::get('check_in_before_minutes', 30),
            'check_in_after_minutes' => (int) Cache::get('check_in_after_minutes', 30),
        ];
    }

    /**
     * Validasi jadwal check-in
     */
    private function validateCheckInSchedule()
    {
        $settings = $this->getScheduleSettings();
        $now = now();
        
        $checkInTime = \Carbon\Carbon::createFromFormat('H:i', $settings['check_in_time']);
        $checkInStart = $checkInTime->clone()->subMinutes((int) $settings['check_in_before_minutes']);
        $checkInEnd = $checkInTime->clone()->addMinutes((int) $settings['check_in_after_minutes']);
        
        if ($now->isBefore($checkInStart) || $now->isAfter($checkInEnd)) {
            return [
                'valid' => false,
                'message' => sprintf(
                    'Check-in hanya bisa dilakukan antara %s - %s',
                    $checkInStart->format('H:i'),
                    $checkInEnd->format('H:i')
                )
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validasi jadwal check-out
     */
    private function validateCheckOutSchedule()
    {
        $settings = $this->getScheduleSettings();
        $now = now();
        $checkOutTime = \Carbon\Carbon::createFromFormat('H:i', $settings['check_out_time']);
        
        if ($now->isBefore($checkOutTime)) {
            return [
                'valid' => false,
                'message' => sprintf(
                    'Check-out baru bisa dilakukan setelah %s',
                    $checkOutTime->format('H:i')
                )
            ];
        }

        return ['valid' => true];
    }

    /**
     * Tentukan jenis absensi (check-in atau check-out) berdasarkan attendance hari ini
     */
    private function determineAttendanceType($user)
    {
        $today = now()->toDateString();
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        return $existingAttendance ? 'check-out' : 'check-in';
    }

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

        // Tentukan tipe absensi (check-in atau check-out)
        $attendanceType = $this->determineAttendanceType($user);

        // Validasi jadwal sesuai tipe absensi
        if ($attendanceType === 'check-in') {
            $scheduleValidation = $this->validateCheckInSchedule();
            if (!$scheduleValidation['valid']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $scheduleValidation['message'],
                    'type' => 'schedule_invalid'
                ], 400);
            }
        } else {
            $scheduleValidation = $this->validateCheckOutSchedule();
            if (!$scheduleValidation['valid']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $scheduleValidation['message'],
                    'type' => 'schedule_invalid'
                ], 400);
            }
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

        // Simpan atau update attendance hanya jika match sukses
        $attendance = null;
        if ($data['match']) {
            if ($attendanceType === 'check-in') {
                // Buat record baru untuk check-in
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => now()->toDateString(),
                    'check_in' => now(),
                    'match' => $data['match'],
                    'distance' => $data['distance'],
                    'status' => 'success',
                ]);
            } else {
                // Update record yang sudah ada untuk check-out
                $attendance = Attendance::where('user_id', $user->id)
                    ->where('date', now()->toDateString())
                    ->first();
                
                if ($attendance) {
                    $attendance->update([
                        'check_out' => now(),
                        'status' => 'completed',
                    ]);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'match' => $data['match'],
            'distance' => $data['distance'],
            'attendance_type' => $attendanceType,
            'attendance' => $attendance
        ]);
    }

    public function today(Request $request)
    {
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $request->user()->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json(['data' => null], 200);
        }

        return response()->json(['data' => $attendance], 200);
    }

    public function history(Request $request)
    {
        $attendances = $request->user()->attendances()->orderByDesc('date')->get();
        return response()->json($attendances);
    }
}
