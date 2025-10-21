<?php


namespace App\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CheckStudentExportTemplate implements FromArray, WithStyles, WithColumnWidths
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
            'A' => 15, 'B' => 20, 'C' => 80, 'D' => 18, 'E' => 18, 'F' => 18
        ];
    }

    /**
     * 样式设置
     * @param Worksheet $sheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        // 设置第一行
        $sheet->getRowDimension(1)->setRowHeight(23); // 高度
        $sheet->mergeCells('A1:F1'); // 合并
        // 数据行设置高
        for ($i = 2; $i <= count($this->data); $i++) {
            $sheet->getRowDimension($i)->setRowHeight(22);//设置高度
        }
        // 头部设置
        $sheet->getStyle('A1:F1')->getAlignment()->setVertical('center');//垂直居中
        $sheet->getStyle('A1:F'.(count($this->data)))->applyFromArray(['alignment' => ['horizontal' => 'center']]);//设置水平居中
        $sheet->getStyle('A1:F1')->applyFromArray(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']]]);//字体设置


        $sheet->getStyle('A1:F'.(count($this->data)))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    public function array(): array
    {
        return $this->data;
    }
}
