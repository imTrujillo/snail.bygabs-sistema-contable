<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\Filament\CalendarWidget as FilamentCalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\FetchInfo;          // ← nuevo import
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class CalendarWidget extends FilamentCalendarWidget
{
    protected CalendarViewType $calendarView      = CalendarViewType::DayGridMonth;
    protected ?string $defaultEventClickAction    = 'edit';
    protected \Carbon\WeekDay $firstDay           = \Carbon\WeekDay::Monday;
    protected string|HtmlString|null|bool $heading = 'Calendario de Citas';

    // ─── EVENTOS ────────────────────────────────────────────
    protected function getEvents(FetchInfo $info): Collection|array|Builder  // ← firma correcta
    {
        return Appointment::with(['customer', 'services'])  // ← customer, no customer
            ->whereDate('appointment_date', '>=', $info->start)
            ->whereDate('appointment_date', '<=', $info->end)
            ->get()
            ->map(function (Appointment $appointment) {

                $color = match ($appointment->status) {
                    'Pendiente'  => '#F59E0B',
                    'Completada' => '#10B981',
                    'Cancelada'  => '#EF4444',
                    default      => '#6366F1',
                };

                return CalendarEvent::make($appointment)
                    ->title($appointment->customer->name)   // ← customer
                    ->start($appointment->appointment_date)
                    ->end(
                        Carbon::parse($appointment->appointment_date)
                            ->addMinutes(
                                $appointment->services->sum('duration_minutes') ?: 60
                            )
                    )
                    ->backgroundColor($color)
                    ->extendedProps([
                        'status'   => $appointment->status,
                        'services' => $appointment->services->pluck('name')->join(', '),
                    ]);
            });
    }

    // ─── ACCIÓN CREAR ────────────────────────────────────────
    public function createAppointmentAction(): CreateAction
    {
        return CreateAction::make('createAppointment')
            ->model(Appointment::class)
            ->label('Nueva Cita')
            ->form([
                Select::make('customer_id')               // ← customer_id
                    ->relationship('customer', 'name')    // ← customer
                    ->label('Cliente')
                    ->searchable()
                    ->required(),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Técnica')
                    ->required(),

                Select::make('services')
                    ->relationship('services', 'name')
                    ->label('Servicios')
                    ->multiple()
                    ->required(),

                DateTimePicker::make('appointment_date')
                    ->label('Fecha y hora')
                    ->required(),

                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pendiente'  => 'Pendiente',
                        'completada' => 'Completada',
                        'cancelada'  => 'Cancelada',
                    ])
                    ->default('pendiente')
                    ->required(),

                Textarea::make('notes')
                    ->label('Notas')
                    ->nullable(),
            ]);
    }
}
