<?php


namespace App\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SchoolPaperTemplate implements FromArray, WithStyles, WithColumnWidths
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
            'A' => 15, 'B' => 18, 'C' => 25, 'D' => 30, 'E' => 40, 'F' => 18, 'G' => 80, 'H' => 50
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
        $sheet->getRowDimension(1)->setRowHeight(40); // 高度
        $sheet->mergeCells('A1:H1'); // 合并

        // 数据行设置高
        for ($i = 2; $i <= count($this->data); $i++) {
            $sheet->getRowDimension($i)->setRowHeight(22);//设置高度
        }
        // 头部设置
        $sheet->getStyle('A1:H1')->getAlignment()->setVertical('center');//垂直居中
        $sheet->getStyle('A1:H'.(count($this->data)))->applyFromArray(['alignment' => ['horizontal' => 'center']]);//设置水平居中
        $sheet->getStyle('A1:H1')->applyFromArray(['font' => ['bold' => true, 'size' => 22, 'color' => ['rgb' => '000000']]]);//字体设置
        // 小标题设置
        $sheet->getStyle('A2:H2')->getAlignment()->setVertical('center');//垂直居中
        $sheet->getStyle('A2:H2')->applyFromArray(['alignment' => ['horizontal' => 'center']]);//设置水平居中
        $sheet->getStyle('A2:H2')->applyFromArray(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']]]);//字体设置


        $sheet->getStyle('A1:H'.(count($this->data) - 2))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    public function array(): array
    {
        return $this->data;
    }
}
