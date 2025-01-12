<?php

namespace App\Exports;

use App\Models\ExpiredItem;
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
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class ExpiredItemReportExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
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
        $query = ExpiredItem::query();

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        if ($this->itemName) {
            $query->where('item_name', $this->itemName);
        }

        if ($this->category) {
            $query->where('category', 'like', '%' . $this->category . '%');
        }

        return $query->get()->map(function ($item) {
            return [
                'Barcode Number' => "'" . $item->barcode,
                'Item Name' => $item->item_name,
                'Category' => $item->category ?? 'No Category',
                'Quantity' => $item->quantity,
                'Date Encoded' => $item->created_at->format('m-d-Y'),
                'Expiration Date' => Carbon::parse($item->expiration_date)->format('m-d-Y'),
            ];
        });
    }

    public function headings(): array
    {
        return ['Barcode Number', 'Item Name', 'Category', 'Quantity', 'Date Encoded', 'Expiration Date'];
    }

    public function startCell(): string
    {
        return 'A9'; // Adjusted start row to accommodate empty rows
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Header
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'Divine Word College of Calapan');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18)->setName('Calibri');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', 'DWCC STORE: Sales and Inventory');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->setName('Calibri');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A3:E3');
                $sheet->setCellValue('A3', 'Expired Item Report');
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14)->setName('Calibri');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Empty Row Below "Expired Item Report"
                $sheet->mergeCells('A4:E4');
                $sheet->setCellValue('A4', '');

                // Date Range
                $dateRangeLabel = 'Date Range: ';
                $dateRangeValue = $this->startDate && $this->endDate
                    ? Carbon::createFromFormat('Y-m-d', $this->startDate)->format('m-d-Y') . ' - ' . Carbon::createFromFormat('Y-m-d', $this->endDate)->format('m-d-Y')
                    : 'All Dates';
                $dateRangeRichText = new RichText();
                $dateRangeRichText->createTextRun($dateRangeLabel)->getFont()->setBold(true);
                $dateRangeRichText->createText($dateRangeValue);
                $sheet->mergeCells('A5:E5');
                $sheet->setCellValue('A5', $dateRangeRichText);

                // Item Name
                $itemNameLabel = 'Item Name: ';
                $itemNameValue = $this->itemName ?? 'All Items';
                $itemNameRichText = new RichText();
                $itemNameRichText->createTextRun($itemNameLabel)->getFont()->setBold(true);
                $itemNameRichText->createText($itemNameValue);
                $sheet->mergeCells('A6:E6');
                $sheet->setCellValue('A6', $itemNameRichText);

                // Category
                $categoryLabel = 'Category: ';
                $categoryValue = $this->category ?? 'All Categories';
                $categoryRichText = new RichText();
                $categoryRichText->createTextRun($categoryLabel)->getFont()->setBold(true);
                $categoryRichText->createText($categoryValue);
                $sheet->mergeCells('A7:E7');
                $sheet->setCellValue('A7', $categoryRichText);

                // Empty Row Below "Category"
                $sheet->mergeCells('A8:E8');
                $sheet->setCellValue('A8', '');

                // Table Header Styling
                $sheet->getStyle('A9:F9')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle('A9:F9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF20C997');
                $sheet->getStyle('A9:F9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A9:F9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Auto-size Columns
                foreach (range('A', 'F') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                  // --- Footer Styling ---
                  $generatedBy = Auth::guard('inventory')->check() ? Auth::guard('inventory')->user()->full_name : 'N/A';
                  $generationDate = Carbon::now()->format('F d, Y h:i:s a');
                  $footerStartRow = $sheet->getHighestRow() + 2;
  
                  $sheet->mergeCells('A' . $footerStartRow . ':B' . $footerStartRow);
                  $sheet->setCellValue('A' . ($footerStartRow), "Generated By: $generatedBy");
                  $sheet->mergeCells('A' . ($footerStartRow + 1) . ':B' . ($footerStartRow + 1));
                  $sheet->setCellValue('A' . ($footerStartRow + 1), "Generation Date: $generationDate");
  
                  $sheet->getStyle('A' . $footerStartRow . ':B' . ($footerStartRow + 1))
                      ->getFont()->setItalic(true)->setSize(10);
                  $sheet->getStyle('A' . ($footerStartRow) . ':B' . ($footerStartRow + 1))
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
}
