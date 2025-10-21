<?php

namespace App\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegionDataReportTemplate implements FromArray, WithStyles, WithTitle
{
    protected $data;
    protected $expert_count;
    protected $target_count;
    protected $front_col;
    protected $back_col;
    protected $name;

    public function __construct(array $data, $expert_count, $target_count, $front_col, $back_col, $name)
    {
        $this->data = $data;
        $this->expert_count = $expert_count;
        $this->target_count = $target_count;
        $this->front_col = $front_col;
        $this->back_col = $back_col;
        $this->name = $name;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function title(): string
    {
        return $this->name.'评估报告';
    }

    public function styles(Worksheet $sheet)
    {

        $total_col = $this->expert_count + 2;
        $totalRows = count($this->data);

        // 设置所有数据垂直居中
        $sheet->getStyle('A2:'.chr(64 + $total_col).($totalRows))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // 设置所有数据水平居中
        $sheet->getStyle('A2:'.chr(64 + $total_col).($totalRows))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 设置所有单元格默认样式
        $sheet->getDefaultRowDimension()->setRowHeight(25); // 默认行高
        
        // 设置列宽
        // $sheet->getColumnDimension('A')->setWidth(15);
        // $sheet->getColumnDimension('B')->setWidth(25);
        // $sheet->getColumnDimension('C')->setWidth(15);
        // $sheet->getColumnDimension('D')->setWidth(25);
        // $sheet->getColumnDimension('E')->setWidth(15);
        // $sheet->getColumnDimension('F')->setWidth(15);
        // $sheet->getColumnDimension('G')->setWidth(15);
        // $sheet->getColumnDimension('H')->setWidth(15);
        // $sheet->getColumnDimension('I')->setWidth(15);
        
        // 设置标题行行高和样式
        $sheet->getRowDimension(1)->setRowHeight(40);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->mergeCells('A1:'.chr(64 + $total_col).'1'); // 合并标题单元格
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        
        // 设置基本信息样式
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->mergeCells('A2:'.chr(64 + $total_col).'2');
        

        $sheet->mergeCells('B3:'.chr(64 + $this->front_col).'3');
        $sheet->mergeCells(chr(64 + $this->front_col + 2).'3:'.chr(64 + $total_col).'3');

        $sheet->mergeCells('B4:'.chr(64 + $this->front_col).'4');
        $sheet->mergeCells(chr(64 + $this->front_col + 2).'4:'.chr(64 + $total_col).'4');

        $sheet->mergeCells('B5:'.chr(64 + $this->front_col).'5');
        $sheet->mergeCells(chr(64 + $this->front_col + 2).'5:'.chr(64 + $total_col).'5');


        // 设置专家评分
        $sheet->getStyle('A6')->getFont()->setBold(true);
        $sheet->mergeCells('A6:'.chr(64 + $total_col).'6');
        
        // 设置专家评分表头样式
        // $expertHeaderRowIndex = 7;
        // $expertHeaderRange = 'A'.$expertHeaderRowIndex.':'.chr(64 + $total_col).$expertHeaderRowIndex;
        // $sheet->getStyle($expertHeaderRange)->applyFromArray($headerStyles);
        
         // 专家评分表最后一行
        $expertTableLastRow = 7 + $this->target_count + 1;

        // 设置分项评审意见
        $reviewTitleRow = $expertTableLastRow + 1;
        $sheet->getStyle('A'.$reviewTitleRow)->getFont()->setBold(true);
        $sheet->mergeCells('A'.$reviewTitleRow.':'.chr(64 + $total_col).$reviewTitleRow);
        
        // 设置评审意见表格样式和边框
        $reviewStartRow = $reviewTitleRow + 1;
        
        
        // 合并分项评审意见中的单元格
        $start_A = $reviewStartRow;
        $count = 0;
        
        foreach ($this->data as $index => $row_index) {
            if ($index < $reviewStartRow - 1) {
                continue;
            }
            
            $current_row = $index + 1;
            
            // 合并评审意见部分的单元格
            if (isset($this->data[$index - 1]) && $row_index[0] === $this->data[$index - 1][0]) {
                $count++;
            } else {
                // 上一组数据结束，需要合并
                if ($count > 0) {
                    $sheet->mergeCells('A' . $start_A . ':A' . ($start_A + $count));
                }
                $start_A = $current_row;
                $count = 0;
            }
        }
        
        // 处理最后一组单元格
        if ($count > 0) {
            $sheet->mergeCells('A' . $start_A . ':A' . ($start_A + $count));
        }

        // 合并分项评分
        for ($i = $reviewStartRow; $i <= $totalRows - 2; $i++) {
            if ($i == $totalRows - 2 - $this->expert_count) {
                $sheet->getStyle('A'.($totalRows - 2 - $this->expert_count))->getFont()->setBold(true);
                $sheet->mergeCells('A'.($totalRows - 2 - $this->expert_count).':'.chr(64 + $total_col).($totalRows - 2 - $this->expert_count));
            } else {
                $sheet->mergeCells('C' . $i . ':' . chr(64 + $total_col).$i);
            }
        }


        // 设置评审结论
        $sheet->getStyle('A'.($totalRows-1))->getFont()->setBold(true);
        $sheet->mergeCells('A'.($totalRows-1).':'.chr(64 + $total_col).($totalRows-1));
        
        // 设置最后一行评审结论样式
        if ($totalRows > 0) {
            $lastRow = $totalRows;
            $sheet->getStyle('A'.$lastRow)->getFont()->setBold(true);
            $sheet->mergeCells('B'.$lastRow.':'.chr(64 + $total_col).$lastRow);
        }

        // 设置所有边框
        $sheet->getStyle('A1:'.chr(64 + $total_col).($totalRows))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // 设置所有列宽
        for ($i = 1; $i <= $total_col; $i++) {
            $sheet->getColumnDimension(chr(64 + $i))->setWidth(20);
        }
        // 设置所有行高
        for ($i = 1; $i <= $totalRows; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(25);
        }
    }
}