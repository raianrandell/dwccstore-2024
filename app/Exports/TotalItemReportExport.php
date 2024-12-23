<?php

namespace App\Exports;

use App\Models\TotalItemReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TotalItemReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $items;

    public function __construct()
    {
        // Fetch all items from the TotalItemReport model
        $this->items = TotalItemReport::orderBy('category_name')->get();
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        // Return the custom headings with the school name, address, and date
        return [
            ['DIVINE WORD COLLEGE OF CALAPAN'], // School Name
            ['Gov. Infantado St., Calapan City, Oriental Mindoro'], // Address
            ['COLLEGE BOOKSTORE INVENTORY AS OF ' . now()->format('F d, Y')], // Inventory Title
            [''], // Empty row for spacing
            [] // Return an empty array to skip column headings here
        ];
    }

    public function map($item): array
    {
        // Manage the category titles and item rows
        static $lastCategory = null;
        $currentCategory = $item->category_name;

        $rows = [];

        if ($lastCategory !== $currentCategory) {
            // Insert the category title in the format "CATEGORY: [NAME]"
            $rows[] = [
                'CATEGORY: ' . strtoupper($currentCategory), // Title for the category, formatted as "CATEGORY: [NAME]"
                '', // Leave other cells blank for formatting
                '',
                '',
                '',
                '',
                '',
            ];

            // Add the headings for the table
            $rows[] = [
                'ITEM NAME',
                'QUANTITY',
                'UNIT',
                'BASE PRICE',
                'SELLING PRICE',
                'TOTAL BASE PRICE',
                'TOTAL SELLING PRICE',
            ];

            $lastCategory = $currentCategory;
        }

        // Add the current item's data without the category column
        $rows[] = [
            $item->item_name,
            $item->quantity,
            $item->unit,
            number_format($item->base_price, 2),
            number_format($item->selling_price, 2),
            number_format($item->quantity * $item->base_price, 2), // Total Base Price
            number_format($item->quantity * $item->selling_price, 2), // Total Selling Price
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Align text to the left for all data rows
        $sheet->getStyle('A1:G' . $sheet->getHighestRow())
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Apply styles for the heading
        $sheet->mergeCells('A1:G1'); // Merge cells for the school name
        $sheet->mergeCells('A2:G2'); // Merge cells for the address
        $sheet->mergeCells('A3:G3'); // Merge cells for the inventory title

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14); // School name
        $sheet->getStyle('A2')->getFont()->setSize(12); // Address
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12); // Title

        // Center align the heading text
        $sheet->getStyle('A1:G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Apply styles for separate category tables
        $currentRow = 5; // Start from row 5 to account for the heading and spacing
        $highestRow = $sheet->getHighestRow();

        // Loop through each row to set the styles correctly
        while ($currentRow <= $highestRow) {
            $cellValue = $sheet->getCell('A' . $currentRow)->getValue();
            if (strpos($cellValue, 'CATEGORY:') === 0) {
                // This is a category title row, make it bold
                $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $currentRow)->getFont()->setSize(12); // Optional: Set the size for category title
                $currentRow++; // Move to the next row
                continue; // Skip the next iteration to avoid double counting
            }

            // If it's a header row, make it bold
            if ($cellValue === 'ITEM NAME') {
                $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->getFont()->setBold(true);
                $currentRow++; // Move to the next row
                continue;
            }

            // Apply borders only to the current table
            $borderStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'], // Black color
                    ],
                ],
            ];

            // Apply borders to the current category's range
            $nextRow = $currentRow + 1;
            $nextCellValue = $sheet->getCell('A' . $nextRow)->getValue();
            $lastRowOfCurrentCategory = $nextCellValue ? $nextRow - 1 : $currentRow; // Adjust if the next value is empty
            $sheet->getStyle('A' . $currentRow . ':G' . $lastRowOfCurrentCategory)->applyFromArray($borderStyle);

            // Add a space after each category table
            $sheet->getRowDimension($lastRowOfCurrentCategory + 1)->setRowHeight(15); // Add spacing row

            // Move to the next category
            $currentRow = $nextRow + 1; // Move to the next row for spacing
        }
    }
}
