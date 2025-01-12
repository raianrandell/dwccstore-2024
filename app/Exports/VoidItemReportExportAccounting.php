<?php

namespace App\Exports;

use App\Models\VoidRecords;
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

class VoidItemReportExportAccounting implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    protected $startDate;
    protected $endDate;
    protected $itemName;
    protected $category;

    public function __construct($startDate, $endDate, $itemName = null, $category = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->itemName = $itemName;
        $this->category = $category;
    }

    public function collection()
    {
        // Build the query with necessary relationships and filters
        $query = VoidRecords::with(['items.category']);

        if ($this->startDate) {
            $query->whereDate('voided_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('voided_at', '<=', $this->endDate);
        }

        if ($this->itemName) {
            $query->where('item_name', 'like', '%' . $this->itemName . '%');
        }

        if ($this->category) {
            $query->whereHas('items.category', function ($q) {
                $q->where('category_name', 'like', '%' . $this->category . '%');
            });
        }

        // Fetch the filtered data and map it to the required format
        return $query->get()->map(function ($item) {
            return [
                'Date/Time' => Carbon::parse($item->voided_at)->format('m-d-Y h:i:s'),
                'Item Name' => $item->item_name,
                'Category' => $item->items->category->category_name ?? 'N/A',
                'Price' => $item->price ?? 0, // Replace 'price' with the actual price column name
                'Cashier Name' => $item->voided_by ?? 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return ['Date/Time', 'Item Name', 'Category', 'Price', 'Cashier Name'];
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

                // --- Headers ---
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'Divine Word College of Calapan');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', 'DWCC STORE: Sales and Inventory');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A3:E3');
                $sheet->setCellValue('A3', 'Void Item Report');
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- Filter Information ---
                $sheet->getStyle('A4:A6')->getFont()->setBold(true);

                // Date Range
                $dateRange = $this->startDate && $this->endDate
                    ? Carbon::parse($this->startDate)->format('m-d-Y') . ' - ' . Carbon::parse($this->endDate)->format('m-d-Y')
                    : 'All Dates';
                $sheet->setCellValue('A4', "Date Range:");
                $sheet->setCellValue('B4', $dateRange);

                // Item Name
                $itemNameFilter = $this->itemName ? $this->itemName : 'All Items';
                $sheet->setCellValue('A5', "Item Name:");
                $sheet->setCellValue('B5', $itemNameFilter);

                // Category
                $categoryFilter = $this->category ? $this->category : 'All Categories';
                $sheet->setCellValue('A6', "Category:");
                $sheet->setCellValue('B6', $categoryFilter);

                // --- Data Table Styling ---

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(25);

                // Header row
                $headerStyleArray = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '20c997']],
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN]],
                ];
                $sheet->getStyle('A7:E7')->applyFromArray($headerStyleArray);

                // Data rows
                $dataStyleArray = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
                $dataRange = 'A8:E' . ($sheet->getHighestRow());
                $sheet->getStyle($dataRange)->applyFromArray($dataStyleArray);

                // --- Footer ---
                $generatedBy = Auth::guard('accounting')->user()->full_name;
                $generationDate = Carbon::now()->format('F d, Y h:i:s a');
                $footerStartRow = $sheet->getHighestRow() + 2;

                $sheet->setCellValue("A$footerStartRow", "Generated By:");
                $sheet->setCellValue("B$footerStartRow", $generatedBy);

                $sheet->setCellValue("A" . ($footerStartRow + 1), "Generation Date:");
                $sheet->setCellValue("B" . ($footerStartRow + 1), $generationDate);

                // Footer styling
                $footerStyleArray = [
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ];
                $sheet->getStyle("A$footerStartRow:B" . ($footerStartRow + 1))->applyFromArray($footerStyleArray);
            },
        ];
    }
}