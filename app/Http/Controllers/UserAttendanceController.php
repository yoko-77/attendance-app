<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
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

    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))->startOfMonth()
            : now()->startOfMonth();
        $previousMonth = $date->copy()->subMonthNoOverflow()->toDateString();
        $nextMonth = $date->copy()->addMonthNoOverflow()->toDateString();

        $attendanceRecords = Auth::user()->attendanceRecords()
            ->with('breaks')
            ->whereBetween('date', [$date->toDateString(), $date->copy()->endOfMonth()->toDateString()])
            ->get()
            ->keyBy('date');

        $formattedAttendanceRecords = collect(CarbonPeriod::create($date, $date->copy()->endOfMonth())->toArray())
            ->map(function ($currentDate) use ($attendanceRecords) {
                $record = $attendanceRecords->get($currentDate->toDateString());

                if ($record === null) {
                    return [
                        'id' => null,
                        'date' => $currentDate->isoFormat('MM/DD(ddd)'),
                        'clock_in' => null,
                        'clock_out' => null,
                        'total_break_time' => null,
                        'total_time' => null,
                    ];
                }

                return [
                    'id' => $record->id,
                    'date' => $currentDate->isoFormat('MM/DD(ddd)'),
                    'clock_in' => Carbon::parse($record->clock_in)->format('H:i'),
                    'clock_out' => $record->clock_out ? Carbon::parse($record->clock_out)->format('H:i') : null,
                    'total_break_time' => $record->total_break_time,
                    'total_time' => $record->total_time,
                ];
            });

        return view('user.user-attendance-list', compact('date', 'previousMonth', 'nextMonth', 'formattedAttendanceRecords'));
    }
}
