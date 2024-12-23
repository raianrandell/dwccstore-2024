<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class SalesReportExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        $data = [];
        $totalSales = 0;

        foreach ($this->transactions as $transaction) {
            foreach ($transaction->items as $item) {
                // Fetch category name using the category ID
                $categoryName = $item->item->cat_id 
                    ? Category::find($item->item->cat_id)->category_name 
                    : 'All Categories';

                $data[] = [
                    'date_time' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'transaction_no' => $transaction->transaction_no,
                    'item_name' => $item->item_name,
                    'category' => $categoryName,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                    'payment_method' => ucfirst($transaction->payment_method),
                    'cashier_name' => $transaction->user->full_name ?? 'N/A',
                ];

                // Accumulate the total sales value
                $totalSales += $item->total;
            }
        }

        // Add the "Total Sales" row at the bottom
        $data[] = [
            'date_time' => 'Total Sales',
            'transaction_no' => '',
            'item_name' => '',
            'category' => '',
            'quantity' => '',
            'price' => '',
            'total' => $totalSales,
            'payment_method' => '',
            'cashier_name' => '',
        ];

        // Add the footer row with the report generation date
        $data[] = [
            'date_time' => 'Generation Date:',
            'transaction_no' => Carbon::now()->format('Y-m-d H:i:s'),
            'item_name' => '',
            'category' => '',
            'quantity' => '',
            'price' => '',
            'total' => '',
            'payment_method' => '',
            'cashier_name' => '',
        ];

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Date/Time',
            'Transaction Number',
            'Item Name',
            'Category',
            'Quantity',
            'Price',
            'Total',
            'Payment Method',
            'Cashier Name',
        ];
    }

    public function styles($sheet)
    {
        return [
            // Adjust the header style if needed
            1 => ['font' => ['bold' => true, 'size' => 12]],
            // Apply bold styling to the "Total Sales" row
            count($this->transactions) + 2 => ['font' => ['bold' => true, 'size' => 12]],
            // Apply bold styling to the "Generated On" footer row
            count($this->transactions) + 3 => ['font' => ['italic' => true, 'size' => 10]],
        ];
    }
}