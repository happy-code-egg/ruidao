<?php


namespace App\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SchoolsExport implements FromArray, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * 设置列宽
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 40, 'C' => 25, 'D' => 25
        ];
    }

    /**
     * 样式设置
     * @param Worksheet $sheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        // 数据行设置高
        for ($i = 1; $i <= count($this->data); $i++) {
            $sheet->getRowDimension($i)->setRowHeight(22);//设置高度
        }
        // 头部设置
        $sheet->getStyle('A1:D'.(count($this->data)))->getAlignment()->setVertical('center');//垂直居中
        $sheet->getStyle('A1:D'.(count($this->data)))->applyFromArray(['alignment' => ['horizontal' => 'center']]);//设置水平居中
        $sheet->getStyle('A1:D1')->applyFromArray(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']]]);//字体设置

        $sheet->getStyle('A1:D'.(count($this->data)))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    public function array(): array
    {
        return $this->data;
    }
}
