<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    /**
     * 初始化工作流数据
     * 包含14个预定义的工作流配置，每个流程包含8个节点
     */
    public function run()
    {
        $workflows = [
            [
                'id' => 1,
                'name' => '合同流程',
                'code' => 'CONTRACT_FLOW',
                'case_type' => '合同',
                'description' => '合同审批流程，只有第一个节点可选到客户资料记录的业务员的主管审核',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动节点', 'type' => '启动', 'description' => '合同流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审查', 'type' => '审核', 'description' => '主管审核合同内容和条款', 'assignee' => [2, 3], 'timeLimit' => 48, 'required' => true],
                    ['name' => '法务审查', 'type' => '审核', 'description' => '法务部门审查合同条款', 'assignee' => [4, 5], 'timeLimit' => 48, 'required' => true],
                    ['name' => '财务审查', 'type' => '审核', 'description' => '财务部门审查费用和付款条款', 'assignee' => [6, 7], 'timeLimit' => 48, 'required' => true],
                    ['name' => '最终审批', 'type' => '审核', 'description' => '高级管理层最终审批', 'assignee' => [1, 2], 'timeLimit' => 24, 'required' => true],
                    ['name' => '合同归档', 'type' => '处理', 'description' => '合同归档和备案', 'assignee' => [8], 'timeLimit' => 24, 'required' => false],
                    ['name' => '客户通知', 'type' => '处理', 'description' => '通知客户合同审批结果', 'assignee' => [9], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '合同流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => '立案流程(商版专)',
                'code' => 'CASE_SIMPLE_FLOW',
                'case_type' => '专利',
                'description' => '立案流程（商版专），根据合同的某个状态，使这里有可以开始处理的数据，也可以没有合同，直接发起',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '立案流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '客服核实', 'type' => '审核', 'description' => '客服核实', 'assignee' => [1, 2, 3], 'timeLimit' => 48, 'required' => true],
                    ['name' => '节点3', 'type' => '审核', 'description' => '节点3', 'assignee' => [2, 3, 4], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [3, 4, 5], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [4, 5, 6], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [5, 6, 7], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [6, 7, 8], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '立案流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => '立案流程（科服）',
                'code' => 'CASE_TECH_FLOW',
                'case_type' => '专利',
                'description' => '立案流程（科服），根据合同的某个状态，使这里有可以开始处理的数据，也可以没有合同，直接发起',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '立案流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '客服核实', 'type' => '审核', 'description' => '客服核实（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [1, 2, 3], 'timeLimit' => 48, 'required' => true],
                    ['name' => '科服部审核', 'type' => '审核', 'description' => '科服部审核', 'assignee' => [4, 5, 6], 'timeLimit' => 48, 'required' => true],
                    ['name' => '客服再核', 'type' => '审核', 'description' => '客服再核', 'assignee' => [1, 2, 3], 'timeLimit' => 24, 'required' => true],
                    ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [7, 8, 9], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [8, 9, 10], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [9, 10, 11], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '立案流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'name' => '配案流程',
                'code' => 'ASSIGN_FLOW',
                'case_type' => '通用',
                'description' => '配案流程，流动后，就给到某个人分配，分配出去以后就达到某个人的待处理界面',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '配案流程启动（严格上讲这里，没有单独启动，是其它地方发起直接到配案人的）', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管分配', 'type' => '分配', 'description' => '主管分配', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '节点3', 'type' => '审核', 'description' => '节点3', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '配案流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'name' => '核稿流程',
                'code' => 'CHECK_FLOW',
                'case_type' => '通用',
                'description' => '核稿流程，启动的时候，要选一个核稿人',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '核稿流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '核稿', 'type' => '检查', 'description' => '核稿', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '节点3', 'type' => '审核', 'description' => '节点3', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '核稿流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'name' => '递交流程',
                'code' => 'SUBMIT_FLOW',
                'case_type' => '通用',
                'description' => '递交流程，涉及到达流程初核这个节点，要记录日期的问题',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '递交流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '流程初核', 'type' => '审核', 'description' => '流程初核（到达这个节点，需要向这个处理事项的二次提交和一次提交写日期）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '流程复核', 'type' => '审核', 'description' => '流程复核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '递交官方', 'type' => '处理', 'description' => '递交官方（有用户和电脑网卡检查的白名单）', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '递交流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 7,
                'name' => '案件更新',
                'code' => 'CASE_UPDATE_FLOW',
                'case_type' => '通用',
                'description' => '案件更新流程，涉及到，如果是处理事项改成完成，不要要执行完成规则的问题',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '案件更新启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '流程审核', 'type' => '审核', 'description' => '流程审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '节点3', 'type' => '审核', 'description' => '节点3', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '案件更新结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 8,
                'name' => '请款',
                'code' => 'PAYMENT_FLOW',
                'case_type' => '财务',
                'description' => '请款流程，涉及，填写请款的时候，如果正在请款中，没有走完流程的，不能再次提交',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '请款流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '流程审核', 'type' => '审核', 'description' => '流程审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '请款流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 9,
                'name' => '收款',
                'code' => 'RECEIVE_FLOW',
                'case_type' => '财务',
                'description' => '收款流程',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '收款流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '节点3', 'type' => '审核', 'description' => '节点3', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '收款流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 10,
                'name' => '开票',
                'code' => 'INVOICE_FLOW',
                'case_type' => '财务',
                'description' => '开票流程，没有走完的流程，可以撤回和删除。流程走完以后，要把这个开票与收款单做关联',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '开票流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '财务开票', 'type' => '处理', 'description' => '财务开票', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '发起人确认', 'type' => '确认', 'description' => '发起人确认', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '开票流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 11,
                'name' => '支出',
                'code' => 'EXPENSE_FLOW',
                'case_type' => '财务',
                'description' => '支出流程，没有走完的流程，可以撤回和删除。流程走完，相就出款日期和出款单号记录到费用里面',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '支出流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '财务付款', 'type' => '处理', 'description' => '财务付款', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '支出流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 12,
                'name' => '缴费',
                'code' => 'PAY_FEE_FLOW',
                'case_type' => '财务',
                'description' => '缴费流程',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '缴费流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '财务付款', 'type' => '处理', 'description' => '财务付款', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '流程确认', 'type' => '确认', 'description' => '流程确认', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '缴费流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 13,
                'name' => '运营提成',
                'code' => 'OPERATION_COMMISSION_FLOW',
                'case_type' => '财务',
                'description' => '运营提成流程，没有走完的流程，可以撤回和删除。流程走完以后要添加提成的相应字段内容',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '运营提成流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '流程审核', 'type' => '审核', 'description' => '流程审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '财务审核', 'type' => '审核', 'description' => '财务审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '运营提成流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 14,
                'name' => '商务提成',
                'code' => 'BUSINESS_COMMISSION_FLOW',
                'case_type' => '财务',
                'description' => '商务提成流程，没有走完的流程，可以撤回和删除。流程走完以后要添加提成的相应字段内容',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '商务提成流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '流程审核', 'type' => '审核', 'description' => '流程审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '财务审核', 'type' => '审核', 'description' => '财务审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                    ['name' => '结束', 'type' => '结束', 'description' => '商务提成流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // 清空现有数据
        DB::table('workflows')->truncate();
        
        // 插入新数据
        foreach ($workflows as $workflow) {
            DB::table('workflows')->insert($workflow);
        }

        $this->command->info('工作流数据初始化完成！共创建了 ' . count($workflows) . ' 个工作流。');
    }
}
