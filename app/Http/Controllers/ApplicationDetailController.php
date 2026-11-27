<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApplicationDetailController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $applications = $user->applications()
            ->orderBy('application_date', 'desc')
            ->get();
        $formattedApplications = $applications->map(function ($application) {
            return [
                'id' => $application->id,
                'approval_status' => $application->approval_status,
                'date' => $application->new_date->format('Y/m/d'),
                'comment' => $application->comment,
                'application_date' => Carbon::parse($application->application_date)->format('Y/m/d'),
            ];
        });

        return view('user.user-application-list', compact('user', 'formattedApplications'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $attendanceRecord = $user->attendanceRecords()->with('breaks')->findOrFail($id);

        $pendingApplication = $attendanceRecord->applications()
            ->where('approval_status', '承認待ち')
            ->first();

        $date = Carbon::parse($attendanceRecord->date);

        $breaks = $attendanceRecord->breaks->sortBy('break_in')->values()->map(function ($breakTime) use ($pendingApplication) {
            $proposalBreak = $pendingApplication?->proposalBreaks->firstWhere('break_id', $breakTime->id);

            return [
                'break_in' => $this->formatTime($proposalBreak?->break_in ?? $breakTime->break_in),
                'break_out' => $this->formatTime($proposalBreak?->break_out ?? $breakTime->break_out),
            ];
        });

        if ($pendingApplication) {
            $addedBreaks = $pendingApplication->proposalBreaks->whereNull('break_id')->map(function ($proposalBreak) {
                return [
                    'break_in' => $this->formatTime($proposalBreak->break_in),
                    'break_out' => $this->formatTime($proposalBreak->break_out),
                ];
            });
            $breaks = $breaks->concat($addedBreaks)->values();
        }

        $data = [
            'id' => $attendanceRecord->id,
            'application' => $pendingApplication,
            'year' => $date->format('Y年'),
            'date' => $date->format('n月j日'),
            'clock_in' => $this->formatTime($pendingApplication?->new_clock_in ?? $attendanceRecord->clock_in),
            'clock_out' => $this->formatTime($pendingApplication?->new_clock_out ?? $attendanceRecord->clock_out),
            'breaks' => $breaks,
            'comment' => $pendingApplication?->comment ?? $attendanceRecord->comment,
        ];

        return view('user.user-detail', compact('user', 'data'));
    }

    public function showApplication($id)
    {
        $application = Auth::user()->applications()->findOrFail($id);

        return $this->show($application->attendance_record_id);
    }

    public function store(AttendanceRequest $request, $id)
    {
        $user = Auth::user();
        $attendanceRecord = $user->attendanceRecords()->with('breaks')->findOrFail($id);

        $hasPendingApplication = $attendanceRecord->applications()
            ->where('approval_status', '承認待ち')
            ->exists();

        if ($hasPendingApplication) {
            return redirect('/attendance/'.$attendanceRecord->id);
        }

        $breaks = $attendanceRecord->breaks->sortBy('break_in')->values();

        DB::transaction(function () use ($request, $user, $attendanceRecord, $breaks) {
            $application = $attendanceRecord->applications()->create([
                'user_id' => $user->id,
                'new_date' => $attendanceRecord->date,
                'new_clock_in' => $request->input('new_clock_in'),
                'new_clock_out' => $request->input('new_clock_out'),
                'approval_status' => '承認待ち',
                'application_date' => now(),
                'comment' => $request->input('comment'),
            ]);

            foreach ($request->input('new_break_in', []) as $index => $breakIn) {
                if ($breakIn === null) {
                    continue;
                }

                $application->proposalBreaks()->create([
                    'break_id' => $breaks->get($index)?->id,
                    'break_in' => $breakIn,
                    'break_out' => $request->input('new_break_out.'.$index),
                ]);
            }
        });

        return redirect('/attendance/'.$attendanceRecord->id);
    }

    private function formatTime(?string $time): ?string
    {
        return $time ? Carbon::parse($time)->format('H:i') : null;
    }
}
