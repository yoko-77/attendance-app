<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAttendanceController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $formattedDate = now()->isoFormat('YYYY年M月D日(ddd)');
        $formattedTime = now()->format('H:i');

        return view('user.attendance-register', compact('user', 'formattedDate', 'formattedTime'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $status = $user->attendance_status;
        $todayRecord = $user->attendanceRecords()
            ->where('date', today()->toDateString())
            ->first();
        $action = $request->input('action');

        if ($action === 'clock_in' && $status === '勤務外') {
            $user->attendanceRecords()->create([
                'date' => today()->toDateString(),
                'clock_in' => now()->format('H:i'),
            ]);
        } elseif ($action === 'break_in' && $status === '出勤中') {
            $todayRecord->breaks()->create([
                'break_in' => now()->format('H:i'),
            ]);
        } elseif ($action === 'break_out' && $status === '休憩中') {
            $todayRecord->breaks()->whereNull('break_out')->first()->update([
                'break_out' => now()->format('H:i'),
            ]);
        } elseif ($action === 'clock_out' && $status === '出勤中') {
            $todayRecord->update([
                'clock_out' => now()->format('H:i'),
            ]);
        }

        return redirect('/attendance');
    }
}
