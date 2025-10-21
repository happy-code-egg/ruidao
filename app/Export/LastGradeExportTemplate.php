<?php


namespace App\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LastGradeExportTemplate implements FromArray, WithStyles, WithColumnWidths
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
            'A' => 15, 'B' => 18, 'C' => 80, 'D' => 14, 'E' => 14, 'F' => 14, 'G' => 14, 'H' => 14, 'I' => 14, 'J' => 14, 'K' => 14, 'L' => 14, 'M' => 14, 'N' => 14, 'O' => 14, 'P' => 14, 'Q' => 14, 'R' => 14, 'S' => 14,
            'T' => 14, 'U' => 14, 'V' => 14, 'W' => 14, 'X' => 14, 'Y' => 14, 'Z' => 14, 'AA' => 14, 'AB' => 14, 'AC' => 14, 'AD' => 14, 'AE' => 14, 'AF' => 14, 'AG' => 14, 'AH' => 14, 'AI' => 14, 'AJ' => 14, 'AK' => 14, 'AL' => 14, 'AM' => 14,
            'AN' => 14, 'AO' => 14
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
        $sheet->mergeCells('A1:AN1'); // 合并
        // 设置第二行
        $sheet->getRowDimension(2)->setRowHeight(15);// 高度
        // 前面标题合并
        $sheet->mergeCells('A2:A3'); // 合并
        $sheet->mergeCells('B2:B3'); // 合并
        $sheet->mergeCells('C2:C3'); // 合并
        $sheet->mergeCells('D2:D3'); // 合并
        $sheet->mergeCells('E2:E3'); // 合并
        // 总分栏合并
        $sheet->mergeCells('AJ2:AJ3'); // 合并
        $sheet->mergeCells('AK2:AK3'); // 合并
        $sheet->mergeCells('AL2:AL3'); // 合并
        $sheet->mergeCells('AM2:AM3'); // 合并
        $sheet->mergeCells('AN2:AN3'); // 合并
        // 专家小分合并
        $sheet->mergeCells('F2:K2'); // 合并
        $sheet->mergeCells('L2:Q2'); // 合并
        $sheet->mergeCells('R2:W2'); // 合并
        $sheet->mergeCells('X2:AC2'); // 合并
        $sheet->mergeCells('AD2:AI2'); // 合并

        // 数据行设置高
        for ($i = 4; $i <= count($this->data); $i++) {
            $sheet->getRowDimension($i)->setRowHeight(22);//设置高度
        }
        // 头部设置
        $sheet->getStyle('A1:AN1')->getAlignment()->setVertical('center');//垂直居中
        $sheet->getStyle('A1:AN'.(count($this->data)))->applyFromArray(['alignment' => ['horizontal' => 'center']]);//设置水平居中
        $sheet->getStyle('A1:AN1')->applyFromArray(['font' => ['bold' => true, 'size' => 22, 'color' => ['rgb' => '000000']]]);//字体设置
        // 小标题设置
        $sheet->getStyle('A2:AN3')->getAlignment()->setVertical('center');//垂直居中
        $sheet->getStyle('A2:AN3')->applyFromArray(['alignment' => ['horizontal' => 'center']]);//设置水平居中
        $sheet->getStyle('A2:AN3')->applyFromArray(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']]]);//字体设置


        $sheet->getStyle('A1:AN'.(count($this->data)))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    public function array(): array
    {
        return $this->data;
    }
}
