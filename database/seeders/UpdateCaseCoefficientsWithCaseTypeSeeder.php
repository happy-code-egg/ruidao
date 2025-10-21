<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateCaseCoefficientsWithCaseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 为现有的项目系数记录添加项目类型
     * @return void
     */
    public function run()
    {
        // 获取现有的项目系数记录
        $coefficients = DB::table('case_coefficients')->get();
        
        if ($coefficients->isEmpty()) {
            // 如果没有现有记录，创建一些示例数据
            $sampleData = [
                ['name' => '发明专利系数', 'case_type' => '专利', 'sort' => 1],
                ['name' => '实用新型系数', 'case_type' => '专利', 'sort' => 2],
                ['name' => '外观设计系数', 'case_type' => '专利', 'sort' => 3],
                ['name' => '商标注册系数', 'case_type' => '商标', 'sort' => 4],
                ['name' => '商标续展系数', 'case_type' => '商标', 'sort' => 5],
                ['name' => '软件著作权系数', 'case_type' => '版权', 'sort' => 6],
                ['name' => '作品著作权系数', 'case_type' => '版权', 'sort' => 7],
                ['name' => '项目申报系数', 'case_type' => '科服', 'sort' => 8],
                ['name' => '项目验收系数', 'case_type' => '科服', 'sort' => 9],
            ];
            
            foreach ($sampleData as $data) {
                DB::table('case_coefficients')->insert([
                    'name' => $data['name'],
                    'case_type' => $data['case_type'],
                    'sort' => $data['sort'],
                    'is_valid' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            $this->command->info('已创建示例项目系数数据');
        } else {
            // 为现有记录随机分配项目类型
            $caseTypes = ['专利', '商标', '版权', '科服'];
            
            foreach ($coefficients as $coefficient) {
                // 根据名称智能分配项目类型
                $caseType = $this->determineCaseType($coefficient->name, $caseTypes);
                
                DB::table('case_coefficients')
                    ->where('id', $coefficient->id)
                    ->update([
                        'case_type' => $caseType,
                        'updated_at' => now(),
                    ]);
            }
            
            $this->command->info('已为现有项目系数记录添加项目类型');
        }
    }
    
    /**
     * 根据名称智能确定项目类型
     */
    private function determineCaseType($name, $caseTypes)
    {
        $name = strtolower($name);
        
        // 专利相关关键词
        if (strpos($name, '专利') !== false || 
            strpos($name, '发明') !== false || 
            strpos($name, '实用新型') !== false || 
            strpos($name, '外观') !== false) {
            return '专利';
        }
        
        // 商标相关关键词
        if (strpos($name, '商标') !== false || 
            strpos($name, '标识') !== false) {
            return '商标';
        }
        
        // 版权相关关键词
        if (strpos($name, '版权') !== false || 
            strpos($name, '著作权') !== false || 
            strpos($name, '软件') !== false || 
            strpos($name, '作品') !== false) {
            return '版权';
        }
        
        // 科服相关关键词
        if (strpos($name, '项目') !== false || 
            strpos($name, '申报') !== false || 
            strpos($name, '验收') !== false || 
            strpos($name, '科技') !== false) {
            return '科服';
        }
        
        // 默认随机分配
        return $caseTypes[array_rand($caseTypes)];
    }
}
