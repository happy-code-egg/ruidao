<?php

namespace App\Http\Controllers\Api;

use App\Models\CommissionSettings;

class CommissionSettingsController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     * 
     * 功能说明：
     * - 返回佣金设置模型的完整类名
     * - 供父类BaseDataConfigController使用，用于实例化模型
     * - 实现父类的抽象方法，定义具体的业务模型
     * 
     * 返回参数：
     * - string：CommissionSettings模型的完整类名
     */
    protected function getModelClass()
    {
        return CommissionSettings::class;
    }

    /**
     * 获取验证规则
     * 
     * 功能说明：
     * - 定义提成设置数据的验证规则
     * - 支持创建和更新操作的数据验证
     * - 确保提成配置数据的完整性和准确性
     * - 验证处理人等级、案例类型、业务类型等关键字段
     * - 验证提成比例、积分等数值字段的合理性
     * 
     * 请求参数：
     * - handler_level (string): 处理人等级，必填，最大50字符（如：初级、中级、高级、专家级）
     * - case_type (string): 案例类型，必填，最大50字符（如：发明专利、实用新型、商标、版权、外观设计）
     * - business_type (string): 业务类型，必填，支持逗号分隔的字符串（如：申请业务、注册业务、维权业务）
     * - application_type (string): 申请类型，必填，支持逗号分隔的字符串（如：普通申请、加急申请、优先申请）
     * - case_coefficient (string): 案例系数，必填，支持逗号分隔的字符串（用于计算提成的案例难度系数）
     * - matter_coefficient (string): 事项系数，必填，支持逗号分隔的字符串（用于计算提成的事项复杂度系数）
     * - processing_matter (string): 处理事项，必填，支持逗号分隔的字符串（如：申请文件撰写、答复审查意见、商标注册）
     * - case_stage (string): 案例阶段，必填，最大50字符（如：申请阶段、审查阶段、注册阶段、登记阶段）
     * - commission_type (string): 提成类型，必填，最大50字符（如：按件提成、按比例提成、固定提成）
     * - piece_ratio (numeric): 按件提成比例，必填，数值范围0-100（百分比形式）
     * - piece_points (integer): 按件积分，必填，最小值0（用于积分制提成计算）
     * - country (string): 国家地区，必填，最大50字符（如：中国、美国、欧盟、日本）
     * - rate (numeric): 提成比例，可选，数值范围0-100（百分比形式，用于比例提成）
     * - status (integer): 状态，必填，枚举值0或1（0=禁用，1=启用）
     * - sort_order (integer): 排序序号，可选，最小值0（用于列表排序显示）
     * 
     * 响应参数：
     * - array: 验证规则数组，包含各字段的验证规则定义
     *   - 字段名 => 验证规则字符串（Laravel验证规则格式）
     *   - 支持必填验证、类型验证、长度验证、数值范围验证、枚举值验证
     * 
     * 内部业务逻辑：
     * - 基础验证：验证必填字段、数据类型、字符长度等基本规则
     * - 数值验证：验证提成比例、积分等数值字段的合理范围
     * - 枚举验证：验证状态字段的有效值（启用/禁用）
     * - 字符串验证：验证文本字段的最大长度限制
     * - 支持逗号分隔：部分字段支持多值输入（如业务类型、申请类型等）
     * - 创建更新兼容：同一规则适用于创建和更新操作
     * 
     * @param bool $isUpdate 是否为更新操作（当前未使用，保留扩展性）
     * @return array 验证规则数组
     */
    protected function getValidationRules($isUpdate = false)
    {
        return [
            'handler_level' => 'required|string|max:50',
            'case_type' => 'required|string|max:50',
            'business_type' => 'required|string', // 支持逗号分隔的字符串
            'application_type' => 'required|string', // 支持逗号分隔的字符串
            'case_coefficient' => 'required|string', // 支持逗号分隔的字符串
            'matter_coefficient' => 'required|string', // 支持逗号分隔的字符串
            'processing_matter' => 'required|string', // 支持逗号分隔的字符串
            'case_stage' => 'required|string|max:50',
            'commission_type' => 'required|string|max:50',
            'piece_ratio' => 'required|numeric|min:0|max:100',
            'piece_points' => 'required|integer|min:0',
            'country' => 'required|string|max:50',
            'rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'handler_level.required' => '处理人等级不能为空',
            'case_type.required' => '项目类型不能为空',
            'business_type.required' => '业务类型不能为空',
            'application_type.required' => '申请类型不能为空',
            'case_coefficient.required' => '项目系数不能为空',
            'matter_coefficient.required' => '处理事项系数不能为空',
            'processing_matter.required' => '处理事项不能为空',
            'case_stage.required' => '项目阶段不能为空',
            'commission_type.required' => '提成类型不能为空',
            'piece_ratio.required' => '按件比例不能为空',
            'piece_points.required' => '按件点数不能为空',
            'country.required' => '国家（地区）不能为空',
        ]);
    }

    /**
     * 获取提成设置列表
     * 
     * 功能说明：
     * - 重载父类的列表查询方法，支持提成设置数据的查询和筛选
     * - 支持关键词模糊搜索和精确字段筛选
     * - 提供分页功能，支持自定义页码和每页数量
     * - 按排序序号和ID进行排序，确保数据展示的一致性
     * - 适用于前端提成设置管理页面的数据展示
     * 
     * 请求参数：
     * - keyword (string): 关键词，可选，支持在多个字段中模糊匹配搜索
     * - handlerLevel (string): 处理人等级，可选，精确匹配筛选（如：初级、中级、高级、专家级）
     * - caseType (string): 案例类型，可选，精确匹配筛选（如：发明专利、实用新型、商标、版权）
     * - businessType (string): 业务类型，可选，精确匹配筛选（如：申请业务、注册业务、维权业务）
     * - status (integer): 状态，可选，精确匹配筛选（0=禁用，1=启用）
     * - page (integer): 页码，可选，默认为1，最小值为1
     * - limit (integer): 每页数量，可选，默认为15，范围1-100
     * 
     * 响应参数：
     * - success (boolean): 请求是否成功
     * - message (string): 响应消息
     * - data (object): 响应数据对象
     *   - list (array): 提成设置列表数据
     *     - id (integer): 记录ID
     *     - name (string): 配置名称
     *     - code (string): 配置代码
     *     - handler_level (string): 处理人等级
     *     - case_type (string): 案例类型
     *     - business_type (string): 业务类型
     *     - application_type (string): 申请类型
     *     - case_coefficient (string): 案例系数
     *     - matter_coefficient (string): 事项系数
     *     - processing_matter (string): 处理事项
     *     - case_stage (string): 案例阶段
     *     - commission_type (string): 提成类型
     *     - piece_ratio (decimal): 按件提成比例
     *     - piece_points (integer): 按件积分
     *     - country (string): 国家地区
     *     - rate (decimal): 提成比例
     *     - status (integer): 状态（0=禁用，1=启用）
     *     - sort_order (integer): 排序序号
     *     - description (string): 描述信息
     *     - created_at (string): 创建时间
     *     - updated_at (string): 更新时间
     *   - total (integer): 总记录数
     *   - page (integer): 当前页码
     *   - limit (integer): 每页数量
     *   - pages (integer): 总页数
     * 
     * @param \Illuminate\Http\Request $request HTTP请求对象
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function index(\Illuminate\Http\Request $request)
    {
        try {
            // 获取模型类并创建查询构建器
            $modelClass = $this->getModelClass();
            $query = $modelClass::query();

            // 关键词模糊搜索：在多个关键字段中进行模糊匹配
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('handler_level', 'like', "%{$keyword}%")      // 处理人等级模糊匹配
                      ->orWhere('case_type', 'like', "%{$keyword}%")         // 案例类型模糊匹配
                      ->orWhere('business_type', 'like', "%{$keyword}%")     // 业务类型模糊匹配
                      ->orWhere('application_type', 'like', "%{$keyword}%")  // 申请类型模糊匹配
                      ->orWhere('processing_matter', 'like', "%{$keyword}%") // 处理事项模糊匹配
                      ->orWhere('case_stage', 'like', "%{$keyword}%")        // 案例阶段模糊匹配
                      ->orWhere('commission_type', 'like', "%{$keyword}%")   // 提成类型模糊匹配
                      ->orWhere('country', 'like', "%{$keyword}%");          // 国家地区模糊匹配
                });
            }

            // 精确字段筛选：根据具体字段值进行精确匹配
            if ($request->filled('handlerLevel')) {
                $query->where('handler_level', $request->get('handlerLevel')); // 处理人等级精确筛选
            }
            if ($request->filled('caseType')) {
                $query->where('case_type', $request->get('caseType')); // 案例类型精确筛选
            }
            if ($request->filled('businessType')) {
                $query->where('business_type', $request->get('businessType')); // 业务类型精确筛选
            }
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status); // 状态精确筛选（支持0值）
            }

            // 分页参数处理：确保页码和每页数量在合理范围内
            $page = max(1, (int)$request->get('page', 1));           // 页码最小为1
            $limit = max(1, min(100, (int)$request->get('limit', 15))); // 每页数量范围1-100

            // 获取总记录数（用于计算总页数）
            $total = $query->count();

            // 执行分页查询：按排序序号和ID排序，确保数据展示的一致性
            $data = $query->orderBy('sort_order')  // 首先按排序序号排序
                         ->orderBy('id')           // 然后按ID排序（确保相同排序序号的记录顺序固定）
                         ->offset(($page - 1) * $limit) // 计算偏移量
                         ->limit($limit)                 // 限制返回数量
                         ->get();

            // 返回成功响应：包含列表数据和分页信息
            return json_success('获取列表成功', [
                'list' => $data,                    // 提成设置列表数据
                'total' => $total,                  // 总记录数
                'page' => $page,                    // 当前页码
                'limit' => $limit,                  // 每页数量
                'pages' => ceil($total / $limit)    // 总页数（向上取整）
            ]);

        } catch (\Exception $e) {
            // 异常处理：捕获所有异常并返回统一的错误响应
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取提成设置选项列表
     * 
     * 功能说明：
     * - 返回启用状态的提成设置数据，用于前端下拉选择组件
     * - 将多个关键字段组合成易读的显示名称
     * - 生成标准化的代码标识符，便于程序处理
     * - 只返回必要的字段（id、name、code），减少数据传输量
     * - 按排序序号和ID排序，确保选项顺序的一致性
     * 
     * 请求参数：
     * - 无需传入参数（获取所有启用的提成设置选项）
     * 
     * 响应参数：
     * - success (boolean): 请求是否成功
     * - message (string): 响应消息
     * - data (array): 选项数据数组
     *   - id (integer): 提成设置记录ID，用于关联和选择
     *   - name (string): 显示名称，格式为"处理人等级 / 案例类型 / 业务类型 / 处理事项"
     *   - code (string): 代码标识符，将显示名称转换为小写下划线格式，便于程序处理
     * 
     * 内部业务逻辑：
     * - 数据筛选：只获取启用状态（status=1）的提成设置记录
     * - 数据排序：按sort_order和id字段排序，确保选项顺序稳定
     * - 名称组装：将handler_level、case_type、business_type、processing_matter四个字段用" / "连接
     * - 代码生成：将组装的名称转换为标准化代码格式（小写+下划线）
     * - 字符处理：去除多余空格，替换特殊字符（空格、斜杠、破折号等）为下划线
     * - 异常处理：记录详细错误日志，返回用户友好的错误信息
     * 
     * @param \Illuminate\Http\Request $request HTTP请求对象（未使用）
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function options(\Illuminate\Http\Request $request)
    {
        try {
            // 获取模型类并查询启用且已排序的记录
            $modelClass = $this->getModelClass();
            $items = $modelClass::enabled()->ordered()->get(); // 只获取启用状态的记录，按排序规则排序

            // 数据转换：将数据库记录转换为前端选项格式
            $data = $items->map(function ($item) {
                // 组装显示名称：将关键字段用斜杠分隔组合成易读格式
                $name = trim(($item->handler_level ?? '') . ' / ' . ($item->case_type ?? '') . ' / ' . ($item->business_type ?? '') . ' / ' . ($item->processing_matter ?? ''));
                
                // 生成代码标识符：将显示名称标准化为程序可用的代码格式
                $code = strtolower(str_replace([' ', '／', '/', '—', '-'], '_', $name)); // 替换各种空格和分隔符为下划线
                
                return [
                    'id' => $item->id,      // 记录ID，用于数据关联
                    'name' => $name,        // 显示名称，用于前端展示
                    'code' => $code,        // 代码标识符，用于程序处理
                ];
            });

            return json_success('获取选项成功', $data);
        } catch (\Exception $e) {
            // 异常处理：记录详细错误信息到日志，便于问题排查
            log_exception($e, '获取提成配置选项失败');
            return json_fail('获取选项列表失败');
        }
    }
    
}