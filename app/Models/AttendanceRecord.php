<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'comment',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(BreakTime::class);
    }

    protected function totalBreakTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                $breakMinutes = $this->breakMinutes();
                if ($breakMinutes === null) {
                    return null;
                }

                return gmdate('H:i', $breakMinutes * 60);
            },
        )->shouldCache();
    }

    protected function totalTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                $breakMinutes = $this->breakMinutes();
                if ($this->clock_out === null || $breakMinutes === null) {
                    return null;
                }
                $workMinutes = Carbon::parse($this->clock_in)->diffInMinutes(Carbon::parse($this->clock_out)) - $breakMinutes;

                return gmdate('H:i', $workMinutes * 60);
            },
        )->shouldCache();
    }

    private function breakMinutes(): ?int
    {
        if ($this->breaks->whereNull('break_out')->isNotEmpty()) {
            return null;
        }

        return $this->breaks->sum(function ($breakTime) {
            return Carbon::parse($breakTime->break_in)->diffInMinutes(Carbon::parse($breakTime->break_out));
        });
    }
}
