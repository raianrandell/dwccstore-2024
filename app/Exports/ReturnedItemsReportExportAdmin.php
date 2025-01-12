<?php

namespace App\Exports;

use App\Models\ReturnedItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReturnedItemsReportExportAdmin implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    protected $itemName;
    protected $startDate;
    protected $endDate;
    protected $categoryName;

    public function __construct($itemName = null, $startDate = null, $endDate = null, $categoryName = null)
    {
        $this->itemName = $itemName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->categoryName = $categoryName;
    }

    public function collection()
    {
        $query = ReturnedItem::with('item.category'); // Eager load item and category

        // Filter by item name
        if ($this->itemName) {
            $query->where('item_name', 'like', '%' . $this->itemName . '%');
        }

        // Filter by start date
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        // Filter by end date
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        // Filter by category through item
        if ($this->categoryName) {
            $query->whereHas('item.category', function ($q) {
                $q->where('category_name', 'like', '%' . $this->categoryName . '%');
            });
        }

        return $query->get()->map(function ($item) {
            return [
                'Transaction Number' => $item->transaction_no,
                'Item Name'          => $item->item_name,
                'Category'           => $item->item->category->category_name ?? 'N/A', // Include category
                'Quantity Returned'  => $item->return_quantity,
                'Reason'             => $item->reason,
                'Replacement Item'   => $item->replacement_item ?? 'N/A',
                'Date/Time'          => Carbon::parse($item->created_at)->format('m-d-Y h:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Transaction Number',
            'Item Name',
            'Category', // New heading
            'Quantity Returned',
            'Reason',
            'Replacement Item',
            'Date/Time',
        ];
    }

    public function startCell(): string
    {
        return 'A7'; // Start headers at row 7
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- Headers ---
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'Divine Word College of Calapan');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A2', 'DWCC STORE: Sales and Inventory');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A3:G3');
                $sheet->setCellValue('A3', 'Returned Items Report');
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- Filter Information ---
                // Apply bold styling to labels
                $sheet->getStyle('A4:A6')->getFont()->setBold(true);

                // Date Range
                if ($this->startDate && $this->endDate) {
                    $dateRange = Carbon::parse($this->startDate)->format('m-d-Y') . ' - ' . Carbon::parse($this->endDate)->format('m-d-Y');
                } elseif ($this->startDate) {
                    $dateRange = 'From ' . Carbon::parse($this->startDate)->format('m-d-Y');
                } elseif ($this->endDate) {
                    $dateRange = 'Up to ' . Carbon::parse($this->endDate)->format('m-d-Y');
                } else {
                    $dateRange = 'All Dates';
                }

                $sheet->setCellValue('A4', "Date Range:");
                $sheet->setCellValue('B4', $dateRange);

                // Item Name
                $itemNameFilter = $this->itemName ? $this->itemName : 'All Items';
                $sheet->setCellValue('A5', "Item Name:");
                $sheet->setCellValue('B5', $itemNameFilter);

                // Category
                $categoryNameFilter = $this->categoryName ? $this->categoryName : 'All Categories';
                $sheet->setCellValue('A6', "Category:");
                $sheet->setCellValue('B6', $categoryNameFilter);

                // --- Data Table Styling ---

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(25); // Category column
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(40);
                $sheet->getColumnDimension('F')->setWidth(30);
                $sheet->getColumnDimension('G')->setWidth(20);

                // Header row
                $headerRow = 'A7:G7';
                $headerStyleArray = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '20c997']],
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN]],
                ];
                $sheet->getStyle($headerRow)->applyFromArray($headerStyleArray);

                // Data rows
                $dataStyleArray = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
                $dataStartRow = 8;
                $dataEndRow = $sheet->getHighestRow();
                $dataRange = "A{$dataStartRow}:G{$dataEndRow}";
                $sheet->getStyle($dataRange)->applyFromArray($dataStyleArray);

                // --- Footer ---
                $generatedBy = Auth::guard('admin')->check() ? Auth::guard('admin')->user()->full_name : 'N/A';
                $generationDate = Carbon::now()->format('m-d-Y h:i:s a');
                $footerStartRow = $sheet->getHighestRow() + 2;

                $sheet->setCellValue("A{$footerStartRow}", "Generated By:");
                $sheet->setCellValue("B{$footerStartRow}", $generatedBy);

                $sheet->setCellValue("A" . ($footerStartRow + 1), "Generation Date:");
                $sheet->setCellValue("B" . ($footerStartRow + 1), $generationDate);

                // Footer styling
                $footerStyleArray = [
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ];
                $sheet->getStyle("A{$footerStartRow}:B" . ($footerStartRow + 1))->applyFromArray($footerStyleArray);
            },
        ];
    }
}
