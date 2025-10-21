<?php


namespace App\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReviewRegionDetailTemplate implements FromArray, WithStyles, WithColumnWidths
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
            'A' => 5, 'B' => 10, 'C' => 11, 'D' => 28, 'E' => 53, 'F' => 17, 'G' => 50, 'H' => 17, 'I' => 17
        ];
    }

//    /**
//     * 格式化列
//     * @return array
//     */
//    public function columnFormats(): array
//    {
//        $format = NumberFormat::FORMAT_NUMBER_00;//金额保留两位小数
//        return ['H' => $format, 'M' => $format];
//    }

    /**
     * 样式设置
     * @param Worksheet $sheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        // 数据行设置高
        for ($i = 5; $i <= count($this->data); $i++) {
            $sheet->getRowDimension($i)->setRowHeight(81);//设置高度
        }
        // 头部设置
        $sheet->getRowDimension(2)->setRowHeight(29); // 高度
        $sheet->mergeCells('A2:I2'); // 合并
        $sheet->getStyle('A2:I2')->getAlignment()->setVertical('center');//垂直居中
        $sheet->getStyle('A2:I2')->applyFromArray(['alignment' => ['horizontal' => 'center']]);//设置水平居中
        $sheet->getStyle('A2:I2')->applyFromArray(['font' => ['name' => '仿宋', 'size' => 18, 'color' => ['rgb' => '000000']]]);//字体设置

        // 标题设置
        $sheet->getRowDimension(4)->setRowHeight(43); // 高度
        $sheet->getStyle('A4:I4')->getAlignment()->setVertical('center');//垂直居中
        $sheet->getStyle('A4:I4')->applyFromArray(['alignment' => ['horizontal' => 'center']]);//设置水平居中
        $sheet->getStyle('A4:I4')->applyFromArray(['font' => ['name' => '宋体', 'bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']]]);//字体设置
        $sheet->getStyle('A4:I4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('FFF2CC');

        // 文本设置
        $sheet->getStyle('A5:I'.(count($this->data)))->getAlignment()->setVertical('center');//垂直居中
        $sheet->getStyle('A5:I'.(count($this->data)))->applyFromArray(['alignment' => ['horizontal' => 'center']]);//设置水平居中
        $sheet->getStyle('A5:I'.(count($this->data)))->applyFromArray(['font' => ['name' => '宋体','size' => 12, 'color' => ['rgb' => '000000']]]);//字体设置
        $sheet->getStyle('A4:I'.(count($this->data)))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A5:I'.(count($this->data)))->getAlignment()->setWrapText(true);//自动换行
        // 单独设立水平居左
        $sheet->getStyle('E5:E'.(count($this->data)))->applyFromArray(['alignment' => ['horizontal' => 'left']]);//设置水平居左
        $sheet->getStyle('G5:H'.(count($this->data)))->applyFromArray(['alignment' => ['horizontal' => 'left']]);//设置水平居左

        $count_data = count($this->data);
        $start_A = 5;
        $count = 0;
        $start_C = 5;
        $count1 = 0;
        foreach ($this->data as $index => $item) {
            if ($index < 5) {
                continue;
            }
            if ($item['index'] === $this->data[$index - 1]['index']) {
                $count++;
                if (($index + 1) === $count_data) {
                    $sheet->mergeCells('A' . $start_A . ':' . 'A' . ($start_A + $count));
                    $sheet->mergeCells('B' . $start_A . ':' . 'B' . ($start_A + $count));
                }
            } else {
                if ($start_A !== $index) {
                    $sheet->mergeCells('A' . $start_A . ':' . 'A' . ($start_A + $count));
                    $sheet->mergeCells('B' . $start_A . ':' . 'B' . ($start_A + $count));
                }
                $start_A = $index + 1;
                $count = 0;
            }
            if ($item['second_target'] === $this->data[$index - 1]['second_target']) {
                $count1++;
                if (($index + 1) === $count_data) {
                    $sheet->mergeCells('C' . $start_C . ':' . 'C' . ($start_C + $count1));
                    $sheet->mergeCells('G' . $start_C . ':' . 'G' . ($start_C + $count1));
                    $sheet->mergeCells('H' . $start_C . ':' . 'H' . ($start_C + $count1));
                    $sheet->mergeCells('I' . $start_C . ':' . 'I' . ($start_C + $count1));
                }
            } else {
                if ($start_C !== $index) {
                    $sheet->mergeCells('C' . $start_C . ':' . 'C' . ($start_C + $count1));
                    $sheet->mergeCells('G' . $start_C . ':' . 'G' . ($start_C + $count1));
                    $sheet->mergeCells('H' . $start_C . ':' . 'H' . ($start_C + $count1));
                    $sheet->mergeCells('I' . $start_C . ':' . 'I' . ($start_C + $count1));
                }
                $start_C = $index + 1;
                $count1 = 0;
            }
        }
    }

    public function array(): array
    {
        return $this->data;
    }
}
