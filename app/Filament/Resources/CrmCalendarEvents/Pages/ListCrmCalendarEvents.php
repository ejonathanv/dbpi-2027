<?php

namespace App\Filament\Resources\CrmCalendarEvents\Pages;

use App\Filament\Resources\CrmCalendarEvents\CrmCalendarEventResource;
use App\Models\CrmCalendarEvent;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCrmCalendarEvents extends ListRecords
{
    protected static string $resource = CrmCalendarEventResource::class;

    protected string $view = 'filament.pages.crm-calendar-list';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo evento'),
        ];
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $month = now()->startOfMonth();
        $end = now()->endOfMonth();

        $events = CrmCalendarEvent::query()
            ->whereBetween('starts_at', [$month, $end])
            ->with(['account', 'contact', 'opportunity'])
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (CrmCalendarEvent $event): string => $event->starts_at->format('Y-m-d'));

        return [
            ...parent::getViewData(),
            'monthLabel' => $month->translatedFormat('F Y'),
            'eventsByDay' => $events,
        ];
    }
}
