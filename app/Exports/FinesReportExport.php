<?php

namespace App\Exports;

use App\Models\FinesHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FinesReportExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    protected $startDate;
    protected $endDate;
    protected $itemName;
    protected $condition;
    protected $paymentMethod;

    public function __construct($startDate, $endDate, $itemName = null, $condition = null, $paymentMethod = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->itemName = $itemName;
        $this->condition = $condition;
        $this->paymentMethod = $paymentMethod;
    }

    public function collection()
    {
        $query = FinesHistory::query();

        // Apply filters
        if ($this->startDate) {
            $query->whereDate('borrowed_date', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('borrowed_date', '<=', $this->endDate);
        }
        if ($this->itemName) {
            $query->where('item_borrowed', $this->itemName);
        }
        if ($this->condition) {
            $query->where('condition', $this->condition);
        }
        if ($this->paymentMethod) {
            $query->where('payment_method', $this->paymentMethod);
        }

        // Map data
        return $query->get()->map(function ($fine) {
            return [
                $fine->student_id,
                $fine->student_name,
                $fine->item_borrowed,
                $fine->borrowed_date ? Carbon::parse($fine->borrowed_date)->format('m-d-Y') : 'N/A',
                $fine->expected_return_date ? Carbon::parse($fine->expected_return_date)->format('m-d-Y') : 'N/A',
                $fine->days_late ?? 0,
                ucfirst($fine->condition),
                '₱' . number_format($fine->days_late * 10, 2), // Late Fee with Peso sign
                '₱' . number_format(abs($fine->days_late * 10 - $fine->fines_amount), 2), // Additional Fee with Peso sign
                $fine->payment_method,
                '₱' . number_format($fine->fines_amount, 2), // Total with Peso sign
                $fine->actual_return_date ? Carbon::parse($fine->actual_return_date)->format('m-d-Y') : 'N/A',
                $fine->cashier_name ?? 'Unknown',
            ];
        });
        
    }

    public function headings(): array
    {
        return [
            'ID Number',
            'Student Name',
            'Item Borrowed',
            'Borrowed Date',
            'Expected Return Date',
            'Days Late',
            'Condition',
            'Late Fee',
            'Additional Fee',
            'Mode of Payment',
            'Total',
            'Actual Return Date',
            'Cashier', 
        ];
    }

    public function startCell(): string
    {
        return 'A10'; // Start data from row 10 to accommodate headers and blank cell
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Add report headers
                $this->addReportHeaders($sheet);

                // Adjust column widths
                $this->adjustColumnWidths($sheet);

                // Style headers
                $this->styleHeaders($sheet);

                // Style data rows
                $this->styleDataRows($sheet);

                // Add footer
                $this->addFooter($sheet);
            },
        ];
    }

    private function addReportHeaders($sheet)
    {
        // Main title
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'Divine Word College of Calapan');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Subtitle
        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', 'DWCC STORE: Sales and Inventory');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Report title
        $sheet->mergeCells('A3:L3');
        $sheet->setCellValue('A3', 'Toga Fines Report');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Filters
        $dateRange = $this->startDate && $this->endDate
            ? Carbon::parse($this->startDate)->format('m-d-Y') . ' - ' . Carbon::parse($this->endDate)->format('m-d-Y')
            : 'All Dates';

        $sheet->setCellValue('A5', 'Date Range:');
        $sheet->setCellValue('B5', $dateRange);

        $sheet->setCellValue('A6', 'Item Borrowed:');
        $sheet->setCellValue('B6', $this->itemName ?? 'All Items');

        $sheet->setCellValue('A7', 'Condition:');
        $sheet->setCellValue('B7', $this->condition ? ucfirst($this->condition) : 'All Conditions');

        $sheet->setCellValue('A8', 'Mode of Payment:');
        $sheet->setCellValue('B8', $this->paymentMethod ?? 'All Methods');

        // Add blank cell below filters
        $sheet->mergeCells('A9:L9');
    }

    private function adjustColumnWidths($sheet)
        {
            $sheet->getColumnDimension('A')->setWidth(18); // ID Number
            $sheet->getColumnDimension('B')->setWidth(30); // Student Name
            $sheet->getColumnDimension('C')->setWidth(30); // Item Borrowed
            $sheet->getColumnDimension('D')->setWidth(15); // Borrowed Date
            $sheet->getColumnDimension('E')->setWidth(20); // Expected Return Date
            $sheet->getColumnDimension('F')->setWidth(10); // Days Late
            $sheet->getColumnDimension('G')->setWidth(15); // Condition
            $sheet->getColumnDimension('H')->setWidth(15); // Late Fee
            $sheet->getColumnDimension('I')->setWidth(15); // Additional Fee
            $sheet->getColumnDimension('J')->setWidth(20); // Mode of Payment
            $sheet->getColumnDimension('K')->setWidth(15); // Total
            $sheet->getColumnDimension('L')->setWidth(20); // Actual Return Date
            $sheet->getColumnDimension('M')->setWidth(20); // Cashier (New column)
        }


    private function styleHeaders($sheet)
    {
        $headerRange = 'A10:M10'; // Adjusted header range to match new start row
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '20c997']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
    }

    private function styleDataRows($sheet)
    {
        $dataRange = 'A11:M' . $sheet->getHighestRow();
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
    }

    private function addFooter($sheet)
{
    $footerStartRow = $sheet->getHighestRow() + 2;

    // Generated By
    $generatedBy = Auth::guard('cashier')->user()->full_name ?? 'N/A';
    $generationDate = Carbon::now()->format('F d, Y h:i:s a');

    $sheet->setCellValue("A{$footerStartRow}", 'Generated By:');
    $sheet->setCellValue("B{$footerStartRow}", $generatedBy);

    $sheet->setCellValue("A" . ($footerStartRow + 1), 'Generation Date:');
    $sheet->setCellValue("B" . ($footerStartRow + 1), $generationDate);

    // Display Total Fines
    $totalFinesRow = $footerStartRow + 2;
    $sheet->mergeCells("A{$totalFinesRow}:H{$totalFinesRow}"); // Merge cells for the label
    $sheet->setCellValue("A{$totalFinesRow}", 'Total Fines:');
    $sheet->setCellValue("I{$totalFinesRow}", '₱' . number_format($this->getTotalFines(), 2)); // Display total fines in column I

    // Style the Total Fines row
    $sheet->getStyle("A{$totalFinesRow}:I{$totalFinesRow}")->applyFromArray([
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
    ]);
}

private function getTotalFines()
    {
        $query = FinesHistory::query();

        // Apply filters
        if ($this->startDate) {
            $query->whereDate('borrowed_date', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('borrowed_date', '<=', $this->endDate);
        }
        if ($this->itemName) {
            $query->where('item_borrowed', $this->itemName);
        }
        if ($this->condition) {
            $query->where('condition', $this->condition);
        }
        if ($this->paymentMethod) {
            $query->where('payment_method', $this->paymentMethod);
        }

        return $query->sum('fines_amount');
    }

}
