<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 公开路由（无需认证）
Route::group(['namespace' => 'Api'], function () {
    // 用户认证
    Route::post('/login', 'AuthController@login')->name('login');// 登录

        // 基础数据接口（无需特殊权限）
        // 获取部门列表接口 - 返回启用状态的部门信息，用于下拉选择组件，包含部门ID和名称
        Route::get('/departments/simple', 'UserController@getDepartments')->name('api.departments.simple');
        // 获取所有角色列表接口 - 返回系统中所有角色信息，用于用户角色分配和权限管理，包含角色ID和名称
        Route::get('/roles/all', 'RoleController@getAllRoles')->name('api.roles.all');

    // 代理机构管理（临时移到公开路由用于测试）
    // 获取代理机构列表接口 - 支持按机构名称、国家、有效性筛选，返回分页数据
    Route::get('/agencies', 'AgencyController@index')->name('api.agencies.index');
    // 获取国家选项接口 - 返回预定义的国家列表，用于代理机构管理的下拉选择
    Route::get('/agencies/countries', 'AgencyController@getCountries')->name('api.agencies.countries');
    // 创建代理机构接口 - 新增代理机构信息，包含中英文名称、国家、联系方式等完整信息
    Route::post('/agencies', 'AgencyController@store')->name('api.agencies.store');
    // 获取代理机构详情接口 - 根据ID获取单个代理机构的完整信息
    Route::get('/agencies/{id}', 'AgencyController@show')->name('api.agencies.show');
    // 更新代理机构接口 - 修改指定代理机构的信息，支持部分字段更新
    Route::put('/agencies/{id}', 'AgencyController@update')->name('api.agencies.update');
    // 删除代理机构接口 - 根据ID删除指定的代理机构记录
    Route::delete('/agencies/{id}', 'AgencyController@destroy')->name('api.agencies.destroy');

    // 工作流管理（临时移到公开路由用于测试）
    // 获取工作流列表接口 - 支持分页、搜索和项目类型筛选，返回工作流基本信息
    Route::get('/workflows', 'WorkflowController@index')->name('api.workflows.index');
    // 获取项目类型选项接口 - 返回可用的项目类型列表，用于工作流配置下拉框
    Route::get('/workflows/case-types', 'WorkflowController@getCaseTypes')->name('api.workflows.case.types');
    // 获取可分配用户列表接口 - 返回按部门分组的用户列表，用于工作流节点人员配置
    Route::get('/workflows/assignable-users', 'WorkflowController@getAssignableUsers')->name('api.workflows.assignable.users');
    // 创建工作流接口 - 新建工作流，需要提供名称、代码、项目类型和节点配置信息
    Route::post('/workflows', 'WorkflowController@store')->name('api.workflows.store');
    // 获取工作流详情接口 - 根据ID获取指定工作流的完整信息，包括节点配置
    Route::get('/workflows/{id}', 'WorkflowController@show')->name('api.workflows.show');
    // 更新工作流接口 - 修改工作流配置，可更新状态和节点信息
    Route::put('/workflows/{id}', 'WorkflowController@update')->name('api.workflows.update');
    // 删除工作流接口 - 根据ID删除指定的工作流
    Route::delete('/workflows/{id}', 'WorkflowController@destroy')->name('api.workflows.destroy');
    // 切换工作流状态接口 - 启用或禁用指定工作流
    Route::put('/workflows/{id}/toggle-status', 'WorkflowController@toggleStatus')->name('api.workflows.toggle.status');
    // 获取工作流节点配置接口 - 获取指定工作流的节点配置信息
    Route::get('/workflows/{id}/nodes', 'WorkflowController@getNodes')->name('api.workflows.nodes');
    // 更新工作流节点配置接口 - 修改指定工作流的节点配置
    Route::put('/workflows/{id}/nodes', 'WorkflowController@updateNodes')->name('api.workflows.update.nodes');

    // 流程配置专用API（避免与其他workflow接口冲突）
    // 注意：具体路径的路由必须放在参数化路由之前，避免路由冲突
    Route::get('/workflow-config/list', 'WorkflowConfigController@getList')->name('api.workflow.config.list');// 获取工作流配置列表
    Route::get('/workflow-config/assignable-users', 'WorkflowConfigController@getAssignableUsers')->name('api.workflow.config.assignable.users');// 获取可分配用户列表
    Route::get('/workflow-config/case-types', 'WorkflowConfigController@getCaseTypes')->name('api.workflow.config.case.types');// 获取项目类型选项
    Route::post('/workflow-config/batch-update', 'WorkflowConfigController@batchUpdate')->name('api.workflow.config.batch.update');// 批量更新工作流配置
    Route::get('/workflow-config/{id}', 'WorkflowConfigController@getDetail')->name('api.workflow.config.show');// 获取工作流配置详情
    Route::put('/workflow-config/{id}', 'WorkflowConfigController@update')->name('api.workflow.config.update');// 更新工作流配置
    Route::post('/workflow-config/{id}/reset', 'WorkflowConfigController@resetToDefault')->name('api.workflow.config.reset');// 重置工作流配置为默认值
    Route::post('/workflow-config/{id}/validate', 'WorkflowConfigController@validateWorkflow')->name('api.workflow.config.validate');// 验证工作流配置

    // 审核进度相关路由
    Route::get('/review-progress/contract-flows', 'ReviewProgressController@getContractFlows')->name('api.review.progress.contract.flows');// 获取合同审核流程列表
    Route::get('/review-progress/contract-flows/{id}', 'ReviewProgressController@getContractFlowDetail')->name('api.review.progress.contract.flows.detail');// 获取合同审核流程详情
    Route::get('/review-progress/assign-flows', 'ReviewProgressController@getAssignFlows')->name('api.review.progress.assign.flows');// 获取分配审核流程列表
    Route::get('/review-progress/review-flows', 'ReviewProgressController@getReviewFlows')->name('api.review.progress.review.flows');// 获取审核流程列表

    // 工作流实例管理（公开路由 - 仅查询接口）
    // 注意：具体路径的路由必须放在参数化路由之前，避免路由冲突
    Route::get('/workflow-instances/business-status', 'WorkflowInstanceController@businessStatus')->name('api.workflow.instances.business.status');// 获取业务状态信息
    Route::get('/workflow-instances/assignable-users', 'WorkflowInstanceController@getAssignableUsers')->name('api.workflow.instances.assignable.users');// 获取可分配用户列表
    Route::get('/workflow-instances/{id}', 'WorkflowInstanceController@show')->name('api.workflow.instances.show');// 获取工作流实例详情
    Route::get('/workflow-instances/{instanceId}/backable-nodes', 'WorkflowInstanceController@getBackableNodes')->name('api.workflow.instances.backable.nodes');// 获取可回退节点列表

    // 首页Dashboard（临时移到公开路由用于测试）
    Route::get('/dashboard/statistics', 'DashboardController@statistics')->name('api.dashboard.statistics');//获取首页统计数据
    Route::get('/dashboard/my-tasks', 'DashboardController@myTasks')->name('api.dashboard.my.tasks');//获取我的待办任务 (myTasks)
    Route::get('/dashboard/recent-activities', 'DashboardController@recentActivities')->name('api.dashboard.recent.activities');//获取最新动态 (recentActivities)
    Route::get('/dashboard/notifications', 'DashboardController@notifications')->name('api.dashboard.notifications');//获取通知 (notifications)
    Route::get('/dashboard/quick-actions', 'DashboardController@quickActions')->name('api.dashboard.quick.actions');//获取快捷操作 (quickActions)

    // 合同管理（已移至认证路由组）

    // 测试路由
    Route::get('/test-contracts', function() {
        return response()->json(['message' => 'Test route works', 'count' => \App\Models\Contract::count()]);
    });// 测试合同数据查询路由

    // 审核进度路由（公开访问，用于审核进度页面）
    Route::get('/review-progress/register-flows', 'ReviewProgressController@getRegisterFlows');// 获取注册流程审核进度列表
    Route::get('/review-progress/register-flows/{id}', 'ReviewProgressController@getRegisterFlowDetail');// 获取注册流程审核进度详情
    Route::get('/review-progress/contract-flows', 'ReviewProgressController@getContractFlows');// 获取合同流程审核进度列表
    Route::get('/review-progress/contract-flows/{id}', 'ReviewProgressController@getContractFlowDetail');// 获取合同流程审核进度详情

    // CORS预检请求支持
    Route::options('/review-progress/register-flows', function () {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', request()->header('Origin'))
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    });// CORS预检请求支持
    Route::options('/review-progress/register-flows/{id}', function () {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', request()->header('Origin'))
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    });// CORS预检请求支持
    Route::options('/review-progress/contract-flows', function () {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', request()->header('Origin'))
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    });// CORS预检请求支持
    Route::options('/review-progress/contract-flows/{id}', function () {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', request()->header('Origin'))
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    });// CORS预检请求支持

    // 测试合同数据查询
    Route::get('/test-contracts-data', function() {
        $contracts = \App\Models\Contract::with(['customer'])->paginate(10);
        return response()->json([
            'data' => $contracts->items(),
            'total' => $contracts->total(),
            'current_page' => $contracts->currentPage(),
            'per_page' => $contracts->perPage()
        ]);
    });// 测试合同数据分页查询路由

    // 客户等级和相关类型设置（临时移到公开路由用于测试）
    Route::get('/test-customer-levels', 'CustomerLevelsController@index')->name('api.test.customer.levels.index');//根据筛选条件获取客户等级列表，支持分页和排序
    Route::get('/test-customer-levels/options', 'CustomerLevelsController@options')->name('api.test.customer.levels.options');//获取选项列表（用于下拉框等）
    Route::get('/test-related-types', 'RelatedTypesController@index')->name('api.test.related.types.index');// 获取相关类型列表
    Route::get('/test-related-types/case-type-options', 'RelatedTypesController@getCaseTypeOptions')->name('api.test.related.types.case.type.options');// 获取项目类型选项

    // 通知规则管理（临时移到公开路由用于测试）
    Route::get('/notification-rules', 'NotificationRuleController@index')->name('api.notification.rules.index');//获取通知规则列表
    Route::get('/notification-rules/file-types', 'NotificationRuleController@getFileTypeTree')->name('api.notification.rules.file.types');//获取文件类型树
    Route::get('/notification-rules/file-category/{id}/rules', 'NotificationRuleController@getRulesByFileCategory')->name('api.notification.rules.file.category.rules');//获取文件分类下的规则
    Route::get('/notification-rules/rule-types', 'NotificationRuleController@getRuleTypes')->name('api.notification.rules.rule.types');//获取规则类型
    Route::get('/notification-rules/process-items', 'NotificationRuleController@getProcessItems')->name('api.notification.rules.process.items');//获取流程项
    Route::get('/notification-rules/process-statuses', 'NotificationRuleController@getProcessStatuses')->name('api.notification.rules.process.statuses');//获取处理事项状态
    Route::get('/notification-rules/users', 'NotificationRuleController@getUsers')->name('api.notification.rules.users');//获取用户列表
    Route::get('/notification-rules/countries', 'NotificationRuleController@getCountries')->name('api.notification.rules.countries');//获取国家列表
    Route::get('/notification-rules/case-types', 'NotificationRuleController@getCaseTypes')->name('api.notification.rules.case.types');//获取项目类型
    Route::post('/notification-rules', 'NotificationRuleController@store')->name('api.notification.rules.store');//创建通知规则
    Route::get('/notification-rules/{id}', 'NotificationRuleController@show')->name('api.notification.rules.show');//获取通知规则详情
    Route::put('/notification-rules/{id}', 'NotificationRuleController@update')->name('api.notification.rules.update');//更新通知规则
    Route::delete('/notification-rules/{id}', 'NotificationRuleController@destroy')->name('api.notification.rules.destroy');//删除通知规则
    Route::put('/notification-rules/{id}/toggle-status', 'NotificationRuleController@toggleStatus')->name('api.notification.rules.toggle.status');//切换通知规则状态
    Route::post('/notification-rules/{id}/test', 'NotificationRuleController@testRule')->name('api.notification.rules.test');// 未使用
    Route::post('/notification-rules/batch', 'NotificationRuleController@batchOperation')->name('api.notification.rules.batch');// 批量操作通知规则

    // 流程规则管理（临时移到公开路由用于测试）
    Route::get('/process-rules', 'ProcessRuleController@index')->name('api.process.rules.index');// 获取流程规则列表
    Route::post('/process-rules', 'ProcessRuleController@store')->name('api.process.rules.store');// 创建流程规则
    Route::get('/process-rules/process-item-tree', 'ProcessRuleController@getProcessItemTree')->name('api.process.rules.process.item.tree');// 获取流程项树形结构
    Route::get('/process-rules/process-items', 'ProcessRuleController@getProcessItemTree')->name('api.process.rules.process.items');// 获取流程项树形结构（别名）
    Route::get('/process-rules/process-item-detail/{id}', 'ProcessRuleController@getProcessItemDetail')->name('api.process.rules.process.item.detail');// 获取流程项详情
    Route::get('/process-rules/process-item-rules', 'ProcessRuleController@getProcessItemRules')->name('api.process.rules.process.item.rules');// 获取流程项规则列表
    Route::get('/process-rules/rule-types', 'ProcessRuleController@getRuleTypes')->name('api.process.rules.rule.types');// 获取规则类型
    Route::get('/process-rules/case-types', 'ProcessRuleController@getCaseTypes')->name('api.process.rules.case.types');// 获取项目类型
    Route::get('/process-rules/business-types', 'ProcessRuleController@getBusinessTypes')->name('api.process.rules.business.types');// 获取业务类型
    Route::get('/process-rules/application-types', 'ProcessRuleController@getApplicationTypes')->name('api.process.rules.application.types');// 获取申请类型
    Route::get('/process-rules/countries', 'ProcessRuleController@getCountries')->name('api.process.rules.countries');// 获取国家列表
    Route::get('/process-rules/process-item-types', 'ProcessRuleController@getProcessItemTypes')->name('api.process.rules.process.item.types');// 获取流程项类型
    Route::get('/process-rules/process-statuses', 'ProcessRuleController@getProcessStatuses')->name('api.process.rules.process.statuses');// 获取处理状态
    Route::get('/process-rules/users', 'ProcessRuleController@getUsers')->name('api.process.rules.users');// 获取用户列表
    Route::get('/process-rules/{id}', 'ProcessRuleController@show')->name('api.process.rules.show');// 获取流程规则详情
    Route::put('/process-rules/{id}', 'ProcessRuleController@update')->name('api.process.rules.update');// 更新流程规则
    Route::delete('/process-rules/{id}', 'ProcessRuleController@destroy')->name('api.process.rules.destroy');// 删除流程规则
    Route::put('/process-rules/{id}/toggle-status', 'ProcessRuleController@toggleStatus')->name('api.process.rules.toggle.status');// 切换流程规则状态

    // 我方公司设置（临时测试路由）
    Route::get('/test-our-companies', 'OurCompaniesController@index')->name('api.test.our.companies.index');// 获取我方公司列表

    // 客户管理临时测试路由（移到公开路由用于测试）
    Route::get('/customer-contacts', 'CustomerContactController@index')->name('api.test.customer.contacts.index');// 获取客户联系人列表
    Route::post('/customer-contacts', 'CustomerContactController@store')->name('api.test.customer.contacts.store');// 创建客户联系人
    Route::get('/customer-contacts/{id}', 'CustomerContactController@show')->name('api.test.customer.contacts.show');// 获取客户联系人详情
    Route::put('/customer-contacts/{id}', 'CustomerContactController@update')->name('api.test.customer.contacts.update');// 更新客户联系人信息
    Route::delete('/customer-contacts/{id}', 'CustomerContactController@destroy')->name('api.test.customer.contacts.destroy');// 删除客户联系人
    Route::get('/customer-contacts-options', 'CustomerContactController@getCustomerOptions')->name('api.test.customer.contacts.options');// 获取客户联系人选项

    Route::get('/customer-applicants', 'CustomerApplicantController@index')->name('api.test.customer.applicants.index');// 获取客户申请人列表
    Route::post('/customer-applicants', 'CustomerApplicantController@store')->name('api.test.customer.applicants.store');// 创建客户申请人
    Route::get('/customer-applicants/{id}', 'CustomerApplicantController@show')->name('api.test.customer.applicants.show');// 获取客户申请人详情
    Route::put('/customer-applicants/{id}', 'CustomerApplicantController@update')->name('api.test.customer.applicants.update');// 更新客户申请人信息
    Route::delete('/customer-applicants/{id}', 'CustomerApplicantController@destroy')->name('api.test.customer.applicants.destroy');// 删除客户申请人

    Route::get('/customer-inventors', 'CustomerInventorController@index')->name('api.test.customer.inventors.index');//获取客户发明人列表
    Route::post('/customer-inventors', 'CustomerInventorController@store')->name('api.test.customer.inventors.store');// 创建客户发明人
    Route::get('/customer-inventors/{id}', 'CustomerInventorController@show')->name('api.test.customer.inventors.show');//获取客户发明人详情
    Route::put('/customer-inventors/{id}', 'CustomerInventorController@update')->name('api.test.customer.inventors.update');//修改客户发明人
    Route::delete('/customer-inventors/{id}', 'CustomerInventorController@destroy')->name('api.test.customer.inventors.destroy');//删除客户发明人

    // 三个数据配置页面测试路由（临时）
    Route::get('/test-commission-settings', 'CommissionSettingsController@index')->name('api.test.commission.settings.index');// 获取提成设置列表
    Route::post('/test-commission-settings', 'CommissionSettingsController@store')->name('api.test.commission.settings.store');// 创建提成设置
    Route::get('/test-commission-settings/{id}', 'CommissionSettingsController@show')->name('api.test.commission.settings.show');// 获取提成设置详情
    Route::put('/test-commission-settings/{id}', 'CommissionSettingsController@update')->name('api.test.commission.settings.update');// 更新提成设置

    Route::get('/test-manuscript-scoring-items', 'ManuscriptScoringItemsController@index')->name('api.test.manuscript.scoring.items.index');// 获取核稿评分项目列表
    Route::post('/test-manuscript-scoring-items', 'ManuscriptScoringItemsController@store')->name('api.test.manuscript.scoring.items.store');// 创建核稿评分项目
    Route::get('/test-manuscript-scoring-items/{id}', 'ManuscriptScoringItemsController@show')->name('api.test.manuscript.scoring.items.show');// 获取核稿评分项目详情
    Route::put('/test-manuscript-scoring-items/{id}', 'ManuscriptScoringItemsController@update')->name('api.test.manuscript.scoring.items.update');// 更新核稿评分项目

    Route::get('/test-protection-centers', 'ProtectionCentersController@index')->name('api.test.protection.centers.index');// 获取保护中心列表
    Route::post('/test-protection-centers', 'ProtectionCentersController@store')->name('api.test.protection.centers.store');// 创建保护中心
    Route::get('/test-protection-centers/{id}', 'ProtectionCentersController@show')->name('api.test.protection.centers.show');// 获取保护中心详情
    Route::put('/test-protection-centers/{id}', 'ProtectionCentersController@update')->name('api.test.protection.centers.update');// 更新保护中心

    // 价格指数设置（移到公开路由，前端可以直接访问）
    Route::get('/data-config/price-indices', 'PriceIndicesController@index')->name('api.price.indices.index');// 获取价格指数列表
    Route::get('/data-config/price-indices/options', 'PriceIndicesController@options')->name('api.price.indices.options');// 获取价格指数选项
    Route::post('/data-config/price-indices', 'PriceIndicesController@store')->name('api.price.indices.store');// 创建价格指数
    Route::get('/data-config/price-indices/{id}', 'PriceIndicesController@show')->name('api.price.indices.show');// 获取价格指数详情
    Route::put('/data-config/price-indices/{id}', 'PriceIndicesController@update')->name('api.price.indices.update');// 更新价格指数
    Route::delete('/data-config/price-indices/{id}', 'PriceIndicesController@destroy')->name('api.price.indices.destroy');// 删除价格指数
    Route::post('/data-config/price-indices/batch-status', 'PriceIndicesController@batchUpdateStatus')->name('api.price.indices.batch.status');// 批量更新价格指数状态

    // 创新指数设置（移到公开路由，前端可以直接访问）
    Route::get('/data-config/innovation-indices', 'InnovationIndicesController@index')->name('api.innovation.indices.index');//获取创新指数列表
    Route::get('/data-config/innovation-indices/options', 'InnovationIndicesController@options')->name('api.innovation.indices.options');//获取选项列表（用于下拉框等）
    Route::post('/data-config/innovation-indices', 'InnovationIndicesController@store')->name('api.innovation.indices.store');//无
    Route::get('/data-config/innovation-indices/{id}', 'InnovationIndicesController@show')->name('api.innovation.indices.show');//无
    Route::put('/data-config/innovation-indices/{id}', 'InnovationIndicesController@update')->name('api.innovation.indices.update');//无
    Route::delete('/data-config/innovation-indices/{id}', 'InnovationIndicesController@destroy')->name('api.innovation.indices.destroy');//无
    Route::post('/data-config/innovation-indices/batch-status', 'InnovationIndicesController@batchUpdateStatus')->name('api.innovation.indices.batch.status');//无

    // 产品设置（移到公开路由，前端可以直接访问）
    Route::get('/data-config/products', 'ProductController@index')->name('api.products.index');// 获取产品列表
    Route::get('/data-config/products/options', 'ProductController@options')->name('api.products.options');// 获取产品选项列表
    Route::post('/data-config/products', 'ProductController@store')->name('api.products.store');// 创建产品
    Route::get('/data-config/products/{id}', 'ProductController@show')->name('api.products.show');// 获取产品详情
    Route::put('/data-config/products/{id}', 'ProductController@update')->name('api.products.update');// 更新产品
    Route::delete('/data-config/products/{id}', 'ProductController@destroy')->name('api.products.destroy');// 删除产品
    Route::post('/data-config/products/batch-status', 'ProductController@batchUpdateStatus')->name('api.products.batch.status');// 批量更新产品状态

    // 业务服务类型设置（移到公开路由，前端可以直接访问）
    Route::get('/data-config/business-service-types', 'BusinessServiceTypesController@index')->name('api.business.service.types.index');//重写 index 方法，支持按名称、业务类别、状态搜索业务服务类型列表
    Route::get('/data-config/business-service-types/options', 'BusinessServiceTypesController@options')->name('api.business.service.types.options');// 获取业务服务类型选项列表
    Route::post('/data-config/business-service-types', 'BusinessServiceTypesController@store')->name('api.business.service.types.store');// 创建业务服务类型
    Route::get('/data-config/business-service-types/{id}', 'BusinessServiceTypesController@show')->name('api.business.service.types.show');// 获取业务服务类型详情
    Route::put('/data-config/business-service-types/{id}', 'BusinessServiceTypesController@update')->name('api.business.service.types.update');// 更新业务服务类型
    Route::delete('/data-config/business-service-types/{id}', 'BusinessServiceTypesController@destroy')->name('api.business.service.types.destroy');// 删除业务服务类型
    Route::post('/data-config/business-service-types/batch-status', 'BusinessServiceTypesController@batchUpdateStatus')->name('api.business.service.types.batch.status');// 批量更新业务服务类型状态

    // 处理事项类型设置（移到公开路由，前端可以直接访问）
    Route::get('/data-config/process-types', 'ProcessTypesController@index')->name('api.process.types.index');// 获取处理事项类型列表
    Route::get('/data-config/process-types/options', 'ProcessTypesController@options')->name('api.process.types.options');// 获取处理事项类型选项列表
    Route::post('/data-config/process-types', 'ProcessTypesController@store')->name('api.process.types.store');// 创建处理事项类型
    Route::get('/data-config/process-types/{id}', 'ProcessTypesController@show')->name('api.process.types.show');// 获取处理事项类型详情
    Route::put('/data-config/process-types/{id}', 'ProcessTypesController@update')->name('api.process.types.update');// 更新处理事项类型
    Route::delete('/data-config/process-types/{id}', 'ProcessTypesController@destroy')->name('api.process.types.destroy');// 删除处理事项类型

    // 客户管理（临时移到公开路由用于测试）
    // 注意：具体的路由必须放在参数路由之前
    Route::get('/test-customers/user-options', 'CustomerController@getUserOptions')->name('api.test.customers.user.options');// 获取用户选项列表
    Route::get('/test-customers/export', 'CustomerController@export')->name('api.test.customers.export');// 导出客户数据
    Route::get('/test-customers/download-template', 'CustomerController@downloadTemplate')->name('api.test.customers.download.template');// 下载导入模板
    Route::post('/test-customers/import', 'CustomerController@import')->name('api.test.customers.import');// 导入客户数据
    Route::post('/test-customers/batch-transfer-business', 'CustomerController@batchTransferBusiness')->name('api.test.customers.batch.transfer.business');// 批量转移客户业务
    Route::post('/test-customers/batch-add-business', 'CustomerController@batchAddBusiness')->name('api.test.customers.batch.add.business');// 批量添加业务人员
    Route::post('/test-customers/transfer', 'CustomerController@transfer')->name('api.test.customers.transfer');// 转移客户
    Route::post('/test-customers/move-to-public', 'CustomerController@moveToPublic')->name('api.test.customers.move.to.public');// 移入公海
    Route::post('/test-customers/batch-destroy', 'CustomerController@batchDestroy')->name('api.test.customers.batch.destroy');// 批量删除客户

    // 基础CRUD路由放在最后
    Route::get('/test-customers', 'CustomerController@index')->name('api.test.customers.index');// 获取客户列表
    Route::post('/test-customers', 'CustomerController@store')->name('api.test.customers.store');// 创建客户
    Route::get('/test-customers/{id}', 'CustomerController@show')->name('api.test.customers.show');// 获取客户详情
    Route::put('/test-customers/{id}', 'CustomerController@update')->name('api.test.customers.update');// 更新客户信息
    Route::delete('/test-customers/{id}', 'CustomerController@destroy')->name('api.test.customers.destroy');// 删除客户

    // 前端页面调用的路由（临时移到公开区域）
    // 具体路由必须在参数路由之前
    Route::get('/customers/user-options', 'CustomerController@getUserOptions')->name('api.customers.user.options');// 获取用户选项列表
    Route::get('/customers/organization-tree', 'CustomerController@getOrganizationTree')->name('api.customers.organization.tree');// 获取组织架构树
    Route::get('/customers/config-data', 'CustomerController@getConfigData')->name('api.customers.config.data');// 获取配置数据
    Route::post('/customers/check-customer-code-unique', 'CustomerController@checkCustomerCodeUnique')->name('api.customers.check.customer.code.unique');// 检查客户编码唯一性
    Route::get('/customers/export', 'CustomerController@export')->name('api.customers.export');// 导出客户数据
    Route::get('/customers/download-template', 'CustomerController@downloadTemplate')->name('api.customers.download.template');// 下载导入模板
    Route::post('/customers/import', 'CustomerController@import')->name('api.customers.import');// 导入客户数据
    Route::post('/customers/batch-transfer-business', 'CustomerController@batchTransferBusiness')->name('api.customers.batch.transfer.business');// 批量转移客户业务
    Route::post('/customers/batch-add-business', 'CustomerController@batchAddBusiness')->name('api.customers.batch.add.business');// 批量添加业务人员
    Route::post('/customers/transfer', 'CustomerController@transfer')->name('api.customers.transfer');// 转移客户
    Route::post('/customers/move-to-public', 'CustomerController@moveToPublic')->name('api.customers.move.to.public');// 移入公海
    Route::post('/customers/batch-destroy', 'CustomerController@batchDestroy')->name('api.customers.batch.destroy');// 批量删除客户

    // 客户排序功能
    Route::post('/customers/sort/save', 'CustomerController@saveCustomerSort')->name('api.customers.sort.save');// 保存客户排序
    Route::get('/customers/sort', 'CustomerController@getCustomerSort')->name('api.customers.sort.get');// 获取客户排序
    Route::post('/customers/sort/reset', 'CustomerController@resetCustomerSort')->name('api.customers.sort.reset');// 重置客户排序
    Route::post('/customers/sort/set', 'CustomerController@setCustomerSort')->name('api.customers.sort.set');// 设置客户排序

    // 客户业务员信息
    Route::get('/customers/business-persons', 'CustomerController@getCustomerBusinessPersons')->name('api.customers.business.persons');// 获取客户业务员信息

    // 客户数据导出
    Route::post('/customers/export', 'CustomerController@export')->name('api.customers.export');// 客户数据导出

    // 测试客户等级和规模数据
    Route::get('/customers/test-level-scale', 'CustomerController@testLevelAndScale')->name('api.customers.test.level.scale');// 测试客户等级和规模数据

    // 修复客户等级和规模数据
    Route::post('/customers/fix-level-scale', 'CustomerController@fixLevelAndScaleData')->name('api.customers.fix.level.scale');// 修复客户等级和规模数据

    // 从合同项目记录创建正式项目
    Route::post('/customers/contract-case-records/{id}/create-case', 'CustomerController@createCaseFromRecord')->name('api.customers.contract.case.records.create.case');// 从合同项目记录创建正式项目

    // 撤销立项
    Route::post('/customers/contract-case-records/{id}/cancel-case', 'CustomerController@cancelCaseFromRecord')->name('api.customers.contract.case.records.cancel.case');// 撤销立项

    // 文件管理查询（公开访问）
    Route::get('/search/files', 'FileSearchController@search')->name('api.search.files');// 文件搜索
    Route::post('/search/files/export', 'FileSearchController@export')->name('api.search.files.export');// 文件导出
    Route::post('/search/files/batch-download', 'FileSearchController@batchDownload')->name('api.search.files.batch.download');// 批量下载
    Route::get('/search/files/options', 'FileSearchController@getOptions')->name('api.search.files.options');//获取下拉选项数据
    Route::get('/search/files/download/{id}', 'FileSearchController@downloadFile')->name('api.search.files.download');//文件下载

    // 基础路由放在最后
    Route::get('/customers', 'CustomerController@index')->name('api.customers.index');// 获取客户列表
    Route::post('/customers', 'CustomerController@store')->name('api.customers.store');// 创建客户
    Route::get('/customers/{id}', 'CustomerController@show')->name('api.customers.show');// 获取客户详情
    Route::put('/customers/{id}', 'CustomerController@update')->name('api.customers.update');// 更新客户信息
    Route::delete('/customers/{id}', 'CustomerController@destroy')->name('api.customers.destroy');// 删除客户

    // 客户Tab页面相关API（移到公开区域用于前端测试）
    // 客户联系人管理
    Route::get('/customer-contacts', 'CustomerContactController@index')->name('api.customer.contacts.index');// 获取客户联系人列表
    Route::post('/customer-contacts', 'CustomerContactController@store')->name('api.customer.contacts.store');// 创建客户联系人
    Route::get('/customer-contacts/{id}', 'CustomerContactController@show')->name('api.customer.contacts.show');// 获取客户联系人详情
    Route::put('/customer-contacts/{id}', 'CustomerContactController@update')->name('api.customer.contacts.update');// 更新客户联系人信息
    Route::delete('/customer-contacts/{id}', 'CustomerContactController@destroy')->name('api.customer.contacts.destroy');// 删除客户联系人

    // 客户申请人管理
    Route::get('/customer-applicants', 'CustomerApplicantController@index')->name('api.customer.applicants.index');// 获取客户申请人列表
    Route::post('/customer-applicants', 'CustomerApplicantController@store')->name('api.customer.applicants.store');// 创建客户申请人
    Route::get('/customer-applicants/{id}', 'CustomerApplicantController@show')->name('api.customer.applicants.show');// 获取客户申请人详情
    Route::put('/customer-applicants/{id}', 'CustomerApplicantController@update')->name('api.customer.applicants.update');// 更新客户申请人信息
    Route::delete('/customer-applicants/{id}', 'CustomerApplicantController@destroy')->name('api.customer.applicants.destroy');// 删除客户申请人

    // 客户发明人管理
    Route::get('/customer-inventors', 'CustomerInventorController@index')->name('api.customer.inventors.index');// 获取客户发明人列表
    Route::post('/customer-inventors', 'CustomerInventorController@store')->name('api.customer.inventors.store');// 创建客户发明人
    Route::get('/customer-inventors/{id}', 'CustomerInventorController@show')->name('api.customer.inventors.show');// 获取客户发明人详情
    Route::put('/customer-inventors/{id}', 'CustomerInventorController@update')->name('api.customer.inventors.update');// 更新客户发明人
    Route::delete('/customer-inventors/{id}', 'CustomerInventorController@destroy')->name('api.customer.inventors.destroy');// 删除客户发明人

    // 客户合同管理
    Route::get('/customer-contracts', 'CustomerContractController@index')->name('api.customer.contracts.index');// 获取客户合同列表
    Route::post('/customer-contracts', 'CustomerContractController@store')->name('api.customer.contracts.store');// 创建客户合同
    Route::get('/customer-contracts/{id}', 'CustomerContractController@show')->name('api.customer.contracts.show');// 获取客户合同详情
    Route::put('/customer-contracts/{id}', 'CustomerContractController@update')->name('api.customer.contracts.update');// 更新客户合同信息
    Route::delete('/customer-contracts/{id}', 'CustomerContractController@destroy')->name('api.customer.contracts.destroy');// 删除客户合同

    // 客户相关人员管理
    Route::get('/customer-related-persons', 'CustomerRelatedPersonController@index')->name('api.customer.related.persons.index');// 获取客户相关人员列表
    Route::post('/customer-related-persons', 'CustomerRelatedPersonController@store')->name('api.customer.related.persons.store');// 创建客户相关人员
    // 具体路由必须放在 {id} 路由之前
    Route::get('/customer-related-persons/person-types', 'CustomerRelatedPersonController@getPersonTypes')->name('api.customer.related.persons.person.types');// 获取客户相关人员类型
    Route::get('/customer-related-persons/business-persons', 'CustomerRelatedPersonController@getCustomerBusinessPersons')->name('api.customer.related.persons.business.persons');// 获取客户相关人员
    Route::get('/customer-related-persons/search-users', 'CustomerRelatedPersonController@searchUsers')->name('api.customer.related.persons.search.users');//搜索用户（用于添加助理/协作人）
    Route::post('/customer-related-persons/add-business-person', 'CustomerRelatedPersonController@addCustomerBusinessPerson')->name('api.customer.related.persons.add.business.person');//添加客户相关人员
    Route::post('/customer-related-persons/remove-business-person', 'CustomerRelatedPersonController@removeCustomerBusinessPerson')->name('api.customer.related.persons.remove.business.person');//删除客户相关人员
    // 通用 {id} 路由放在最后
    Route::get('/customer-related-persons/{id}', 'CustomerRelatedPersonController@show')->name('api.customer.related.persons.show');// 获取客户相关人员详情
    Route::put('/customer-related-persons/{id}', 'CustomerRelatedPersonController@update')->name('api.customer.related.persons.update');// 更新客户相关人员
    Route::delete('/customer-related-persons/{id}', 'CustomerRelatedPersonController@destroy')->name('api.customer.related.persons.destroy');// 删除客户相关人员

    // 案例监控管理（临时移到公开路由用于测试）
    Route::get('/case-monitor/item-monitor', 'CaseMonitorController@itemMonitor')->name('api.case.monitor.item');//事项监控（查询案例处理事项列表）
    Route::post('/case-monitor/item-monitor/export', 'CaseMonitorController@exportItemMonitor')->name('api.case.monitor.item.export');//导出事项监控数据
    Route::get('/case-monitor/fee-monitor', 'CaseMonitorController@feeMonitor')->name('api.case.monitor.fee');//官费监控列表查询
    Route::get('/case-monitor/fee-stats', 'CaseMonitorController@feeStats')->name('api.case.monitor.fee.stats');//  获取费用统计数据
    Route::post('/case-monitor/fee-monitor/export', 'CaseMonitorController@exportFeeMonitor')->name('api.case.monitor.fee.export');// 导出官费监控数据
    Route::get('/case-monitor/abnormal-fee', 'CaseMonitorController@abnormalFee')->name('api.case.monitor.abnormal.fee');// 官费监控（费用管理查询）
    Route::post('/case-monitor/abnormal-fee/export', 'CaseMonitorController@exportAbnormalFee')->name('api.case.monitor.abnormal.fee.export');//导出异常官费数据
    Route::post('/case-monitor/abnormal-fee/mark-processed', 'CaseMonitorController@markAbnormalFeeProcessed')->name('api.case.monitor.abnormal.fee.mark.processed');//标记异常费用已处理

    // 请款管理（临时移到公开路由用于测试）
    Route::get('/payment-requests', 'PaymentRequestController@index')->name('api.payment.requests.index');// 获取请款申请列表
    Route::get('/payment-requests/statistics', 'PaymentRequestController@statistics')->name('api.payment.requests.statistics');// 获取请款统计数据
    Route::get('/payment-requests/{id}', 'PaymentRequestController@show')->name('api.payment.requests.show');// 获取请款申请详情
    Route::post('/payment-requests', 'PaymentRequestController@store')->name('api.payment.requests.store');// 创建请款申请
    Route::put('/payment-requests/{id}', 'PaymentRequestController@update')->name('api.payment.requests.update');// 更新请款申请
    Route::delete('/payment-requests/{id}', 'PaymentRequestController@destroy')->name('api.payment.requests.destroy');// 删除请款申请
    Route::post('/payment-requests/{id}/submit', 'PaymentRequestController@submit')->name('api.payment.requests.submit');// 提交请款申请
    Route::post('/payment-requests/{id}/withdraw', 'PaymentRequestController@withdraw')->name('api.payment.requests.withdraw');// 撤回请款申请
    Route::post('/payment-requests/{id}/approve', 'PaymentRequestController@approve')->name('api.payment.requests.approve');// 审批请款申请
    Route::post('/payment-requests/export', 'PaymentRequestController@export')->name('api.payment.requests.export');// 导出请款申请数据

    // 到款管理（临时移到公开路由用于测试）
    Route::get('/payment-receiveds', 'PaymentReceivedController@index')->name('api.payment.receiveds.index');// 获取到款列表
    Route::get('/payment-receiveds/statistics', 'PaymentReceivedController@statistics')->name('api.payment.receiveds.statistics');// 获取到款统计数据
    Route::get('/payment-receiveds/{id}', 'PaymentReceivedController@show')->name('api.payment.receiveds.show');// 获取到款详情
    Route::post('/payment-receiveds', 'PaymentReceivedController@store')->name('api.payment.receiveds.store');// 创建到款
    Route::put('/payment-receiveds/{id}', 'PaymentReceivedController@update')->name('api.payment.receiveds.update');// 更新到款
    Route::delete('/payment-receiveds/{id}', 'PaymentReceivedController@destroy')->name('api.payment.receiveds.destroy');// 删除到款
    Route::post('/payment-receiveds/{id}/submit', 'PaymentReceivedController@submit')->name('api.payment.receiveds.submit');// 提交到款
    Route::post('/payment-receiveds/{id}/claim', 'PaymentReceivedController@claim')->name('api.payment.receiveds.claim');// 认领到款
    Route::post('/payment-receiveds/export', 'PaymentReceivedController@export')->name('api.payment.receiveds.export');// 导出到款数据

    // 核销管理（临时移到公开路由用于测试）
    Route::get('/write-offs/pending', 'WriteOffController@getPendingList')->name('api.write-offs.pending');// 获取待核销列表
    Route::get('/write-offs/pending/statistics', 'WriteOffController@getPendingStatistics')->name('api.write-offs.pending.statistics');// 获取待核销统计数据
    Route::post('/write-offs/write-off', 'WriteOffController@writeOff')->name('api.write-offs.write-off');// 核销处理
    Route::post('/write-offs/batch-write-off', 'WriteOffController@batchWriteOff')->name('api.write-offs.batch-write-off');// 批量核销
    Route::get('/write-offs/completed', 'WriteOffController@getCompletedList')->name('api.write-offs.completed');// 获取已核销列表
    Route::get('/write-offs/completed/statistics', 'WriteOffController@getCompletedStatistics')->name('api.write-offs.completed.statistics');// 获取已核销统计数据
    Route::get('/write-offs/write-off-completed', 'WriteOffController@getWriteOffCompletedList')->name('api.write-offs.write-off-completed');// 获取核销完成列表
    Route::get('/write-offs/write-off-completed/statistics', 'WriteOffController@getWriteOffCompletedStatistics')->name('api.write-offs.write-off-completed.statistics');// 获取核销完成统计数据
    Route::post('/write-offs/{id}/revert', 'WriteOffController@revertWriteOff')->name('api.write-offs.revert');// 撤销核销
    Route::post('/write-offs/batch-revert', 'WriteOffController@batchRevertWriteOff')->name('api.write-offs.batch-revert');// 批量撤销核销
    Route::get('/write-offs/{id}', 'WriteOffController@show')->name('api.write-offs.show');// 获取核销详情
    Route::get('/write-offs/export/pending', 'WriteOffController@exportPending')->name('api.write-offs.export.pending');// 导出待核销数据
    Route::get('/write-offs/export/completed', 'WriteOffController@exportCompleted')->name('api.write-offs.export.completed');// 导出已核销数据

    // 用户和客户API（临时移到公开路由用于测试）
    Route::get('/users', 'UserController@index')->name('api.users.index.public');// 获取用户列表（公开）
    Route::get('/customers', 'CustomerController@index')->name('api.customers.index.public');// 获取客户列表（公开）

    // 分配管理（临时移到公开路由用于测试）
    Route::get('/assignment/new-applications', 'AssignmentController@newApplications')->name('api.assignment.new.applications');// 获取新申请待分配列表
    Route::get('/assignment/middle-cases', 'AssignmentController@middleCases')->name('api.assignment.middle.cases');// 获取中间案待分配列表
    Route::get('/assignment/tech-service-cases', 'AssignmentController@techServiceCases')->name('api.assignment.tech.service.cases');// 获取科服待分配列表
    Route::get('/assignment/assigned-cases', 'AssignmentController@assignedCases')->name('api.assignment.assigned.cases');// 获取已分配列表
    Route::post('/assignment/batch-assign', 'AssignmentController@batchAssign')->name('api.assignment.batch.assign');// 批量分配处理事项
    Route::post('/assignment/direct-assign', 'AssignmentController@directAssign')->name('api.assignment.direct.assign');// 直接分配（单个处理事项）
    Route::post('/assignment/withdraw-assignment', 'AssignmentController@withdrawAssignment')->name('api.assignment.withdraw');// 撤回分配
    Route::get('/assignment/assignable-users', 'AssignmentController@getAssignableUsers')->name('api.assignment.assignable.users');// 获取可分配用户列表
    Route::get('/assignment/process-detail/{id}', 'AssignmentController@getProcessDetail')->name('api.assignment.process.detail');// 获取处理事项详情

    // 提成管理（临时移到公开路由用于测试）
    Route::get('/commission/stats', 'CommissionController@getCommissionStats')->name('api.commission.stats');// 获取提成统计数据
    Route::get('/commission/user-summary', 'CommissionController@getUserCommissionSummary')->name('api.commission.user.summary');// 获取用户提成汇总
    Route::get('/commission/config', 'CommissionController@getCommissionConfig')->name('api.commission.config');// 获取提成配置

    // 核稿管理（临时移到公开路由用于测试）- 使用修复版控制器
    Route::get('/review/draft-list', 'ReviewControllerFixed@getDraftList')->name('api.review.draft.list');// 获取草稿列表
    Route::get('/review/to-be-start-list', 'ReviewControllerFixed@getToBeStartList')->name('api.review.to.be.start.list');// 获取待开始审核列表
    Route::get('/review/in-review-list', 'ReviewControllerFixed@getInReviewList')->name('api.review.in.review.list');// 获取审核中列表
    Route::get('/review/completed-list', 'ReviewControllerFixed@getCompletedList')->name('api.review.completed.list');// 获取已完成审核列表
    Route::get('/review/detail/{id}', 'ReviewControllerFixed@getReviewDetail')->name('api.review.detail');// 获取审核详情
    Route::post('/review/transfer', 'ReviewControllerFixed@transferProcess')->name('api.review.transfer');// 转交审核
    Route::post('/review/return', 'ReviewControllerFixed@returnProcess')->name('api.review.return');// 退回审核

    // 提成配置管理（临时移到公开路由用于测试）
    Route::get('/commission-configs', 'CommissionConfigController@index')->name('api.commission.configs.index');// 获取提成配置列表
    Route::post('/commission-configs', 'CommissionConfigController@store')->name('api.commission.configs.store');// 创建提成配置
    Route::get('/commission-configs/{id}', 'CommissionConfigController@show')->name('api.commission.configs.show');// 获取提成配置详情
    Route::put('/commission-configs/{id}', 'CommissionConfigController@update')->name('api.commission.configs.update');// 更新提成配置
    Route::delete('/commission-configs/{id}', 'CommissionConfigController@destroy')->name('api.commission.configs.destroy');// 删除提成配置
    Route::post('/commission-configs/batch-destroy', 'CommissionConfigController@batchDestroy')->name('api.commission.configs.batch.destroy');// 批量删除提成配置
    Route::post('/commission-configs/batch-enable', 'CommissionConfigController@batchEnable')->name('api.commission.configs.batch.enable');// 批量启用提成配置
    Route::post('/commission-configs/batch-disable', 'CommissionConfigController@batchDisable')->name('api.commission.configs.batch.disable');// 批量禁用提成配置

    // 用户等级配置管理（临时移到公开路由用于测试）
    Route::get('/user-level-configs', 'UserLevelConfigController@index')->name('api.user.level.configs.index');// 获取用户等级配置列表
    Route::post('/user-level-configs', 'UserLevelConfigController@store')->name('api.user.level.configs.store');// 创建用户等级配置
    Route::get('/user-level-configs/{id}', 'UserLevelConfigController@show')->name('api.user.level.configs.show');// 获取用户等级配置详情
    Route::put('/user-level-configs/{id}', 'UserLevelConfigController@update')->name('api.user.level.configs.update');// 更新用户等级配置
    Route::delete('/user-level-configs/{id}', 'UserLevelConfigController@destroy')->name('api.user.level.configs.destroy');// 删除用户等级配置
    Route::post('/user-level-configs/batch-destroy', 'UserLevelConfigController@batchDestroy')->name('api.user.level.configs.batch.destroy');// 批量删除用户等级配置
    Route::post('/user-level-configs/batch-enable', 'UserLevelConfigController@batchEnable')->name('api.user.level.configs.batch.enable');// 批量启用用户等级配置
    Route::post('/user-level-configs/batch-disable', 'UserLevelConfigController@batchDisable')->name('api.user.level.configs.batch.disable');// 批量禁用用户等级配置

});

// 需要认证的路由
Route::group(['middleware' => ['auth:sanctum'], 'namespace' => 'Api'], function () {
    // 认证相关
    Route::post('/logout', 'AuthController@logout')->name('api.logout');// 登出
    Route::get('/user/profile', 'AuthController@profile')->name('api.user.profile');// 获取当前登录用户信息接口
    Route::put('/user/profile', 'AuthController@updateProfile')->name('api.user.profile.update');// 修改当前登录用户信息接口
    Route::put('/user/change-password', 'AuthController@changePassword')->name('api.user.change.password');// 修改当前登录用户密码接口

    // 通用文件上传
    Route::post('/upload', 'FileUploadController@upload')->name('api.upload');//通用文件上传

    // 客户文件管理
    Route::get('/customer-files', 'CustomerFileController@index')->name('api.customer.files.index');// 获取客户文件列表
    Route::post('/customer-files/upload', 'CustomerFileController@upload')->name('api.customer.files.upload');// 上传客户文件
    Route::get('/customer-files/{id}', 'CustomerFileController@show')->name('api.customer.files.show');// 获取客户文件详情
    Route::put('/customer-files/{id}', 'CustomerFileController@update')->name('api.customer.files.update');// 更新客户文件信息
    Route::delete('/customer-files/{id}', 'CustomerFileController@destroy')->name('api.customer.files.destroy');// 删除客户文件
    Route::get('/customer-files/{id}/download', 'CustomerFileController@download')->name('api.customer.files.download');// 下载客户文件
    Route::get('/customer-files/file-categories', 'CustomerFileController@getFileCategories')->name('api.customer.files.file.categories');// 获取客户文件分类

    // 用户管理（需要权限）
    Route::group(['middleware' => 'permission:system.user'], function () {
        Route::get('/users', 'UserController@index')->name('api.users.index');// 获取用户列表
        Route::get('/users/{id}', 'UserController@show')->name('api.users.show');// 获取用户详情
        Route::post('/users', 'UserController@store')->name('api.users.store');// 创建用户
        Route::put('/users/{id}', 'UserController@update')->name('api.users.update');// 更新用户信息
        Route::delete('/users/{id}', 'UserController@destroy')->name('api.users.destroy');// 删除用户
        Route::put('/users/{id}/reset-password', 'UserController@resetPassword')->name('api.users.reset.password');// 重置用户密码
        Route::get('/users/{id}/roles', 'UserController@getUserRoles')->name('api.users.roles');// 获取用户角色
        Route::post('/users/{id}/roles', 'UserController@assignRoles')->name('api.users.assign.roles');// 分配用户角色
        Route::post('/users/batch-delete', 'UserController@batchDelete')->name('api.users.batch.delete');// 批量删除用户
        Route::put('/users/{id}/toggle-status', 'UserController@toggleStatus')->name('api.users.toggle.status');// 切换用户状态
    });

    // 部门管理（需要权限）
    Route::group(['middleware' => 'permission:system.department'], function () {
        Route::get('/departments', 'DepartmentController@index')->name('api.departments.index');//获取部门列表（树形结构）
        Route::get('/departments/managers', 'DepartmentController@getManagers')->name('api.departments.managers');//获取部门列表（树形结构）
        Route::post('/departments', 'DepartmentController@store')->name('api.departments.store');//添加部门
        Route::get('/departments/{id}', 'DepartmentController@show')->name('api.departments.show');//获取部门详情
        Route::put('/departments/{id}', 'DepartmentController@update')->name('api.departments.update');//修改部门
        Route::delete('/departments/{id}', 'DepartmentController@destroy')->name('api.departments.destroy');//删除部门
    });

    // 角色管理（需要权限）
    Route::group(['middleware' => 'permission:system.role'], function () {
        Route::get('/roles', 'RoleController@index')->name('api.roles.index');// 获取角色列表
        Route::post('/roles', 'RoleController@store')->name('api.roles.store');// 创建角色
        Route::get('/roles/{id}', 'RoleController@show')->name('api.roles.show');// 获取角色详情
        Route::put('/roles/{id}', 'RoleController@update')->name('api.roles.update');// 更新角色信息
        Route::delete('/roles/{id}', 'RoleController@destroy')->name('api.roles.destroy');// 删除角色
        Route::get('/roles/{id}/permissions', 'RoleController@getPermissions')->name('api.roles.permissions');// 获取角色权限
        Route::post('/roles/{id}/permissions', 'RoleController@assignPermissions')->name('api.roles.assign.permissions');// 分配角色权限
    });

    // 权限管理（需要权限）
    Route::group(['middleware' => 'permission:system.permission'], function () {
        Route::get('/permissions', 'PermissionController@index')->name('api.permissions.index');// 获取权限列表
        Route::get('/permissions/types', 'PermissionController@getTypes')->name('api.permissions.types');// 获取权限类型
        Route::get('/permissions/parent-options', 'PermissionController@getParentOptions')->name('api.permissions.parent.options');// 获取父级权限选项
        Route::post('/permissions', 'PermissionController@store')->name('api.permissions.store');// 创建权限
        Route::get('/permissions/{id}', 'PermissionController@show')->name('api.permissions.show');// 获取权限详情
        Route::put('/permissions/{id}', 'PermissionController@update')->name('api.permissions.update');// 更新权限信息
        Route::delete('/permissions/{id}', 'PermissionController@destroy')->name('api.permissions.destroy');// 删除权限
    });

    // 系统配置模块API（管理员可访问）
    // 日志管理（注意路由顺序，具体路径要在参数路径之前）
    Route::get('/logs', 'LogsController@index')->name('api.logs.index');// 获取日志列表
    Route::get('/logs/types', 'LogsController@getTypes')->name('api.logs.types');// 获取日志类型列表
    Route::get('/logs/users', 'LogsController@getUsers')->name('api.logs.users');// 获取操作用户列表
    Route::get('/logs/export', 'LogsController@export')->name('api.logs.export');// 导出日志
    Route::delete('/logs/clear', 'LogsController@clear')->name('api.logs.clear');// 清空日志
    Route::post('/logs/batch-delete', 'LogsController@batchDelete')->name('api.logs.batch.delete');// 批量删除日志
    Route::get('/logs/{id}', 'LogsController@show')->name('api.logs.show');// 获取日志详情
    Route::delete('/logs/{id}', 'LogsController@destroy')->name('api.logs.destroy');// 删除日志

    // 工作流管理 - 暂时移至此处进行测试
    // Route::get('/workflows', 'WorkflowController@index')->name('api.workflows.index');
    // Route::get('/workflows/case-types', 'WorkflowController@getCaseTypes')->name('api.workflows.case.types');
    // Route::get('/workflows/assignable-users', 'WorkflowController@getAssignableUsers')->name('api.workflows.assignable.users');
    // Route::post('/workflows', 'WorkflowController@store')->name('api.workflows.store');
    // Route::get('/workflows/{id}', 'WorkflowController@show')->name('api.workflows.show');
    // Route::put('/workflows/{id}', 'WorkflowController@update')->name('api.workflows.update');
    // Route::delete('/workflows/{id}', 'WorkflowController@destroy')->name('api.workflows.destroy');
    // Route::put('/workflows/{id}/toggle-status', 'WorkflowController@toggleStatus')->name('api.workflows.toggle.status');
    // Route::get('/workflows/{id}/nodes', 'WorkflowController@getNodes')->name('api.workflows.nodes');
    // Route::put('/workflows/{id}/nodes', 'WorkflowController@updateNodes')->name('api.workflows.update.nodes');

    // 通知规则管理 - 已移至公开路由组





    // 客户管理（这些路由已移到公开区域，这里保留合同管理路由）
    // 合同管理 - 新的合同管理系统
    Route::get('/contracts', 'ContractController@index')->name('api.contracts.index');// 获取合同列表
    Route::post('/contracts', 'ContractController@store')->name('api.contracts.store');// 创建合同
    Route::post('/contracts/export', 'ContractController@export')->name('api.contracts.export');// 导出合同数据

    // 合同工作流管理（特殊路由需要放在 {id} 路由之前）
    Route::get('/contracts/progress', 'ContractController@getContractProgress')->name('api.contracts.progress');// 获取合同进度
    Route::get('/contracts/pending-count', 'ContractController@getPendingCount')->name('api.contracts.pending.count');// 获取待处理合同数量

    // 合同CRUD（带ID参数的路由）
    Route::get('/contracts/{id}', 'ContractController@show')->name('api.contracts.show');// 获取合同详情
    Route::put('/contracts/{id}', 'ContractController@update')->name('api.contracts.update');// 更新合同信息
    Route::delete('/contracts/{id}', 'ContractController@destroy')->name('api.contracts.destroy');// 删除合同

    // 合同附件管理
    Route::post('/contracts/{id}/attachments', 'ContractController@uploadAttachment')->name('api.contracts.attachments.upload');// 上传合同附件
    Route::delete('/contracts/{id}/attachments/{attachmentId}', 'ContractController@deleteAttachment')->name('api.contracts.attachments.delete');// 删除合同附件
    Route::get('/contracts/{id}/attachments/{attachmentId}/download', 'ContractController@downloadAttachment')->name('api.contracts.attachments.download');// 下载合同附件

    // 合同工作流管理（带ID参数的路由）
    Route::get('/contracts/{id}/workflow-status', 'ContractController@workflowStatus')->name('api.contracts.workflow.status');// 获取合同工作流状态（方法1）
    Route::post('/contracts/{id}/start-workflow', 'ContractController@startWorkflow')->name('api.contracts.start.workflow');// 启动合同工作流
    Route::post('/contracts/{id}/restart-workflow', 'ContractController@restartWorkflow')->name('api.contracts.restart.workflow');// 重新启动合同工作流
    Route::post('/contracts/{id}/start-workflow-with-assignees', 'ContractController@startWorkflowWithAssignees')->name('api.contracts.start.workflow.with.assignees');// 指定审批人启动合同工作流
    Route::get('/contracts/{id}/workflow-status', 'ContractController@getWorkflowStatus')->name('api.contracts.workflow.status');// 获取合同工作流状态（方法2）

    // 工作流实例管理（需要认证）
    Route::post('/workflow-instances/start', 'WorkflowInstanceController@start')->name('api.workflow.instances.start');// 启动工作流实例
    Route::post('/workflow-instances/process/{processId}', 'WorkflowInstanceController@process')->name('api.workflow.instances.process');// 处理工作流任务
    Route::get('/workflow-instances/my-tasks', 'WorkflowInstanceController@myTasks')->name('api.workflow.instances.my.tasks');// 获取我的工作流任务
    Route::put('/workflow-instances/{instanceId}/cancel', 'WorkflowInstanceController@cancel')->name('api.workflow.instances.cancel');// 取消工作流实例
    Route::get('/workflow-instances/{instanceId}/history', 'WorkflowInstanceController@history')->name('api.workflow.instances.history');// 获取工作流实例历史记录
    // 注意：assignable-users 接口已移至公开路由组，避免重复定义

    // 项目管理（需要权限）
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('/cases', 'CaseController@index')->name('api.cases.index');// 获取案件列表
        Route::post('/cases', 'CaseController@store')->name('api.cases.store');// 创建案件
        Route::get('/cases/{id}', 'CaseController@show')->name('api.cases.show');// 获取案件详情
        Route::put('/cases/{id}', 'CaseController@update')->name('api.cases.update');// 更新案件
        Route::delete('/cases/{id}', 'CaseController@destroy')->name('api.cases.destroy');// 删除案件

        // 项目费用明细
        Route::get('/cases/{id}/fees', 'CaseFeeController@index')->name('api.cases.fees.index');// 获取指定案件的费用列表
        Route::post('/cases/{id}/fees', 'CaseFeeController@store')->name('api.cases.fees.store');// 为指定案件创建费用记录
        Route::put('/case-fees/{feeId}', 'CaseFeeController@update')->name('api.case.fees.update');// 更新指定费用记录
        Route::delete('/case-fees/{feeId}', 'CaseFeeController@destroy')->name('api.case.fees.destroy');// 删除指定费用记录

        // 项目附件明细
        Route::get('/cases/{id}/attachments', 'CaseAttachmentController@index')->name('api.cases.attachments.index');// 获取项目附件列表
        Route::post('/cases/{id}/attachments', 'CaseAttachmentController@store')->name('api.cases.attachments.store');// 创建项目附件记录
        Route::post('/cases/{id}/attachments/upload', 'CaseAttachmentController@upload')->name('api.cases.attachments.upload');// 上传项目附件
        Route::delete('/case-attachments/{id}', 'CaseAttachmentController@destroy')->name('api.case.attachments.destroy');// 删除项目附件记录

        // 个人项目管理
        Route::get('/personal-cases', 'PersonalCaseController@index')->name('api.personal.cases.index');// 获取个人项目列表
        Route::get('/personal-cases/pending', 'PersonalCaseController@pending')->name('api.personal.cases.pending');// 获取待办项目列表
        Route::get('/personal-cases/pending-project', 'PersonalCaseController@pendingProject')->name('api.personal.cases.pending.project');// 获取待办项目详情
        Route::get('/personal-cases/completed', 'PersonalCaseController@completed')->name('api.personal.cases.completed');// 获取已完成项目列表
        Route::get('/personal-cases/completed-project', 'PersonalCaseController@completedProject')->name('api.personal.cases.completed.project');// 获取已完成项目详情
        Route::get('/personal-cases/department', 'PersonalCaseController@department')->name('api.personal.cases.department');// 获取部门项目列表

        // 个人项目操作
        Route::post('/personal-cases/modify-estimated-time', 'PersonalCaseController@modifyEstimatedTime')->name('api.personal.cases.modify.estimated.time');// 修改估计时间
        Route::post('/personal-cases/add-complete', 'PersonalCaseController@addComplete')->name('api.personal.cases.add.complete');// 添加完成记录
        Route::post('/personal-cases/add-process-note', 'PersonalCaseController@addProcessNote')->name('api.personal.cases.add.process.note');// 添加处理备注
        Route::post('/personal-cases/start-draft', 'PersonalCaseController@startDraft')->name('api.personal.cases.start.draft');// 开始起草
        Route::post('/personal-cases/start-supplement', 'PersonalCaseController@startSupplement')->name('api.personal.cases.start.supplement');// 开始补充资料
        Route::post('/personal-cases/add-process-follow', 'PersonalCaseController@addProcessFollow')->name('api.personal.cases.add.process.follow');// 添加流程跟进
        Route::post('/personal-cases/add-received-time', 'PersonalCaseController@addReceivedTime')->name('api.personal.cases.add.received.time');// 添加接收时间
        Route::post('/personal-cases/add-deadline', 'PersonalCaseController@addDeadline')->name('api.personal.cases.add.deadline');// 添加截止日期
        Route::post('/personal-cases/add-remark', 'PersonalCaseController@addRemark')->name('api.personal.cases.add.remark');// 添加备注
        Route::post('/personal-cases/export', 'PersonalCaseController@export')->name('api.personal.cases.export');// 导出个人项目
        Route::post('/personal-cases/modify-process-item', 'PersonalCaseController@modifyProcessItem')->name('api.personal.cases.modify.process.item');// 修改流程项

    });







    // 数据配置模块（暂时移除权限检查）
    // 客户等级设置
    Route::get('/config/customer-levels', 'CustomerLevelsController@index')->name('api.config.customer.levels.index');// 获取客户等级列表
    Route::post('/config/customer-levels', 'CustomerLevelsController@store')->name('api.config.customer.levels.store');// 未使用
    Route::get('/config/customer-levels/{id}', 'CustomerLevelsController@show')->name('api.config.customer.levels.show');// 未使用
    Route::put('/config/customer-levels/{id}', 'CustomerLevelsController@update')->name('api.config.customer.levels.update');// 未使用
    Route::delete('/config/customer-levels/{id}', 'CustomerLevelsController@destroy')->name('api.config.customer.levels.destroy');// 未使用
    Route::get('/config/customer-levels/options', 'CustomerLevelsController@options')->name('api.config.customer.levels.options');// 获取选项列表（用于下拉框等）

    // 相关类型设置
    Route::get('/config/related-types', 'RelatedTypesController@index')->name('api.config.related.types.index');// 获取相关类型列表
    Route::post('/config/related-types', 'RelatedTypesController@store')->name('api.config.related.types.store');// 创建相关类型
    Route::get('/config/related-types/{id}', 'RelatedTypesController@show')->name('api.config.related.types.show');// 获取相关类型详情
    Route::put('/config/related-types/{id}', 'RelatedTypesController@update')->name('api.config.related.types.update');// 更新相关类型
    Route::delete('/config/related-types/{id}', 'RelatedTypesController@destroy')->name('api.config.related.types.destroy');// 删除相关类型
    Route::get('/config/related-types/options', 'RelatedTypesController@options')->name('api.config.related.types.options');// 获取相关类型选项列表
    Route::get('/config/related-types/case-type-options', 'RelatedTypesController@getCaseTypeOptions')->name('api.config.related.types.case.type.options');// 获取案件类型选项列表

    // 开票服务类型设置
    Route::get('/config/invoice-services', 'InvoiceServicesController@index')->name('api.config.invoice.services.index');// 获取开票服务类型列表
    Route::post('/config/invoice-services', 'InvoiceServicesController@store')->name('api.config.invoice.services.store');// 创建开票服务类型
    Route::get('/config/invoice-services/{id}', 'InvoiceServicesController@show')->name('api.config.invoice.services.show');// 获取开票服务类型详情
    Route::put('/config/invoice-services/{id}', 'InvoiceServicesController@update')->name('api.config.invoice.services.update');// 更新开票服务类型
    Route::delete('/config/invoice-services/{id}', 'InvoiceServicesController@destroy')->name('api.config.invoice.services.destroy');// 未使用
    Route::get('/config/invoice-services/options', 'InvoiceServicesController@options')->name('api.config.invoice.services.options');// 未使用

    // 园区名称设置
    Route::get('/config/parks', 'ParksConfigController@index')->name('api.config.parks.index');// 获取园区名称列表
    Route::post('/config/parks', 'ParksConfigController@store')->name('api.config.parks.store');// 未使用
    Route::get('/config/parks/{id}', 'ParksConfigController@show')->name('api.config.parks.show');// 未使用
    Route::put('/config/parks/{id}', 'ParksConfigController@update')->name('api.config.parks.update');// 未使用
    Route::delete('/config/parks/{id}', 'ParksConfigController@destroy')->name('api.config.parks.destroy');// 未使用
    Route::get('/config/parks/options', 'ParksConfigController@options')->name('api.config.parks.options');// 获取园区选项列表
    Route::post('/notification-rules/batch', 'NotificationRuleController@batchOperation')->name('api.notification.rules.batch');// 批量操作通知规则

    // 流程规则管理路由已移至上方，避免重复

    // 代理机构管理已移至公开路由

    // 代理师管理
    Route::get('/agents', 'AgentController@index')->name('api.agents.index');// 获取代理师列表
    Route::get('/agents/agencies', 'AgentController@getAgencies')->name('api.agents.agencies');// 未使用
    Route::post('/agents', 'AgentController@store')->name('api.agents.store');// 创建代理师
    Route::get('/agents/{id}', 'AgentController@show')->name('api.agents.show');// 获取代理师详情
    Route::put('/agents/{id}', 'AgentController@update')->name('api.agents.update');// 更新代理师
    Route::delete('/agents/{id}', 'AgentController@destroy')->name('api.agents.destroy');// 删除代理师

    // 数据配置管理
    // 申请类型设置
    Route::get('/data-config/apply-types', 'ApplyTypeController@index')->name('api.apply.types.index');// 获取申请类型列表（重写方法）
    Route::get('/data-config/apply-types/options', 'ApplyTypeController@options')->name('api.apply.types.options');// 未使用
    Route::post('/data-config/apply-types', 'ApplyTypeController@store')->name('api.apply.types.store');// 创建申请类型
    Route::get('/data-config/apply-types/{id}', 'ApplyTypeController@show')->name('api.apply.types.show');// 获取申请类型详情
    Route::put('/data-config/apply-types/{id}', 'ApplyTypeController@update')->name('api.apply.types.update');// 更新申请类型
    Route::delete('/data-config/apply-types/{id}', 'ApplyTypeController@destroy')->name('api.apply.types.destroy');// 未使用
    Route::post('/data-config/apply-types/batch-status', 'ApplyTypeController@batchUpdateStatus')->name('api.apply.types.batch.status');// 未使用
    Route::get('/data-config/apply-types/all/{caseType}', 'ApplyTypeController@all')->name('api.apply.types.all');// 根据案件类型获取所有申请类型选项
    Route::get('/data-config/apply-types/all-by-country/{country}', 'ApplyTypeController@allByCountry')->name('api.apply.types.all.by.country');// 根据国家获取所有申请类型选项

    // 处理事项状态设置
    Route::get('/data-config/process-statuses', 'ProcessStatusController@index')->name('api.process.statuses.index');// 获取处理事项状态列表
    Route::get('/data-config/process-statuses/options', 'ProcessStatusController@options')->name('api.process.statuses.options');// 获取处理事项状态选项列表
    Route::post('/data-config/process-statuses', 'ProcessStatusController@store')->name('api.process.statuses.store');// 创建处理事项状态
    Route::get('/data-config/process-statuses/{id}', 'ProcessStatusController@show')->name('api.process.statuses.show');// 获取处理事项状态详情
    Route::put('/data-config/process-statuses/{id}', 'ProcessStatusController@update')->name('api.process.statuses.update');// 更新处理事项状态
    Route::delete('/data-config/process-statuses/{id}', 'ProcessStatusController@destroy')->name('api.process.statuses.destroy');// 删除处理事项状态
    Route::post('/data-config/process-statuses/batch-status', 'ProcessStatusController@batchUpdateStatus')->name('api.process.statuses.batch.status');// 批量更新处理事项状态

    // 费用配置设置
    Route::get('/data-config/fee-configs', 'FeeConfigController@index')->name('api.fee.configs.index');// 获取费用配置列表
    Route::get('/data-config/fee-configs/options', 'FeeConfigController@options')->name('api.fee.configs.options');// 未使用
    Route::post('/data-config/fee-configs', 'FeeConfigController@store')->name('api.fee.configs.store');// 创建费用配置
    Route::get('/data-config/fee-configs/{id}', 'FeeConfigController@show')->name('api.fee.configs.show');// 获取费用配置详情
    Route::put('/data-config/fee-configs/{id}', 'FeeConfigController@update')->name('api.fee.configs.update');// 更新费用配置
    Route::delete('/data-config/fee-configs/{id}', 'FeeConfigController@destroy')->name('api.fee.configs.destroy');// 未使用
    Route::post('/data-config/fee-configs/batch-status', 'FeeConfigController@batchUpdateStatus')->name('api.fee.configs.batch.status');// 未使用

    // 客户等级设置
    Route::get('/data-config/customer-levels', 'CustomerLevelController@index')->name('api.customer.levels.index');// 获取客户等级列表
    Route::get('/data-config/customer-levels/options', 'CustomerLevelController@options')->name('api.customer.levels.options');// 获取客户等级选项列表
    Route::post('/data-config/customer-levels', 'CustomerLevelController@store')->name('api.customer.levels.store');// 创建客户等级
    Route::get('/data-config/customer-levels/{id}', 'CustomerLevelController@show')->name('api.customer.levels.show');// 获取客户等级详情
    Route::put('/data-config/customer-levels/{id}', 'CustomerLevelController@update')->name('api.customer.levels.update');// 更新客户等级
    Route::delete('/data-config/customer-levels/{id}', 'CustomerLevelController@destroy')->name('api.customer.levels.destroy');// 删除客户等级
    Route::post('/data-config/customer-levels/batch-status', 'CustomerLevelController@batchUpdateStatus')->name('api.customer.levels.batch.status');// 批量更新客户等级状态

    // 跟进方式设置
    Route::get('/data-config/follow-up-methods', 'FollowUpMethodController@index')->name('api.follow.up.methods.index');// 获取跟进方式列表
    Route::get('/data-config/follow-up-methods/options', 'FollowUpMethodController@options')->name('api.follow.up.methods.options');// 未使用
    Route::post('/data-config/follow-up-methods', 'FollowUpMethodController@store')->name('api.follow.up.methods.store');// 未使用
    Route::get('/data-config/follow-up-methods/{id}', 'FollowUpMethodController@show')->name('api.follow.up.methods.show');// 未使用
    Route::put('/data-config/follow-up-methods/{id}', 'FollowUpMethodController@update')->name('api.follow.up.methods.update');// 未使用
    Route::delete('/data-config/follow-up-methods/{id}', 'FollowUpMethodController@destroy')->name('api.follow.up.methods.destroy');// 未使用
    Route::post('/data-config/follow-up-methods/batch-status', 'FollowUpMethodController@batchUpdateStatus')->name('api.follow.up.methods.batch.status');// 未使用

    // 跟进类型设置
    Route::get('/data-config/follow-up-types', 'FollowUpTypeController@index')->name('api.follow.up.types.index');// 获取跟进类型列表
    Route::get('/data-config/follow-up-types/options', 'FollowUpTypeController@options')->name('api.follow.up.types.options');// 未使用
    Route::post('/data-config/follow-up-types', 'FollowUpTypeController@store')->name('api.follow.up.types.store');// 未使用
    Route::get('/data-config/follow-up-types/{id}', 'FollowUpTypeController@show')->name('api.follow.up.types.show');// 未使用
    Route::put('/data-config/follow-up-types/{id}', 'FollowUpTypeController@update')->name('api.follow.up.types.update');// 未使用
    Route::delete('/data-config/follow-up-types/{id}', 'FollowUpTypeController@destroy')->name('api.follow.up.types.destroy');// 未使用
    Route::post('/data-config/follow-up-types/batch-status', 'FollowUpTypeController@batchUpdateStatus')->name('api.follow.up.types.batch.status');// 未使用

    // 商机状态设置
    Route::get('/data-config/business-statuses', 'BusinessStatusController@index')->name('api.business.statuses.index');// 获取商机状态列表
    Route::get('/data-config/business-statuses/options', 'BusinessStatusController@options')->name('api.business.statuses.options');// 获取商机状态选项列表
    Route::post('/data-config/business-statuses', 'BusinessStatusController@store')->name('api.business.statuses.store');// 添加商机状态
    Route::get('/data-config/business-statuses/{id}', 'BusinessStatusController@show')->name('api.business.statuses.show');// 获取商机状态详情
    Route::put('/data-config/business-statuses/{id}', 'BusinessStatusController@update')->name('api.business.statuses.update');// 修改商机状态
    Route::delete('/data-config/business-statuses/{id}', 'BusinessStatusController@destroy')->name('api.business.statuses.destroy');// 删除商机状态
    Route::post('/data-config/business-statuses/batch-status', 'BusinessStatusController@batchUpdateStatus')->name('api.business.statuses.batch.status');// 未使用

    // 项目状态设置
    Route::get('/data-config/case-statuses', 'CaseStatusesController@index')->name('api.case.statuses.index');// 获取项目状态列表
    Route::get('/data-config/case-statuses/options', 'CaseStatusesController@options')->name('api.case.statuses.options');// 获取项目状态选项列表
    Route::post('/data-config/case-statuses', 'CaseStatusesController@store')->name('api.case.statuses.store');// 创建项目状态
    Route::get('/data-config/case-statuses/{id}', 'CaseStatusesController@show')->name('api.case.statuses.show');// 获取项目状态详情
    Route::put('/data-config/case-statuses/{id}', 'CaseStatusesController@update')->name('api.case.statuses.update');// 更新项目状态
    Route::delete('/data-config/case-statuses/{id}', 'CaseStatusesController@destroy')->name('api.case.statuses.destroy');// 删除项目状态
    Route::post('/data-config/case-statuses/batch-status', 'CaseStatusesController@batchUpdateStatus')->name('api.case.statuses.batch.status');// 批量更新项目状态

    // 商机类型设置
    Route::get('/data-config/opportunity-types', 'OpportunityTypeController@index')->name('api.opportunity.types.index');// 获取商机类型列表
    Route::get('/data-config/opportunity-types/options', 'OpportunityTypeController@options')->name('api.opportunity.types.options');// 获取商机类型选项列表
    Route::post('/data-config/opportunity-types', 'OpportunityTypeController@store')->name('api.opportunity.types.store');// 添加商机类型
    Route::get('/data-config/opportunity-types/{id}', 'OpportunityTypeController@show')->name('api.opportunity.types.show');// 获取商机类型详情
    Route::put('/data-config/opportunity-types/{id}', 'OpportunityTypeController@update')->name('api.opportunity.types.update');// 修改商机类型
    Route::delete('/data-config/opportunity-types/{id}', 'OpportunityTypeController@destroy')->name('api.opportunity.types.destroy');// 删除商机类型
    Route::post('/data-config/opportunity-types/batch-status', 'OpportunityTypeController@batchUpdateStatus')->name('api.opportunity.types.batch.status');// 未使用

    // 跟进进度设置
    Route::get('/data-config/follow-up-progresses', 'FollowUpProgressController@index')->name('api.follow.up.progresses.index');// 获取跟进进度列表
    Route::get('/data-config/follow-up-progresses/options', 'FollowUpProgressController@options')->name('api.follow.up.progresses.options');// 未使用
    Route::post('/data-config/follow-up-progresses', 'FollowUpProgressController@store')->name('api.follow.up.progresses.store');// 未使用
    Route::get('/data-config/follow-up-progresses/{id}', 'FollowUpProgressController@show')->name('api.follow.up.progresses.show');// 未使用
    Route::put('/data-config/follow-up-progresses/{id}', 'FollowUpProgressController@update')->name('api.follow.up.progresses.update');// 未使用
    Route::delete('/data-config/follow-up-progresses/{id}', 'FollowUpProgressController@destroy')->name('api.follow.up.progresses.destroy');// 未使用
    Route::post('/data-config/follow-up-progresses/batch-status', 'FollowUpProgressController@batchUpdateStatus')->name('api.follow.up.progresses.batch.status');// 未使用

    // 客户规模设置
    Route::get('/data-config/customer-scales', 'CustomerScaleController@index')->name('api.customer.scales.index');// 获取客户规模列表
    Route::get('/data-config/customer-scales/options', 'CustomerScaleController@options')->name('api.customer.scales.options');// 获取客户规模选项列表
    Route::post('/data-config/customer-scales', 'CustomerScaleController@store')->name('api.customer.scales.store');// 添加客户规模
    Route::get('/data-config/customer-scales/{id}', 'CustomerScaleController@show')->name('api.customer.scales.show');// 获取客户规模详情
    Route::put('/data-config/customer-scales/{id}', 'CustomerScaleController@update')->name('api.customer.scales.update');// 修改客户规模
    Route::delete('/data-config/customer-scales/{id}', 'CustomerScaleController@destroy')->name('api.customer.scales.destroy');// 删除客户规模
    Route::post('/data-config/customer-scales/batch-status', 'CustomerScaleController@batchUpdateStatus')->name('api.customer.scales.batch.status');// 未使用



    // 处理事项信息设置
    Route::get('/data-config/process-informations', 'ProcessInformationController@index')->name('api.process.informations.index');// 获取处理事项信息列表
    Route::get('/data-config/process-informations/options', 'ProcessInformationController@options')->name('api.process.informations.options');// 获取处理事项信息选项列表
    Route::get('/data-config/process-informations/filtered-options', 'ProcessInformationController@getFilteredOptions')->name('api.process.informations.filtered.options');// 获取过滤后的处理事项信息选项
    Route::get('/data-config/process-informations/by-apply-type', 'ProcessInformationController@getByApplyType')->name('api.process.informations.by.apply.type');// 根据申请类型获取处理事项信息
    Route::post('/data-config/process-informations', 'ProcessInformationController@store')->name('api.process.informations.store');// 创建处理事项信息
    Route::get('/data-config/process-informations/{id}', 'ProcessInformationController@show')->name('api.process.informations.show');// 获取处理事项信息详情
    Route::put('/data-config/process-informations/{id}', 'ProcessInformationController@update')->name('api.process.informations.update');// 更新处理事项信息
    Route::delete('/data-config/process-informations/{id}', 'ProcessInformationController@destroy')->name('api.process.informations.destroy');// 删除处理事项信息
    Route::post('/data-config/process-informations/batch-status', 'ProcessInformationController@batchUpdateStatus')->name('api.process.informations.batch.status');// 批量更新处理事项信息状态

    // 处理事项系数设置
    Route::get('/data-config/process-coefficients', 'ProcessCoefficientsController@index')->name('api.process.coefficients.index');// 获取处理事项系数列表
    Route::get('/data-config/process-coefficients/options', 'ProcessCoefficientsController@options')->name('api.process.coefficients.options');// 获取处理事项系数选项列表
    Route::post('/data-config/process-coefficients', 'ProcessCoefficientsController@store')->name('api.process.coefficients.store');// 创建处理事项系数
    Route::get('/data-config/process-coefficients/{id}', 'ProcessCoefficientsController@show')->name('api.process.coefficients.show');// 获取处理事项系数详情
    Route::put('/data-config/process-coefficients/{id}', 'ProcessCoefficientsController@update')->name('api.process.coefficients.update');// 更新处理事项系数
    Route::delete('/data-config/process-coefficients/{id}', 'ProcessCoefficientsController@destroy')->name('api.process.coefficients.destroy');// 删除处理事项系数
    Route::post('/data-config/process-coefficients/batch-status', 'ProcessCoefficientsController@batchUpdateStatus')->name('api.process.coefficients.batch.status');// 批量更新处理事项系数状态

    // 项目处理事项管理
    Route::get('/case-processes', 'CaseProcessController@index')->name('api.case.processes.index');// 获取处理事项列表
    Route::post('/case-processes', 'CaseProcessController@store')->name('api.case.processes.store');// 创建处理事项
    // 项目处理事项更新管理 - 必须放在 {id} 路由之前
    Route::get('/case-processes/update-list', 'CaseProcessController@getUpdateList')->name('api.case.processes.update.list');// 获取需要更新处理事项的项目列表
    Route::get('/case-processes/case/{caseId}/detail', 'CaseProcessController@getCaseDetail')->name('api.case.processes.case.detail');// 获取项目处理详情
    Route::get('/case-processes/case/{caseId}', 'CaseProcessController@getCaseProcesses')->name('api.case.processes.case.processes');// 获取项目的处理事项列表
    Route::post('/case-processes/case/{caseId}/update', 'CaseProcessController@updateCaseProcesses')->name('api.case.processes.case.update');// 更新项目的处理事项
    Route::post('/case-processes/batch-update', 'CaseProcessController@batchUpdate')->name('api.case.processes.batch.update');// 批量更新项目处理事项
    Route::get('/case-processes/{id}', 'CaseProcessController@show')->name('api.case.processes.show');// 获取处理事项详情
    Route::put('/case-processes/{id}', 'CaseProcessController@update')->name('api.case.processes.update');// 修改处理事项
    Route::delete('/case-processes/{id}', 'CaseProcessController@destroy')->name('api.case.processes.destroy');// 删除处理事项

    // 项目系数设置
    Route::get('/data-config/case-coefficients', 'CaseCoefficientController@index')->name('api.case.coefficients.index');// 获取项目系数列表
    Route::get('/data-config/case-coefficients/options', 'CaseCoefficientController@options')->name('api.case.coefficients.options');// 获取项目系数选项列表
    Route::post('/data-config/case-coefficients', 'CaseCoefficientController@store')->name('api.case.coefficients.store');// 创建项目系数
    Route::get('/data-config/case-coefficients/{id}', 'CaseCoefficientController@show')->name('api.case.coefficients.show');// 获取项目系数详情
    Route::put('/data-config/case-coefficients/{id}', 'CaseCoefficientController@update')->name('api.case.coefficients.update');// 更新项目系数
    Route::delete('/data-config/case-coefficients/{id}', 'CaseCoefficientController@destroy')->name('api.case.coefficients.destroy');// 删除项目系数
    Route::post('/data-config/case-coefficients/batch-status', 'CaseCoefficientController@batchUpdateStatus')->name('api.case.coefficients.batch.status');// 批量更新项目系数状态



    // 相关类型设置
    Route::get('/data-config/related-types', 'RelatedTypesController@index')->name('api.related.types.index');// 获取相关类型列表
    Route::get('/data-config/related-types/options', 'RelatedTypesController@options')->name('api.related.types.options');// 获取相关类型选项列表
    Route::post('/data-config/related-types', 'RelatedTypesController@store')->name('api.related.types.store');// 创建相关类型
    Route::get('/data-config/related-types/{id}', 'RelatedTypesController@show')->name('api.related.types.show');// 获取相关类型详情
    Route::put('/data-config/related-types/{id}', 'RelatedTypesController@update')->name('api.related.types.update');// 更新相关类型
    Route::delete('/data-config/related-types/{id}', 'RelatedTypesController@destroy')->name('api.related.types.destroy');// 删除相关类型
    Route::post('/data-config/related-types/batch-status', 'RelatedTypesController@batchUpdateStatus')->name('api.related.types.batch.status');// 批量更新相关类型状态

    // 开票服务类型设置
    Route::get('/data-config/invoice-service-types', 'InvoiceServiceTypesController@index')->name('api.invoice.service.types.index');// 获取开票服务类型列表
    Route::get('/data-config/invoice-service-types/options', 'InvoiceServiceTypesController@options')->name('api.invoice.service.types.options');// 获取开票服务类型选项列表
    Route::post('/data-config/invoice-service-types', 'InvoiceServiceTypesController@store')->name('api.invoice.service.types.store');// 创建开票服务类型
    Route::get('/data-config/invoice-service-types/{id}', 'InvoiceServiceTypesController@show')->name('api.invoice.service.types.show');// 获取开票服务类型详情
    Route::put('/data-config/invoice-service-types/{id}', 'InvoiceServiceTypesController@update')->name('api.invoice.service.types.update');// 更新开票服务类型
    Route::delete('/data-config/invoice-service-types/{id}', 'InvoiceServiceTypesController@destroy')->name('api.invoice.service.types.destroy');// 删除开票服务类型
    Route::post('/data-config/invoice-service-types/batch-status', 'InvoiceServiceTypesController@batchUpdateStatus')->name('api.invoice.service.types.batch.status');// 批量更新开票服务类型状态



    // 文件分类设置
    Route::get('/data-config/file-categories', 'FileCategoriesController@index')->name('api.file.categories.index');// 获取文件分类列表
    Route::get('/data-config/file-categories/options', 'FileCategoriesController@options')->name('api.file.categories.options');// 获取文件分类选项列表
    Route::post('/data-config/file-categories', 'FileCategoriesController@store')->name('api.file.categories.store');// 创建文件分类
    Route::get('/data-config/file-categories/{id}', 'FileCategoriesController@show')->name('api.file.categories.show');// 获取文件分类详情
    Route::put('/data-config/file-categories/{id}', 'FileCategoriesController@update')->name('api.file.categories.update');// 修改文件分类
    Route::delete('/data-config/file-categories/{id}', 'FileCategoriesController@destroy')->name('api.file.categories.destroy');// 删除文件分类
    Route::post('/data-config/file-categories/batch-status', 'FileCategoriesController@batchUpdateStatus')->name('api.file.categories.batch.status');// 未使用

    // 文件描述设置
    Route::get('/data-config/file-descriptions', 'FileDescriptionsController@index')->name('api.file.descriptions.index');// 获取文件描述列表
    Route::get('/data-config/file-descriptions/options', 'FileDescriptionsController@options')->name('api.file.descriptions.options');// 获取文件描述选项列表
    Route::get('/data-config/file-descriptions/tree', 'FileDescriptionsController@getTree')->name('api.file.descriptions.tree');// 获取文件描述树结构
    Route::get('/data-config/file-descriptions/file-category-major', 'FileDescriptionsController@getFileCategoryMajor')->name('api.file.descriptions.file.category.major');// 获取文件描述主分类
    Route::get('/data-config/file-descriptions/file-category-minor', 'FileDescriptionsController@getFileCategoryMinor')->name('api.file.descriptions.file.category.minor');// 获取文件描述子分类
    Route::post('/data-config/file-descriptions', 'FileDescriptionsController@store')->name('api.file.descriptions.store');// 创建文件描述
    Route::get('/data-config/file-descriptions/{id}', 'FileDescriptionsController@show')->name('api.file.descriptions.show');// 获取文件描述详情
    Route::put('/data-config/file-descriptions/{id}', 'FileDescriptionsController@update')->name('api.file.descriptions.update');// 修改文件描述
    Route::delete('/data-config/file-descriptions/{id}', 'FileDescriptionsController@destroy')->name('api.file.descriptions.destroy');// 删除文件描述
    Route::post('/data-config/file-descriptions/batch-status', 'FileDescriptionsController@batchUpdateStatus')->name('api.file.descriptions.batch.status');// 批量更新文件描述状态



    // 版权加快类型设置
    Route::get('/data-config/copyright-expedite-types', 'CopyrightExpediteTypesController@index')->name('api.copyright.expedite.types.index');// 获取版权加快类型列表
    Route::get('/data-config/copyright-expedite-types/options', 'CopyrightExpediteTypesController@options')->name('api.copyright.expedite.types.options');// 获取版权加快类型选项列表
    Route::post('/data-config/copyright-expedite-types', 'CopyrightExpediteTypesController@store')->name('api.copyright.expedite.types.store');// 创建版权加快类型
    Route::get('/data-config/copyright-expedite-types/{id}', 'CopyrightExpediteTypesController@show')->name('api.copyright.expedite.types.show');// 获取版权加快类型详情
    Route::put('/data-config/copyright-expedite-types/{id}', 'CopyrightExpediteTypesController@update')->name('api.copyright.expedite.types.update');// 更新版权加快类型
    Route::delete('/data-config/copyright-expedite-types/{id}', 'CopyrightExpediteTypesController@destroy')->name('api.copyright.expedite.types.destroy');// 删除版权加快类型
    Route::post('/data-config/copyright-expedite-types/batch-status', 'CopyrightExpediteTypesController@batchUpdateStatus')->name('api.copyright.expedite.types.batch.status');// 批量更新版权加快类型状态

    // 我方公司设置
    Route::get('/data-config/our-companies', 'OurCompaniesController@index')->name('api.our.companies.index');// 获取我方公司列表
    Route::get('/data-config/our-companies/options', 'OurCompaniesController@options')->name('api.our.companies.options');// 获取我方公司选项列表
    Route::post('/data-config/our-companies', 'OurCompaniesController@store')->name('api.our.companies.store');// 创建我方公司
    Route::get('/data-config/our-companies/{id}', 'OurCompaniesController@show')->name('api.our.companies.show');// 获取我方公司详情
    Route::put('/data-config/our-companies/{id}', 'OurCompaniesController@update')->name('api.our.companies.update');// 修改我方公司
    Route::delete('/data-config/our-companies/{id}', 'OurCompaniesController@destroy')->name('api.our.companies.destroy');// 删除我方公司

    // 提成类型设置
    Route::get('/data-config/commission-types', 'CommissionTypesController@index')->name('api.commission.types.index');// 获取提成类型列表
    Route::get('/data-config/commission-types/options', 'CommissionTypesController@options')->name('api.commission.types.options');// 获取提成类型选项列表
    Route::post('/data-config/commission-types', 'CommissionTypesController@store')->name('api.commission.types.store');// 创建提成类型
    Route::get('/data-config/commission-types/{id}', 'CommissionTypesController@show')->name('api.commission.types.show');// 获取提成类型详情
    Route::put('/data-config/commission-types/{id}', 'CommissionTypesController@update')->name('api.commission.types.update');// 更新提成类型
    Route::delete('/data-config/commission-types/{id}', 'CommissionTypesController@destroy')->name('api.commission.types.destroy');// 删除提成类型
    Route::post('/data-config/commission-types/batch-status', 'CommissionTypesController@batchUpdateStatus')->name('api.commission.types.batch.status');// 批量更新提成类型状态

    // 提成配置设置
    Route::get('/data-config/commission-settings', 'CommissionSettingsController@index')->name('api.commission.settings.index');// 获取提成配置列表
    Route::get('/data-config/commission-settings/options', 'CommissionSettingsController@options')->name('api.commission.settings.options');// 获取提成配置选项列表
    Route::post('/data-config/commission-settings', 'CommissionSettingsController@store')->name('api.commission.settings.store');// 创建提成配置
    Route::get('/data-config/commission-settings/{id}', 'CommissionSettingsController@show')->name('api.commission.settings.show');// 获取提成配置详情
    Route::put('/data-config/commission-settings/{id}', 'CommissionSettingsController@update')->name('api.commission.settings.update');// 更新提成配置
    Route::delete('/data-config/commission-settings/{id}', 'CommissionSettingsController@destroy')->name('api.commission.settings.destroy');// 删除提成配置
    Route::post('/data-config/commission-settings/batch-status', 'CommissionSettingsController@batchUpdateStatus')->name('api.commission.settings.batch.status');// 批量更新提成配置状态



    // 处理事项系数设置
    Route::get('/data-config/process-coefficients', 'ProcessCoefficientsController@index')->name('api.process.coefficients.index');
    Route::get('/data-config/process-coefficients/options', 'ProcessCoefficientsController@options')->name('api.process.coefficients.options');
    Route::post('/data-config/process-coefficients', 'ProcessCoefficientsController@store')->name('api.process.coefficients.store');
    Route::get('/data-config/process-coefficients/{id}', 'ProcessCoefficientsController@show')->name('api.process.coefficients.show');
    Route::put('/data-config/process-coefficients/{id}', 'ProcessCoefficientsController@update')->name('api.process.coefficients.update');
    Route::delete('/data-config/process-coefficients/{id}', 'ProcessCoefficientsController@destroy')->name('api.process.coefficients.destroy');
    Route::post('/data-config/process-coefficients/batch-status', 'ProcessCoefficientsController@batchUpdateStatus')->name('api.process.coefficients.batch.status');



    // 专利年费配置
    Route::get('/data-config/patent-annual-fees', 'PatentAnnualFeesController@index')->name('api.patent.annual.fees.index');// 获取专利年费列表
    Route::get('/data-config/patent-annual-fees/options', 'PatentAnnualFeesController@options')->name('api.patent.annual.fees.options');// 获取专利年费选项列表
    Route::post('/data-config/patent-annual-fees', 'PatentAnnualFeesController@store')->name('api.patent.annual.fees.store');// 创建专利年费
    Route::get('/data-config/patent-annual-fees/{id}', 'PatentAnnualFeesController@show')->name('api.patent.annual.fees.show');// 未使用
    Route::put('/data-config/patent-annual-fees/{id}', 'PatentAnnualFeesController@update')->name('api.patent.annual.fees.update');// 修改专利年费
    Route::delete('/data-config/patent-annual-fees/{id}', 'PatentAnnualFeesController@destroy')->name('api.patent.annual.fees.destroy');// 未使用

    // 专利年费详情管理
    Route::get('/data-config/patent-annual-fees/{id}/details', 'PatentAnnualFeesController@getFeeDetails')->name('api.patent.annual.fees.details');// 获取专利年费详情列表
    Route::post('/data-config/patent-annual-fee-details', 'PatentAnnualFeesController@createFeeDetail')->name('api.patent.annual.fee.details.store');// 创建专利年费详情
    Route::put('/data-config/patent-annual-fee-details/{id}', 'PatentAnnualFeesController@updateFeeDetail')->name('api.patent.annual.fee.details.update');// 修改专利年费详情
    Route::delete('/data-config/patent-annual-fee-details/{id}', 'PatentAnnualFeesController@deleteFeeDetail')->name('api.patent.annual.fee.details.destroy');// 删除专利年费详情

    // 科技服务类型设置
    Route::get('/data-config/tech-service-types', 'TechServiceTypesController@index')->name('api.tech.service.types.index');// 获取科技服务类型列表
    Route::get('/data-config/tech-service-types/options', 'TechServiceTypesController@options')->name('api.tech.service.types.options');// 获取科技服务类型选项列表
    Route::post('/data-config/tech-service-types', 'TechServiceTypesController@store')->name('api.tech.service.types.store');// 创建科技服务类型
    Route::get('/data-config/tech-service-types/{id}', 'TechServiceTypesController@show')->name('api.tech.service.types.show');// 获取科技服务类型详情
    Route::put('/data-config/tech-service-types/{id}', 'TechServiceTypesController@update')->name('api.tech.service.types.update');// 更新科技服务类型
    Route::delete('/data-config/tech-service-types/{id}', 'TechServiceTypesController@destroy')->name('api.tech.service.types.destroy');// 删除科技服务类型
    Route::post('/data-config/tech-service-types/batch-status', 'TechServiceTypesController@batchUpdateStatus')->name('api.tech.service.types.batch.status');// 批量更新科技服务类型状态

    // 科技服务事项设置
    Route::get('/data-config/tech-service-items', 'TechServiceItemsController@index')->name('api.tech.service.items.index');// 获取科技服务事项列表
    Route::get('/data-config/tech-service-items/type/{typeId}', 'TechServiceItemsController@getByTypeId')->name('api.tech.service.items.by.type');// 根据类型ID获取科技服务事项

    // 查询功能API
    Route::prefix('search')->group(function () {
        // 专利查询
        Route::get('/patents', 'SearchController@searchPatents')->name('api.search.patents');// 搜索专利信息
        Route::post('/patents/export', 'SearchController@exportPatents')->name('api.search.patents.export');// 导出专利搜索结果

        // 商标查询
        Route::get('/trademarks', 'SearchController@searchTrademarks')->name('api.search.trademarks');// 搜索商标信息
        Route::post('/trademarks/export', 'SearchController@exportTrademarks')->name('api.search.trademarks.export');// 导出商标搜索结果

        // 版权查询
        Route::get('/copyrights', 'SearchController@searchCopyrights')->name('api.search.copyrights');// 搜索版权信息
        Route::post('/copyrights/export', 'SearchController@exportCopyrights')->name('api.search.copyrights.export');// 导出版权搜索结果

        // 科服查询
        Route::get('/projects', 'SearchController@searchProjects')->name('api.search.projects');// 搜索科服项目信息
        Route::post('/projects/export', 'SearchController@exportProjects')->name('api.search.projects.export');// 导出科服项目搜索结果

        // 详情页API
        Route::get('/patents/{id}/detail', 'SearchController@getPatentDetail')->name('api.search.patents.detail');// 获取专利详情
        Route::get('/trademarks/{id}/detail', 'SearchController@getTrademarkDetail')->name('api.search.trademarks.detail');// 获取商标详情
        Route::get('/copyrights/{id}/detail', 'SearchController@getCopyrightDetail')->name('api.search.copyrights.detail');// 获取版权详情
        Route::get('/projects/{id}/detail', 'SearchController@getProjectDetail')->name('api.search.projects.detail');// 获取科服项目详情

        // 文件管理查询（已移至公开路由）

        // 辅助数据接口
        Route::get('/business-persons', 'SearchController@getBusinessPersons')->name('api.search.business.persons');// 获取业务人员列表
        Route::get('/case-handlers', 'SearchController@getCaseHandlers')->name('api.search.case.handlers');// 获取案件办理人员列表
        Route::get('/tech-leaders', 'SearchController@getTechLeaders')->name('api.search.tech.leaders');// 获取技术负责人列表
        Route::get('/regions', 'SearchController@getRegions')->name('api.search.regions');// 获取地区列表
        Route::get('/agencies', 'SearchController@getAgencies')->name('api.search.agencies');// 获取代理机构列表
        Route::get('/departments', 'SearchController@getDepartments')->name('api.search.departments');// 获取部门列表
        Route::get('/countries', 'SearchController@getCountries')->name('api.search.countries');// 获取国家列表
    });
    Route::get('/data-config/tech-service-items/region/{regionId}', 'TechServiceItemsController@getByRegionId')->name('api.tech.service.items.by.region');// 根据地区ID获取科技服务事项
    Route::post('/data-config/tech-service-items', 'TechServiceItemsController@store')->name('api.tech.service.items.store');// 创建科技服务事项
    Route::get('/data-config/tech-service-items/{id}', 'TechServiceItemsController@show')->name('api.tech.service.items.show');// 获取科技服务事项详情
    Route::put('/data-config/tech-service-items/{id}', 'TechServiceItemsController@update')->name('api.tech.service.items.update');// 更新科技服务事项
    Route::delete('/data-config/tech-service-items/{id}', 'TechServiceItemsController@destroy')->name('api.tech.service.items.destroy');// 删除科技服务事项

    // 科技服务地区设置
    Route::get('/data-config/tech-service-regions', 'TechServiceRegionsController@index')->name('api.tech.service.regions.index');// 获取科技服务地区列表
    Route::get('/data-config/tech-service-regions/tree', 'TechServiceRegionsController@getTreeData')->name('api.tech.service.regions.tree');// 获取科技服务地区树形数据
    Route::post('/data-config/tech-service-regions', 'TechServiceRegionsController@store')->name('api.tech.service.regions.store');// 创建科技服务地区
    Route::get('/data-config/tech-service-regions/{id}', 'TechServiceRegionsController@show')->name('api.tech.service.regions.show');// 获取科技服务地区详情
    Route::put('/data-config/tech-service-regions/{id}', 'TechServiceRegionsController@update')->name('api.tech.service.regions.update');// 更新科技服务地区
    Route::delete('/data-config/tech-service-regions/{id}', 'TechServiceRegionsController@destroy')->name('api.tech.service.regions.destroy');// 删除科技服务地区

    // 审核打分项设置
    Route::get('/data-config/manuscript-scoring-items', 'ManuscriptScoringItemsController@index')->name('api.manuscript.scoring.items.index');// 未使用
    Route::get('/data-config/manuscript-scoring-items/options', 'ManuscriptScoringItemsController@options')->name('api.manuscript.scoring.items.options');// 未使用
    Route::post('/data-config/manuscript-scoring-items', 'ManuscriptScoringItemsController@store')->name('api.manuscript.scoring.items.store');// 未使用
    Route::get('/data-config/manuscript-scoring-items/{id}', 'ManuscriptScoringItemsController@show')->name('api.manuscript.scoring.items.show');// 未使用
    Route::put('/data-config/manuscript-scoring-items/{id}', 'ManuscriptScoringItemsController@update')->name('api.manuscript.scoring.items.update');// 未使用
    Route::delete('/data-config/manuscript-scoring-items/{id}', 'ManuscriptScoringItemsController@destroy')->name('api.manuscript.scoring.items.destroy');// 未使用
    Route::post('/data-config/manuscript-scoring-items/batch-status', 'ManuscriptScoringItemsController@batchUpdateStatus')->name('api.manuscript.scoring.items.batch.status');// 未使用

    // 保护中心设置
    Route::get('/data-config/protection-centers', 'ProtectionCentersController@index')->name('api.protection.centers.index');// 获取保护中心列表
    Route::get('/data-config/protection-centers/options', 'ProtectionCentersController@options')->name('api.protection.centers.options');// 获取保护中心选项列表
    Route::post('/data-config/protection-centers', 'ProtectionCentersController@store')->name('api.protection.centers.store');// 创建保护中心
    Route::get('/data-config/protection-centers/{id}', 'ProtectionCentersController@show')->name('api.protection.centers.show');// 获取保护中心详情
    Route::put('/data-config/protection-centers/{id}', 'ProtectionCentersController@update')->name('api.protection.centers.update');// 更新保护中心
    Route::delete('/data-config/protection-centers/{id}', 'ProtectionCentersController@destroy')->name('api.protection.centers.destroy');// 删除保护中心
    Route::post('/data-config/protection-centers/batch-status', 'ProtectionCentersController@batchUpdateStatus')->name('api.protection.centers.batch.status');// 批量更新保护中心状态

    // 合同项目管理
    Route::get('/contract-cases', 'ContractCaseController@index')->name('api.contract.cases.index');// 获取案例列表
    Route::get('/contract-cases/{id}', 'ContractCaseController@show')->name('api.contract.cases.show');// 获取案例详情
    Route::post('/contract-cases', 'ContractCaseController@store')->name('api.contract.cases.store');// 创建案例
    Route::put('/contract-cases/{id}', 'ContractCaseController@update')->name('api.contract.cases.update');// 更新案例
    Route::delete('/contract-cases/{id}', 'ContractCaseController@destroy')->name('api.contract.cases.destroy');// 删除案例
    Route::post('/contract-cases/{id}/start-workflow', 'ContractCaseController@startWorkflow')->name('api.contract.cases.start.workflow');// 未使用
    Route::get('/contract-cases/{id}/workflow', 'ContractCaseController@getWorkflow')->name('api.contract.cases.workflow');// 未使用
    Route::get('/contract-cases/debug/pending', 'ContractCaseController@debugPendingData')->name('api.contract.cases.debug.pending');// 未使用

// 工作流配置API
Route::get('/workflows', function(\Illuminate\Http\Request $request) {
    $code = $request->get('code');
    if ($code) {
        $workflow = \App\Models\Workflow::where('code', $code)->where('status', 1)->first();
        return response()->json([
            'success' => true,
            'data' => $workflow
        ]);
    }

    $workflows = \App\Models\Workflow::where('status', 1)->get();
    return response()->json([
        'success' => true,
        'data' => $workflows
    ]);
});// 获取工作流配置信息

// 测试路由
Route::get('/test-workflow-config', function(\Illuminate\Http\Request $request) {
    $controller = new \App\Http\Controllers\Api\WorkflowConfigController();
    return $controller->getList($request);
});// 测试工作流配置API
    Route::get('/contract-cases/options/case-types', 'ContractCaseController@getCaseTypeOptions')->name('api.contract.cases.options.case.types');// 获取合同案例类型选项
    Route::get('/contract-cases/options/case-statuses', 'ContractCaseController@getCaseStatusOptions')->name('api.contract.cases.options.case.statuses');// 获取合同案例状态选项

    // 合同项目记录管理
    Route::get('/contract-case-records', 'ContractCaseRecordController@index')->name('api.contract.case.records.index');// 获取合同项目记录列表
    Route::get('/contract-case-records/{id}', 'ContractCaseRecordController@show')->name('api.contract.case.records.show');// 获取合同项目记录详情
    Route::get('/contract-case-records/by-case/{caseId}', 'ContractCaseRecordController@getByCaseId')->name('api.contract.case.records.by.case');// 根据案例ID获取合同项目记录
    Route::post('/contract-case-records', 'ContractCaseRecordController@store')->name('api.contract.case.records.store');// 创建合同项目记录
    Route::put('/contract-case-records/{id}', 'ContractCaseRecordController@update')->name('api.contract.case.records.update');// 更新合同项目记录
    Route::delete('/contract-case-records/{id}', 'ContractCaseRecordController@destroy')->name('api.contract.case.records.destroy');// 删除合同项目记录
    Route::post('/contract-case-records/{id}/file', 'ContractCaseRecordController@file')->name('api.contract.case.records.file');// 上传合同项目记录文件

});

// 发票管理路由
Route::prefix('invoice-applications')->group(function () {
    // 获取发票申请列表
    Route::get('/', 'InvoiceApplicationController@index')->name('api.invoice.applications.index');
    // 获取发票申请统计数据
    Route::get('/statistics', 'InvoiceApplicationController@statistics')->name('api.invoice.applications.statistics');
    // 获取待处理发票申请列表
    Route::get('/pending', 'InvoiceApplicationController@pending')->name('api.invoice.applications.pending');
    // 获取待处理发票申请统计数据
    Route::get('/pending/statistics', 'InvoiceApplicationController@pendingStats')->name('api.invoice.applications.pending.stats');
    // 获取发票申请详情
    Route::get('/{id}', 'InvoiceApplicationController@show')->name('api.invoice.applications.show');
    // 创建发票申请
    Route::post('/', 'InvoiceApplicationController@store')->name('api.invoice.applications.store');
    // 更新发票申请
    Route::put('/{id}', 'InvoiceApplicationController@update')->name('api.invoice.applications.update');
    // 删除发票申请
    Route::delete('/{id}', 'InvoiceApplicationController@destroy')->name('api.invoice.applications.destroy');
    // 保存发票申请草稿
    Route::post('/draft', 'InvoiceApplicationController@saveDraft')->name('api.invoice.applications.draft');

    // 审批相关
    // 审批发票申请
    Route::post('/{id}/approve', 'InvoiceApplicationController@approve')->name('api.invoice.applications.approve');
    // 拒绝发票申请
    Route::post('/{id}/reject', 'InvoiceApplicationController@reject')->name('api.invoice.applications.reject');
    // 转交发票申请
    Route::post('/{id}/transfer', 'InvoiceApplicationController@transfer')->name('api.invoice.applications.transfer');
    // 批量审批发票申请
    Route::post('/batch-approve', 'InvoiceApplicationController@batchApprove')->name('api.invoice.applications.batch.approve');
    // 批量拒绝发票申请
    Route::post('/batch-reject', 'InvoiceApplicationController@batchReject')->name('api.invoice.applications.batch.reject');

    // 上传相关
    // 提交发票申请上传信息
    Route::post('/{id}/upload-info', 'InvoiceApplicationController@submitUpload')->name('api.invoice.applications.upload.info');

    // 历史记录
    // 获取发票申请历史记录
    Route::get('/{id}/history', 'InvoiceApplicationController@history')->name('api.invoice.applications.history');
});

// 发票查询路由
Route::prefix('invoices')->group(function () {
    // 查询发票
    Route::get('/query', 'InvoiceQueryController@index')->name('api.invoices.query');
    // 获取发票查询统计数据
    Route::get('/query/statistics', 'InvoiceQueryController@statistics')->name('api.invoices.query.statistics');
    // 下载发票
    Route::get('/{id}/download', 'InvoiceQueryController@download')->name('api.invoices.download');
    // 获取发票下载记录
    Route::get('/{id}/download-records', 'InvoiceQueryController@downloadRecords')->name('api.invoices.download.records');
    // 导出发票数据
    Route::post('/export', 'InvoiceQueryController@export')->name('api.invoices.export');
});

// 客户相关路由（发票用）
// 获取客户列表
Route::get('/customers', 'InvoiceApplicationController@getCustomerList')->name('api.customers.list');
// 获取客户发票信息
Route::get('/customers/{customerId}/invoice-info', 'InvoiceApplicationController@getCustomerInvoiceInfo')->name('api.customers.invoice.info');
// 获取客户合同列表
Route::get('/customers/{customerId}/contracts', 'InvoiceApplicationController@getCustomerContracts')->name('api.customers.contracts');

// 合同相关路由（发票用）
// 获取合同发票信息
Route::get('/contracts/{contractId}/invoice-info', 'InvoiceApplicationController@getContractInvoiceInfo')->name('api.contracts.invoice.info');

// 文件上传路由
// 上传发票文件
Route::post('/upload/invoice', function(\Illuminate\Http\Request $request) {
    try {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('invoices', $filename, 'public');

            return response()->json([
                'code' => 0,
                'msg' => '上传成功',
                'data' => [
                    'url' => '/storage/' . $path,
                    'path' => $path,
                    'name' => $filename,
                ]
            ]);
        }

        return response()->json([
            'code' => 1,
            'msg' => '没有文件上传',
            'data' => null
        ], 400);
    } catch (\Exception $e) {
        return response()->json([
            'code' => 1,
            'msg' => '上传失败: ' . $e->getMessage(),
            'data' => null
        ], 500);
    }
})->name('api.upload.invoice');

    // 支出单管理（临时移到公开路由用于测试）
    // 注意：具体路径的路由必须放在参数化路由之前，避免路由冲突
    // 批量删除支出单
    Route::post('/expenses/batch-destroy', 'Api\ExpenseController@batchDestroy')->name('api.expenses.batch.destroy');
    // 获取支出单列表
    Route::get('/expenses', 'Api\ExpenseController@index')->name('api.expenses.index');
    // 创建支出单
    Route::post('/expenses', 'Api\ExpenseController@store')->name('api.expenses.store');
    // 提交支出单
    Route::post('/expenses/{id}/submit', 'Api\ExpenseController@submit')->name('api.expenses.submit');
    // 审批支出单
    Route::post('/expenses/{id}/approve', 'Api\ExpenseController@approve')->name('api.expenses.approve');
    // 拒绝支出单
    Route::post('/expenses/{id}/reject', 'Api\ExpenseController@reject')->name('api.expenses.reject');
    // 获取支出单详情
    Route::get('/expenses/{id}', 'Api\ExpenseController@show')->name('api.expenses.show');
    // 更新支出单
    Route::put('/expenses/{id}', 'Api\ExpenseController@update')->name('api.expenses.update');
    // 删除支出单
    Route::delete('/expenses/{id}', 'Api\ExpenseController@destroy')->name('api.expenses.destroy');
