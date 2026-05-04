<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Carbon\Carbon;
use Carbon\WeekDay;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\CalendarWidget as FilamentCalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class CalendarWidget extends FilamentCalendarWidget
{
    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;

    protected ?string $defaultEventClickAction = 'edit';

    protected WeekDay $firstDay = WeekDay::Monday;

    protected string|HtmlString|null|bool $heading = 'Calendario de Citas';

    protected static ?int $sort = 5;

    // ─── EVENTOS ────────────────────────────────────────────
    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        return Appointment::with(['customer', 'services'])
            ->whereDate('appointment_date', '>=', $info->start)
            ->whereDate('appointment_date', '<=', $info->end)
            ->get()
            ->map(function (Appointment $appointment) {

                $statusValue = $appointment->status instanceof \BackedEnum
                    ? $appointment->status->value
                    : (string) $appointment->status;

                $color = match ($statusValue) {
                    'Pendiente' => '#F59E0B',
                    'Completada' => '#10B981',
                    'Cancelada' => '#EF4444',
                    default => '#6366F1',
                };

                return CalendarEvent::make($appointment)
                    ->title($appointment->customer->name)
                    ->start($appointment->appointment_date)
                    ->end(
                        Carbon::parse($appointment->appointment_date)
                            ->addMinutes(
                                $appointment->services->sum('duration_minutes') ?: 60
                            )
                    )
                    ->backgroundColor($color)
                    ->extendedProps([
                        'status' => $appointment->status,
                        'services' => $appointment->services->pluck('name')->join(', '),
                    ]);
            });
    }
}
