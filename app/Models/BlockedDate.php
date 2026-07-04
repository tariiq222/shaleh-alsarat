<?php

namespace App\Models;

use Database\Factories\BlockedDateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedDate extends Model
{
    /** @use HasFactory<BlockedDateFactory> */
    use HasFactory;

    protected $table = 'blocked_dates';

    protected $fillable = [
        'start_date',
        'end_date',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
        ];
    }

    /**
     * Check whether this block covers the given date range (overlap).
     */
    public function overlapsWith(string $startDate, string $endDate): bool
    {
        return ! ($this->end_date->lte($startDate) || $this->start_date->gte($endDate));
    }

    public function scopeOverlapping(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->where('start_date', '<', $endDate)
                ->where('end_date', '>', $startDate);
        });
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        $startOfMonth = now()->setYear($year)->setMonth($month)->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->setYear($year)->setMonth($month)->endOfMonth()->format('Y-m-d');

        return $query->where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth);
    }
}