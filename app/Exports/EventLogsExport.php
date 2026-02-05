<?php

namespace App\Exports;

use App\Models\EventLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventLogsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = EventLog::query()->with('user');

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Define the headings for the Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'User Name',
            'User Email',
            'Event Type',
            'Resource Type',
            'Resource ID',
            'Endpoint',
            'Method',
            'IP Address',
            'User Agent',
            'Created At',
        ];
    }

    /**
     * Map each event log row.
     *
     * @param  \App\Models\EventLog  $eventLog
     * @return array
     */
    public function map($eventLog): array
    {
        return [
            $eventLog->id,
            $eventLog->user->name ?? 'N/A',
            $eventLog->user->email ?? 'N/A',
            $eventLog->event_type,
            $eventLog->resource_type,
            $eventLog->resource_id ?? 'N/A',
            $eventLog->endpoint,
            $eventLog->method,
            $eventLog->ip_address ?? 'N/A',
            $eventLog->user_agent ?? 'N/A',
            $eventLog->created_at?->toDateTimeString(),
        ];
    }

    /**
     * Apply styles to the worksheet.
     *
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet  $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
