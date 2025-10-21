-- 更新工作流数据库脚本
-- 将所有工作流程扩展到8个节点，并根据业务需求更新配置

-- 清空现有工作流数据
TRUNCATE TABLE workflows;

-- 插入更新后的工作流数据
INSERT INTO workflows (id, name, code, case_type, description, status, nodes, created_by, created_at, updated_at) VALUES
(1, '合同流程', 'CONTRACT_FLOW', '合同', '合同审批流程，只有第一个节点可选到客户资料记录的业务员的主管审核', 1, 
'[{"name":"启动节点","type":"启动","description":"合同流程启动","assignee":[],"timeLimit":8,"required":false},{"name":"主管审查","type":"审核","description":"主管审核，可配置，可不配置","assignee":[],"timeLimit":48,"required":true},{"name":"流程审查","type":"审核","description":"流程审查","assignee":[],"timeLimit":48,"required":true},{"name":"流程复核","type":"审核","description":"流程复核","assignee":[],"timeLimit":48,"required":true},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"合同流程结束","assignee":[],"timeLimit":8,"required":false}]', 
1, NOW(), NOW()),

(2, '立案流程(商版专)', 'CASE_SIMPLE_FLOW', '专利', '立案流程（商版专），根据合同的某个状态，使这里有可以开始处理的数据，也可以没有合同，直接发起', 1,
'[{"name":"启动","type":"启动","description":"立案流程启动","assignee":[],"timeLimit":8,"required":false},{"name":"客服核实","type":"审核","description":"客服核实","assignee":[],"timeLimit":48,"required":true},{"name":"节点3","type":"审核","description":"节点3","assignee":[],"timeLimit":24,"required":false},{"name":"节点4","type":"审核","description":"节点4","assignee":[],"timeLimit":24,"required":false},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"立案流程结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW()),

(3, '立案流程（科服）', 'CASE_TECH_FLOW', '专利', '立案流程（科服），根据合同的某个状态，使这里有可以开始处理的数据，也可以没有合同，直接发起', 1,
'[{"name":"启动","type":"启动","description":"立案流程启动","assignee":[],"timeLimit":8,"required":false},{"name":"客服核实","type":"审核","description":"客服核实（固定一个或几个人备选审核，流动转的时候选定一个）","assignee":[],"timeLimit":48,"required":true},{"name":"科服部审核","type":"审核","description":"科服部审核","assignee":[],"timeLimit":48,"required":true},{"name":"客服再核","type":"审核","description":"客服再核","assignee":[],"timeLimit":24,"required":true},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"立案流程结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW()),

(4, '配案流程', 'ASSIGN_FLOW', '通用', '配案流程，流动后，就给到某个人分配，分配出去以后就达到某个人的待处理界面', 1,
'[{"name":"启动","type":"启动","description":"配案流程启动（严格上讲这里，没有单独启动，是其它地方发起直接到配案人的）","assignee":[],"timeLimit":8,"required":false},{"name":"主管分配","type":"分配","description":"主管分配","assignee":[],"timeLimit":24,"required":true},{"name":"节点3","type":"审核","description":"节点3","assignee":[],"timeLimit":24,"required":false},{"name":"节点4","type":"审核","description":"节点4","assignee":[],"timeLimit":24,"required":false},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"配案流程结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW()),

(5, '核稿流程', 'CHECK_FLOW', '通用', '核稿流程，启动的时候，要选一个核稿人', 1,
'[{"name":"启动","type":"启动","description":"核稿流程启动","assignee":[],"timeLimit":8,"required":false},{"name":"核稿","type":"检查","description":"核稿","assignee":[],"timeLimit":48,"required":true},{"name":"节点3","type":"审核","description":"节点3","assignee":[],"timeLimit":24,"required":false},{"name":"节点4","type":"审核","description":"节点4","assignee":[],"timeLimit":24,"required":false},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"核稿流程结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW()),

(6, '递交流程', 'SUBMIT_FLOW', '通用', '递交流程，涉及到达流程初核这个节点，要记录日期的问题', 1,
'[{"name":"启动","type":"启动","description":"递交流程启动","assignee":[],"timeLimit":8,"required":false},{"name":"主管审核","type":"审核","description":"主管审核（固定一个或几个人备选审核，流动转的时候选定一个）","assignee":[],"timeLimit":48,"required":true},{"name":"流程初核","type":"审核","description":"流程初核（到达这个节点，需要向这个处理事项的二次提交和一次提交写日期）","assignee":[],"timeLimit":48,"required":true},{"name":"流程复核","type":"审核","description":"流程复核（固定一个或几个人备选审核，流动转的时候选定一个）","assignee":[],"timeLimit":48,"required":true},{"name":"递交官方","type":"处理","description":"递交官方（有用户和电脑网卡检查的白名单）","assignee":[],"timeLimit":24,"required":true},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"递交流程结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW()),

(7, '案件更新', 'CASE_UPDATE_FLOW', '通用', '案件更新流程，涉及到，如果是处理事项改成完成，不要要执行完成规则的问题', 1,
'[{"name":"启动","type":"启动","description":"案件更新启动","assignee":[],"timeLimit":8,"required":false},{"name":"流程审核","type":"审核","description":"流程审核","assignee":[],"timeLimit":24,"required":true},{"name":"节点3","type":"审核","description":"节点3","assignee":[],"timeLimit":24,"required":false},{"name":"节点4","type":"审核","description":"节点4","assignee":[],"timeLimit":24,"required":false},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"案件更新结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW()),

(8, '请款', 'PAYMENT_FLOW', '财务', '请款流程，涉及，填写请款的时候，如果正在请款中，没有走完流程的，不能再次提交', 1,
'[{"name":"启动","type":"启动","description":"请款流程启动","assignee":[],"timeLimit":8,"required":false},{"name":"主管审核","type":"审核","description":"主管审核（固定一个或几个人备选审核，流动转的时候选定一个）","assignee":[],"timeLimit":48,"required":true},{"name":"流程审核","type":"审核","description":"流程审核","assignee":[],"timeLimit":24,"required":true},{"name":"节点4","type":"审核","description":"节点4","assignee":[],"timeLimit":24,"required":false},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"请款流程结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW()),

(9, '收款', 'RECEIVE_FLOW', '财务', '收款流程', 1,
'[{"name":"启动","type":"启动","description":"收款流程启动","assignee":[],"timeLimit":8,"required":false},{"name":"主管审核","type":"审核","description":"主管审核（固定一个或几个人备选审核，流动转的时候选定一个）","assignee":[],"timeLimit":48,"required":true},{"name":"节点3","type":"审核","description":"节点3","assignee":[],"timeLimit":24,"required":false},{"name":"节点4","type":"审核","description":"节点4","assignee":[],"timeLimit":24,"required":false},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"收款流程结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW()),

(10, '开票', 'INVOICE_FLOW', '财务', '开票流程，没有走完的流程，可以撤回和删除。流程走完以后，要把这个开票与收款单做关联', 1,
'[{"name":"启动","type":"启动","description":"开票流程启动","assignee":[],"timeLimit":8,"required":false},{"name":"主管审核","type":"审核","description":"主管审核（固定一个或几个人备选审核，流动转的时候选定一个）","assignee":[],"timeLimit":48,"required":true},{"name":"财务开票","type":"处理","description":"财务开票","assignee":[],"timeLimit":48,"required":true},{"name":"发起人确认","type":"确认","description":"发起人确认","assignee":[],"timeLimit":24,"required":true},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"开票流程结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW()),

(11, '支出', 'EXPENSE_FLOW', '财务', '支出流程，没有走完的流程，可以撤回和删除。流程走完，相就出款日期和出款单号记录到费用里面', 1,
'[{"name":"启动","type":"启动","description":"支出流程启动","assignee":[],"timeLimit":8,"required":false},{"name":"主管审核","type":"审核","description":"主管审核（固定一个或几个人备选审核，流动转的时候选定一个）","assignee":[],"timeLimit":48,"required":true},{"name":"财务付款","type":"处理","description":"财务付款","assignee":[],"timeLimit":24,"required":true},{"name":"节点4","type":"审核","description":"节点4","assignee":[],"timeLimit":24,"required":false},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"支出流程结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW()),

(12, '缴费', 'PAY_FEE_FLOW', '财务', '缴费流程', 1,
'[{"name":"启动","type":"启动","description":"缴费流程启动","assignee":[],"timeLimit":8,"required":false},{"name":"主管审核","type":"审核","description":"主管审核（固定一个或几个人备选审核，流动转的时候选定一个）","assignee":[],"timeLimit":48,"required":true},{"name":"财务付款","type":"处理","description":"财务付款","assignee":[],"timeLimit":24,"required":true},{"name":"流程确认","type":"确认","description":"流程确认","assignee":[],"timeLimit":24,"required":true},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"缴费流程结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW()),

(13, '运营提成', 'OPERATION_COMMISSION_FLOW', '财务', '运营提成流程，没有走完的流程，可以撤回和删除。流程走完以后要添加提成的相应字段内容', 1,
'[{"name":"启动","type":"启动","description":"运营提成流程启动","assignee":[],"timeLimit":8,"required":false},{"name":"主管审核","type":"审核","description":"主管审核（固定一个或几个人备选审核，流动转的时候选定一个）","assignee":[],"timeLimit":48,"required":true},{"name":"流程审核","type":"审核","description":"流程审核","assignee":[],"timeLimit":24,"required":true},{"name":"财务审核","type":"审核","description":"财务审核","assignee":[],"timeLimit":48,"required":true},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"运营提成流程结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW()),

(14, '商务提成', 'BUSINESS_COMMISSION_FLOW', '财务', '商务提成流程，没有走完的流程，可以撤回和删除。流程走完以后要添加提成的相应字段内容', 1,
'[{"name":"启动","type":"启动","description":"商务提成流程启动","assignee":[],"timeLimit":8,"required":false},{"name":"主管审核","type":"审核","description":"主管审核（固定一个或几个人备选审核，流动转的时候选定一个）","assignee":[],"timeLimit":48,"required":true},{"name":"流程审核","type":"审核","description":"流程审核","assignee":[],"timeLimit":24,"required":true},{"name":"财务审核","type":"审核","description":"财务审核","assignee":[],"timeLimit":48,"required":true},{"name":"节点5","type":"审核","description":"节点5","assignee":[],"timeLimit":24,"required":false},{"name":"节点6","type":"审核","description":"节点6","assignee":[],"timeLimit":24,"required":false},{"name":"节点7","type":"审核","description":"节点7","assignee":[],"timeLimit":24,"required":false},{"name":"结束","type":"结束","description":"商务提成流程结束","assignee":[],"timeLimit":8,"required":false}]',
1, NOW(), NOW());
