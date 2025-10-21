<?php


namespace App\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReviewTotalScoreTemplate implements FromArray, WithStyles, WithColumnWidths, WithColumnFormatting
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
            'A' => 9, 'B' => 26, 'C' => 16, 'D' => 16, 'E' => 16
        ];
    }

    /**
     * 格式化列
     * @return array
     */
    public function columnFormats(): array
    {
        $format = NumberFormat::FORMAT_NUMBER_00;//金额保留两位小数
        return ['C' => $format];
    }

    /**
     * 样式设置
     * @param Worksheet $sheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        // 数据行设置高
        for ($i = 4; $i <= count($this->data); $i++) {
            $sheet->getRowDimension($i)->setRowHeight(24);//设置高度
        }
        // 头部设置
        $sheet->getRowDimension(2)->setRowHeight(51); // 高度
        $sheet->mergeCells('A2:D2'); // 合并
        $sheet->getStyle('A2:D2')->getAlignment()->setVertical('center');//垂直居中
        $sheet->getStyle('A2:D2')->getAlignment()->setWrapText(true);//自动换行
        $sheet->getStyle('A2:D2')->applyFromArray(['alignment' => ['horizontal' => 'center']]);//设置水平居中
        $sheet->getStyle('A2:D2')->applyFromArray(['font' => ['name' => '仿宋', 'size' => 18, 'color' => ['rgb' => '000000']]]);//字体设置

        // 标题设置
        $sheet->getStyle('A4:D4')->getAlignment()->setVertical('center');//垂直居中
        $sheet->getStyle('A4:D4')->applyFromArray(['alignment' => ['horizontal' => 'center']]);//设置水平居中
        $sheet->getStyle('A4:D4')->applyFromArray(['font' => ['name' => '宋体', 'bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']]]);//字体设置
        $sheet->getStyle('A4:D4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('FFF2CC');

        // 文本设置
        $sheet->getStyle('A5:D'.(count($this->data)))->getAlignment()->setVertical('center');//垂直居中
        $sheet->getStyle('A5:D'.(count($this->data)))->applyFromArray(['alignment' => ['horizontal' => 'center']]);//设置水平居中
        $sheet->getStyle('A5:D'.(count($this->data)))->applyFromArray(['font' => ['name' => '宋体','size' => 12, 'color' => ['rgb' => '000000']]]);//字体设置
        $sheet->getStyle('A4:D'.(count($this->data)))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    public function array(): array
    {
        return $this->data;
    }
}
