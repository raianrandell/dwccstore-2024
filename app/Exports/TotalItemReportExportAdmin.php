<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;

class TotalItemReportExportAdmin implements WithEvents
{
    protected $request;
    protected $itemsGrouped;
    protected $filters;
    protected $userFullName;
    protected $currentDate;
    protected $formattedDate;

    public function __construct($request, $itemsGrouped, $filters, $userFullName, $currentDate, $formattedDate)
    {
        $this->request = $request;
        $this->itemsGrouped = $itemsGrouped;
        $this->filters = $filters;
        $this->userFullName = $userFullName;
        $this->currentDate = $currentDate;
        $this->formattedDate = $formattedDate;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Define the border style
                $borderStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FFDDDDDD'],
                        ],
                    ],
                ];

                // Set Document Title
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'Divine Word College of Calapan');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Subtitle
                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A2', 'DWCC STORE: Sales and Inventory');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Report Title
                $sheet->mergeCells('A4:G4');
                $sheet->setCellValue('A4', 'COLLEGE BOOKSTORE INVENTORY');
                $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

                // As of Date
                $sheet->mergeCells('A5:G5');
                $sheet->setCellValue('A5', 'As of ' . $this->formattedDate); // Corrected variable name
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');

                // Add an empty cell below A5
                $sheet->setCellValue('A6', '');

                // Update filter start row to account for the empty row
                $filterStartRow = 7;

                // Filters Information
                $sheet->setCellValue('A' . $filterStartRow, 'Date Range:');
                $sheet->setCellValue('B' . $filterStartRow, $this->filters['date_range']);

                $sheet->setCellValue('A' . ($filterStartRow +1), 'Item Name:');
                $sheet->setCellValue('B' . ($filterStartRow +1), $this->filters['item_name']);

                $sheet->setCellValue('A' . ($filterStartRow +2), 'Category:');
                $sheet->setCellValue('B' . ($filterStartRow +2), $this->filters['category_name']);

                $sheet->setCellValue('A' . ($filterStartRow +3), 'Unit of Measurement:');
                $sheet->setCellValue('B' . ($filterStartRow +3), $this->filters['unit']);

                // Apply Bold Styling to Filter Labels
                $filterLabelRange = "A{$filterStartRow}:A" . ($filterStartRow +3);
                $sheet->getStyle($filterLabelRange)->getFont()->setBold(true);

                // Starting row for data
                $dataStartRow = $filterStartRow + 5;
                $currentRow = $dataStartRow;

                foreach ($this->itemsGrouped as $category => $items) {
                    // Category Header
                    $sheet->mergeCells("A{$currentRow}:G{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", "Category: {$category}");
                    $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(12);
                    $currentRow++;

                    // Add an empty row below the category header
                    $sheet->setCellValue("A{$currentRow}", ''); // This sets an empty cell in column A
                    $currentRow++;

                    // Table Headers
                    $headers = ['Item Name', 'Quantity', 'Unit', 'Base Price', 'Selling Price', 'Total Base Price', 'Total Selling Price'];
                    $sheet->fromArray($headers, null, "A{$currentRow}");
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getFill()
                          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                          ->getStartColor()->setARGB('FF20C997');

                    // **Apply Borders to Table Headers**
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray($borderStyle);

                    // Data Rows
                    $currentRow++;
                    $dataRowStart = $currentRow; // Keep track of where data starts
                    foreach ($items as $item) {
                        $sheet->setCellValue("A{$currentRow}", $item->item_name);
                        $sheet->setCellValue("B{$currentRow}", $item->qtyInStock);
                        $sheet->setCellValue("C{$currentRow}", $item->unit_of_measurement);
                        $sheet->setCellValue("D{$currentRow}", '₱' . number_format($item->base_price ?? 0, 2)); // Add Peso sign to base price
                        $sheet->setCellValue("E{$currentRow}", '₱' . number_format($item->selling_price ?? 0, 2)); // Add Peso sign to selling price
                        $sheet->setCellValue("F{$currentRow}", '₱' . number_format(($item->qtyInStock ?? 0) * ($item->base_price ?? 0), 2)); // Add Peso sign to base total
                        $sheet->setCellValue("G{$currentRow}", '₱' . number_format(($item->qtyInStock ?? 0) * ($item->selling_price ?? 0), 2)); // Add Peso sign to selling total
                        $currentRow++;
                    }                    
                    $dataRowEnd = $currentRow - 1; // Last data row

                    // **Apply Borders to Data Rows**
                    $sheet->getStyle("A{$dataRowStart}:G{$dataRowEnd}")->applyFromArray($borderStyle);

                    // Add a blank row after each category
                    $currentRow++;
                }

                // Signatures Section
                $signatureStartRow = $currentRow + 2;
                $sheet->setCellValue("A{$signatureStartRow}", "Prepared by:");
                $sheet->setCellValue("A" . ($signatureStartRow + 1), "\u{00A0}"); // Existing empty cell
                $sheet->setCellValue("A" . ($signatureStartRow + 2), "\u{00A0}"); // Another empty cell
                $sheet->setCellValue("A" . ($signatureStartRow + 3), "\u{00A0}");
                $sheet->setCellValue("A" . ($signatureStartRow + 4), "Cashier - College Bookstore");

                $verifiedRow = $signatureStartRow + 6; // Adjusted for extra empty cell
                $sheet->setCellValue("A{$verifiedRow}", "Verified by:");
                $sheet->setCellValue("A" . ($verifiedRow + 1), "\u{00A0}"); // Empty cell
                $sheet->setCellValue("A" . ($verifiedRow + 2), "MS. GRACE LUZON");
                $sheet->setCellValue("A" . ($verifiedRow + 3), "Head, Acctg Office");
                $sheet->getStyle("A" . ($verifiedRow + 2))->getFont()->setBold(true);

                $sheet->setCellValue("D{$signatureStartRow}", "Checked by:");
                $sheet->setCellValue("D" . ($signatureStartRow + 1), "\u{00A0}"); // Existing empty cell
                $sheet->setCellValue("D" . ($signatureStartRow + 2), "\u{00A0}"); // Another empty cell
                $sheet->setCellValue("D" . ($signatureStartRow + 3), "MARAFE O. OCAMPO");
                $sheet->setCellValue("D" . ($signatureStartRow + 4), "Purchasing Officer");
                $sheet->getStyle("D" . ($signatureStartRow + 3))->getFont()->setBold(true);

                $approvedRow = $verifiedRow; // Same as $verifiedRow
                $sheet->setCellValue("D{$approvedRow}", "Approved by:");
                $sheet->setCellValue("D" . ($approvedRow + 1), "\u{00A0}"); // Empty cell
                $sheet->setCellValue("D" . ($approvedRow + 2), "FR. JEROME A. ORMITA, SVD");
                $sheet->setCellValue("D" . ($approvedRow + 3), "VP for Finance");
                $sheet->getStyle("D" . ($approvedRow + 2))->getFont()->setBold(true);

                // Footer Information
                $footerRow = $approvedRow + 6;
                $sheet->setCellValue("A{$footerRow}", "Generated by: {$this->userFullName}");
                $sheet->setCellValue("A" . ($footerRow + 1), "Generation Date: {$this->currentDate}");
                $sheet->mergeCells("A{$footerRow}:G{$footerRow}");
                $sheet->mergeCells("A" . ($footerRow + 1) . ":G" . ($footerRow + 1));

                // Adjust column widths
                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Apply number formatting
                // Ensure that the currentRow points to the last data row
                $sheet->getStyle("D{$dataStartRow}:G{$currentRow}")
                      ->getNumberFormat()
                      ->setFormatCode('#,##0.00');
            },
        ];
    }
}
