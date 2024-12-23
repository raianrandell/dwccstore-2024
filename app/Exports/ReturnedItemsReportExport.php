<?php

namespace App\Exports;

use App\Models\ReturnedItem; // Adjust model as necessary
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReturnedItemsReportExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    protected $itemName;

    public function __construct($itemName = null)
    {
        $this->itemName = $itemName;
    }

    public function collection()
    {
        $query = ReturnedItem::query();

        if ($this->itemName) {
            $query->where('item_name', $this->itemName);
        }

        return $query->get()->map(function ($item) {
            return [
                'Transaction Number' => $item->transaction_no,
                'Item Name' => $item->item_name,
                'Quantity Returned' => $item->return_quantity,
                'Reason' => $item->reason,
                'Replacement Item' => $item->replacement_item ?? 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Transaction Number',
            'Item Name',
            'Quantity Returned',
            'Reason',
            'Replacement Item',
        ];
    }

    public function startCell(): string
    {
        return 'A7'; // Start data from row 7
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Add dynamic headers
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'Returned Items Report');

                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', 'Filter: ' . ($this->itemName ?? 'All Items'));

                // Styling headers
                $sheet->getStyle('A1:A2')->getFont()->setBold(true);
                $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Adjust column widths
                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
