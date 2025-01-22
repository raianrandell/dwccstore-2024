<?php

namespace App\Exports;

use App\Models\Category;
use Illuminate\Support\Collection;
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

class SalesReportExportAdmin implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    protected $transactions;
    protected $startDate;
    protected $endDate;
    protected $selectedCategory;
    protected $selectedPaymentMethod;
    protected $selectedItemName;
    protected $selectedCategoryId;
    protected $totalSales;

    /**
     * Constructor to initialize the export with necessary data and filters.
     *
     * @param Collection $transactions
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $selectedCategory
     * @param string|null $selectedPaymentMethod
     * @param string|null $selectedItemName
     * @param int|null $selectedCategoryId
     */
    public function __construct(
        Collection $transactions,
        $startDate = null,
        $endDate = null,
        $selectedCategory = null,
        $selectedPaymentMethod = null,
        $selectedItemName = null,
        $selectedCategoryId = null
    ) {
        $this->transactions = $transactions;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->selectedCategory = $selectedCategory;
        $this->selectedPaymentMethod = $selectedPaymentMethod;
        $this->selectedItemName = $selectedItemName;
        $this->selectedCategoryId = $selectedCategoryId;
        $this->totalSales = 0;
    }

    /**
     * Prepare the collection of data to be exported.
     *
     * @return Collection
     */
    public function collection()
    {
        $data = [];

        foreach ($this->transactions as $transaction) {
            foreach ($transaction->items as $item) {
                if ($item->item) {
                    $categoryName = $item->item->category
                        ? $item->item->category->category_name
                        : 'N/A';

                    // Apply both category and item name filters
                    $matchesCategory = !$this->selectedCategoryId || $item->item->cat_id == $this->selectedCategoryId;
                    $matchesItemName = !$this->selectedItemName || $item->item->item_name === $this->selectedItemName;

                    if ($matchesCategory && $matchesItemName) {
                        $data[] = [
                            'date_time'      => $transaction->created_at->format('m-d-Y h:i:s'),
                            'transaction_no' => $transaction->transaction_no,
                            'item_name'      => $item->item->item_name,
                            'category'       => $categoryName,
                            'quantity'       => $item->quantity,
                            'unit_of_measurement'  => $item->item->unit_of_measurement,
                            'price' => '₱' . number_format($item->price ?? 0, 2), // Add Peso sign and format price
                            'total' => '₱' . number_format($item->total ?? 0, 2), // Add Peso sign and format total
                            'payment_method' => ucfirst($transaction->payment_method),
                            'cashier_name'   => $transaction->user->full_name ?? 'N/A',
                        ];

                        $this->totalSales += $item->total;
                    }
                }
            }
        }

        return collect($data);
    }

    /**
     * Define the headings for the Excel sheet.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Date/Time',
            'Transaction Number',
            'Item Name',
            'Category',
            'Quantity',
            'Unit',
            'Price',
            'Total',
            'Payment Method',
            'Cashier Name',
        ];
    }

    /**
     * Specify the starting cell for the data table.
     *
     * @return string
     */
    public function startCell(): string
    {
        return 'A9'; // Data table starts from A9
    }

    /**
     * Register events to customize the Excel sheet after it's created.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- Headers ---
                // Merge Cells and Header Styling
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', 'Divine Word College of Calapan');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:I2');
                $sheet->setCellValue('A2', 'DWCC STORE: Sales and Inventory');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A3:I3');
                $sheet->setCellValue('A3', 'Sales Report');
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- Filter Information ---
                $sheet->getStyle('A4:B7')->getFont()->setBold(true);

                // Date Range
                $dateRange = $this->startDate && $this->endDate
                    ? Carbon::parse($this->startDate)->format('m-d-Y') . ' - ' . Carbon::parse($this->endDate)->format('m-d-Y')
                    : 'All Dates';
                $sheet->setCellValue('A4', "Date Range:");
                $sheet->setCellValue('B4', $dateRange);

                // Category
                $categoryFilter = $this->selectedCategory ?? 'All Categories';
                $sheet->setCellValue('A5', "Category:");
                $sheet->setCellValue('B5', $categoryFilter);

                // Payment Method
                $paymentMethodFilter = $this->selectedPaymentMethod ?? 'All Payment Methods';
                $sheet->setCellValue('A6', "Payment Method:");
                $sheet->setCellValue('B6', $paymentMethodFilter);

                // Item Name
                $itemNameFilter = $this->selectedItemName ?? 'All Items';
                $sheet->setCellValue('A7', "Item Name:");
                $sheet->setCellValue('B7', $itemNameFilter);

                $sheet->getStyle('B4:B7')->getFont()->setBold(false);

                // --- Data Table Styling ---
                // Column widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(20);
                $sheet->getColumnDimension('I')->setWidth(25);

                // Header row (Now in row 9)
                $headerStyleArray = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '20c997']],
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN]],
                ];
                $sheet->getStyle('A9:K9')->applyFromArray($headerStyleArray);

                // Data rows styling
                $dataStyleArray = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ];
                $dataRange = 'A9:K' . ($sheet->getHighestRow());
                $sheet->getStyle($dataRange)->applyFromArray($dataStyleArray);

                // --- Total Sales ---
                $footerStartRow = $sheet->getHighestRow() + 1;
                $sheet->setCellValue("F{$footerStartRow}", "Total Sales:");

                // Use the pre-calculated totalSales
                $sheet->setCellValue("G{$footerStartRow}", $this->totalSales);
                $sheet->getStyle("G{$footerStartRow}")->getNumberFormat()->setFormatCode('"₱"#,##0.00'); // Add peso sign

                $sheet->getStyle("F{$footerStartRow}:G{$footerStartRow}")->getFont()->setBold(true);

                // --- Footer ---
                $generatedBy = Auth::guard('admin')->check() ? Auth::guard('admin')->user()->full_name : 'N/A';
                $generationDate = Carbon::now()->format('F d, Y h:i:s a');
                $footerStartRow += 1; // Move two rows below the data

                $sheet->setCellValue("A{$footerStartRow}", "Generated By:");
                $sheet->setCellValue("B{$footerStartRow}", $generatedBy);

                $sheet->setCellValue("A" . ($footerStartRow + 1), "Generation Date:");
                $sheet->setCellValue("B" . ($footerStartRow + 1), $generationDate);

                // Footer styling
                $footerStyleArray = [
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ];
                $sheet->getStyle("A{$footerStartRow}:B" . ($footerStartRow + 1))->applyFromArray($footerStyleArray);
            },
        ];
    }
}
