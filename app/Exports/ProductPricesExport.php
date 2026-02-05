<?php

namespace App\Exports;

use App\Models\ProductPrice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductPricesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ProductPrice::with(['product', 'currency'])
            ->orderBy('product_id')
            ->orderBy('currency_id')
            ->get();
    }

    /**
     * Define the headings for the Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Product Name',
            'Currency Name',
            'Price',
        ];
    }

    /**
     * Map each product price row.
     *
     * @param  \App\Models\ProductPrice  $productPrice
     * @return array
     */
    public function map($productPrice): array
    {
        return [
            $productPrice->product->name ?? 'N/A',
            $productPrice->currency->name ?? 'N/A',
            number_format($productPrice->price, 2, '.', ''),
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
