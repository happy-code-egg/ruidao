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
    Route::post('/login', 'AuthController@login')->name('login');

        // 基础数据接口（无需特殊权限）
        Route::get('/departments/simple', 'UserController@getDepartments')->name('api.departments.simple');
        Route::get('/roles/all', 'RoleController@getAllRoles')->name('api.roles.all');

    // 代理机构管理（临时移到公开路由用于测试）
    Route::get('/agencies', 'AgencyController@index')->name('api.agencies.index');//  获取代理机构列表
    Route::get('/agencies/countries', 'AgencyController@getCountries')->name('api.agencies.countries');//获取国家选项列表
    Route::post('/agencies', 'AgencyController@store')->name('api.agencies.store');//创建代理机构
    Route::get('/agencies/{id}', 'AgencyController@show')->name('api.agencies.show');// 获取代理机构详情
    Route::put('/agencies/{id}', 'AgencyController@update')->name('api.agencies.update');//更新代理机构
    Route::delete('/agencies/{id}', 'AgencyController@destroy')->name('api.agencies.destroy');// 删除代理机构

    // 工作流管理（临时移到公开路由用于测试）
    Route::get('/workflows', 'WorkflowController@index')->name('api.workflows.index');
    Route::get('/workflows/case-types', 'WorkflowController@getCaseTypes')->name('api.workflows.case.types');
    Route::get('/workflows/assignable-users', 'WorkflowController@getAssignableUsers')->name('api.workflows.assignable.users');
    Route::post('/workflows', 'WorkflowController@store')->name('api.workflows.store');
    Route::get('/workflows/{id}', 'WorkflowController@show')->name('api.workflows.show');
    Route::put('/workflows/{id}', 'WorkflowController@update')->name('api.workflows.update');
    Route::delete('/workflows/{id}', 'WorkflowController@destroy')->name('api.workflows.destroy');
    Route::put('/workflows/{id}/toggle-status', 'WorkflowController@toggleStatus')->name('api.workflows.toggle.status');
    Route::get('/workflows/{id}/nodes', 'WorkflowController@getNodes')->name('api.workflows.nodes');
    Route::put('/workflows/{id}/nodes', 'WorkflowController@updateNodes')->name('api.workflows.update.nodes');

    // 流程配置专用API（避免与其他workflow接口冲突）
    // 注意：具体路径的路由必须放在参数化路由之前，避免路由冲突
    Route::get('/workflow-config/list', 'WorkflowConfigController@getList')->name('api.workflow.config.list');
    Route::get('/workflow-config/assignable-users', 'WorkflowConfigController@getAssignableUsers')->name('api.workflow.config.assignable.users');
    Route::get('/workflow-config/case-types', 'WorkflowConfigController@getCaseTypes')->name('api.workflow.config.case.types');
    Route::post('/workflow-config/batch-update', 'WorkflowConfigController@batchUpdate')->name('api.workflow.config.batch.update');
    Route::get('/workflow-config/{id}', 'WorkflowConfigController@getDetail')->name('api.workflow.config.show');
    Route::put('/workflow-config/{id}', 'WorkflowConfigController@update')->name('api.workflow.config.update');
    Route::post('/workflow-config/{id}/reset', 'WorkflowConfigController@resetToDefault')->name('api.workflow.config.reset');
    Route::post('/workflow-config/{id}/validate', 'WorkflowConfigController@validateWorkflow')->name('api.workflow.config.validate');

    // 审核进度相关路由
    Route::get('/review-progress/contract-flows', 'ReviewProgressController@getContractFlows')->name('api.review.progress.contract.flows');
    Route::get('/review-progress/contract-flows/{id}', 'ReviewProgressController@getContractFlowDetail')->name('api.review.progress.contract.flows.detail');
    Route::get('/review-progress/assign-flows', 'ReviewProgressController@getAssignFlows')->name('api.review.progress.assign.flows');
    Route::get('/review-progress/review-flows', 'ReviewProgressController@getReviewFlows')->name('api.review.progress.review.flows');

    // 工作流实例管理（公开路由 - 仅查询接口）
    // 注意：具体路径的路由必须放在参数化路由之前，避免路由冲突
    Route::get('/workflow-instances/business-status', 'WorkflowInstanceController@businessStatus')->name('api.workflow.instances.business.status');
    Route::get('/workflow-instances/assignable-users', 'WorkflowInstanceController@getAssignableUsers')->name('api.workflow.instances.assignable.users');
    Route::get('/workflow-instances/{id}', 'WorkflowInstanceController@show')->name('api.workflow.instances.show');
    Route::get('/workflow-instances/{instanceId}/backable-nodes', 'WorkflowInstanceController@getBackableNodes')->name('api.workflow.instances.backable.nodes');

    // 首页Dashboard（临时移到公开路由用于测试）
    Route::get('/dashboard/statistics', 'DashboardController@statistics')->name('api.dashboard.statistics');
    Route::get('/dashboard/my-tasks', 'DashboardController@myTasks')->name('api.dashboard.my.tasks');
    Route::get('/dashboard/recent-activities', 'DashboardController@recentActivities')->name('api.dashboard.recent.activities');
    Route::get('/dashboard/notifications', 'DashboardController@notifications')->name('api.dashboard.notifications');
    Route::get('/dashboard/quick-actions', 'DashboardController@quickActions')->name('api.dashboard.quick.actions');

    // 合同管理（已移至认证路由组）

    // 测试路由
    Route::get('/test-contracts', function() {
        return response()->json(['message' => 'Test route works', 'count' => \App\Models\Contract::count()]);
    });

    // 审核进度路由（公开访问，用于审核进度页面）
    Route::get('/review-progress/register-flows', 'ReviewProgressController@getRegisterFlows');
    Route::get('/review-progress/register-flows/{id}', 'ReviewProgressController@getRegisterFlowDetail');
    Route::get('/review-progress/contract-flows', 'ReviewProgressController@getContractFlows');
    Route::get('/review-progress/contract-flows/{id}', 'ReviewProgressController@getContractFlowDetail');

    // CORS预检请求支持
    Route::options('/review-progress/register-flows', function () {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', request()->header('Origin'))
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    });
    Route::options('/review-progress/register-flows/{id}', function () {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', request()->header('Origin'))
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    });
    Route::options('/review-progress/contract-flows', function () {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', request()->header('Origin'))
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    });
    Route::options('/review-progress/contract-flows/{id}', function () {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', request()->header('Origin'))
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    });

    // 测试合同数据查询
    Route::get('/test-contracts-data', function() {
        $contracts = \App\Models\Contract::with(['customer'])->paginate(10);
        return response()->json([
            'data' => $contracts->items(),
            'total' => $contracts->total(),
            'current_page' => $contracts->currentPage(),
            'per_page' => $contracts->perPage()
        ]);
    });

    // 客户等级和相关类型设置（临时移到公开路由用于测试）
    Route::get('/test-customer-levels', 'CustomerLevelsController@index')->name('api.test.customer.levels.index');
    Route::get('/test-customer-levels/options', 'CustomerLevelsController@options')->name('api.test.customer.levels.options');
    Route::get('/test-related-types', 'RelatedTypesController@index')->name('api.test.related.types.index');
    Route::get('/test-related-types/case-type-options', 'RelatedTypesController@getCaseTypeOptions')->name('api.test.related.types.case.type.options');

    // 通知规则管理（临时移到公开路由用于测试）
    Route::get('/notification-rules', 'NotificationRuleController@index')->name('api.notification.rules.index');
    Route::get('/notification-rules/file-types', 'NotificationRuleController@getFileTypeTree')->name('api.notification.rules.file.types');
    Route::get('/notification-rules/file-category/{id}/rules', 'NotificationRuleController@getRulesByFileCategory')->name('api.notification.rules.file.category.rules');
    Route::get('/notification-rules/rule-types', 'NotificationRuleController@getRuleTypes')->name('api.notification.rules.rule.types');
    Route::get('/notification-rules/process-items', 'NotificationRuleController@getProcessItems')->name('api.notification.rules.process.items');
    Route::get('/notification-rules/process-statuses', 'NotificationRuleController@getProcessStatuses')->name('api.notification.rules.process.statuses');
    Route::get('/notification-rules/users', 'NotificationRuleController@getUsers')->name('api.notification.rules.users');
    Route::get('/notification-rules/countries', 'NotificationRuleController@getCountries')->name('api.notification.rules.countries');
    Route::get('/notification-rules/case-types', 'NotificationRuleController@getCaseTypes')->name('api.notification.rules.case.types');
    Route::post('/notification-rules', 'NotificationRuleController@store')->name('api.notification.rules.store');
    Route::get('/notification-rules/{id}', 'NotificationRuleController@show')->name('api.notification.rules.show');
    Route::put('/notification-rules/{id}', 'NotificationRuleController@update')->name('api.notification.rules.update');
    Route::delete('/notification-rules/{id}', 'NotificationRuleController@destroy')->name('api.notification.rules.destroy');
    Route::put('/notification-rules/{id}/toggle-status', 'NotificationRuleController@toggleStatus')->name('api.notification.rules.toggle.status');
    Route::post('/notification-rules/{id}/test', 'NotificationRuleController@testRule')->name('api.notification.rules.test');
    Route::post('/notification-rules/batch', 'NotificationRuleController@batchOperation')->name('api.notification.rules.batch');

    // 流程规则管理（临时移到公开路由用于测试）
    Route::get('/process-rules', 'ProcessRuleController@index')->name('api.process.rules.index');
    Route::post('/process-rules', 'ProcessRuleController@store')->name('api.process.rules.store');
    Route::get('/process-rules/process-item-tree', 'ProcessRuleController@getProcessItemTree')->name('api.process.rules.process.item.tree');
    Route::get('/process-rules/process-items', 'ProcessRuleController@getProcessItemTree')->name('api.process.rules.process.items');
    Route::get('/process-rules/process-item-detail/{id}', 'ProcessRuleController@getProcessItemDetail')->name('api.process.rules.process.item.detail');
    Route::get('/process-rules/process-item-rules', 'ProcessRuleController@getProcessItemRules')->name('api.process.rules.process.item.rules');
    Route::get('/process-rules/rule-types', 'ProcessRuleController@getRuleTypes')->name('api.process.rules.rule.types');
    Route::get('/process-rules/case-types', 'ProcessRuleController@getCaseTypes')->name('api.process.rules.case.types');
    Route::get('/process-rules/business-types', 'ProcessRuleController@getBusinessTypes')->name('api.process.rules.business.types');
    Route::get('/process-rules/application-types', 'ProcessRuleController@getApplicationTypes')->name('api.process.rules.application.types');
    Route::get('/process-rules/countries', 'ProcessRuleController@getCountries')->name('api.process.rules.countries');
    Route::get('/process-rules/process-item-types', 'ProcessRuleController@getProcessItemTypes')->name('api.process.rules.process.item.types');
    Route::get('/process-rules/process-statuses', 'ProcessRuleController@getProcessStatuses')->name('api.process.rules.process.statuses');
    Route::get('/process-rules/users', 'ProcessRuleController@getUsers')->name('api.process.rules.users');
    Route::get('/process-rules/{id}', 'ProcessRuleController@show')->name('api.process.rules.show');
    Route::put('/process-rules/{id}', 'ProcessRuleController@update')->name('api.process.rules.update');
    Route::delete('/process-rules/{id}', 'ProcessRuleController@destroy')->name('api.process.rules.destroy');
    Route::put('/process-rules/{id}/toggle-status', 'ProcessRuleController@toggleStatus')->name('api.process.rules.toggle.status');

    // 我方公司设置（临时测试路由）
    Route::get('/test-our-companies', 'OurCompaniesController@index')->name('api.test.our.companies.index');

    // 客户管理临时测试路由（移到公开路由用于测试）
    Route::get('/customer-contacts', 'CustomerContactController@index')->name('api.test.customer.contacts.index');
    Route::post('/customer-contacts', 'CustomerContactController@store')->name('api.test.customer.contacts.store');
    Route::get('/customer-contacts/{id}', 'CustomerContactController@show')->name('api.test.customer.contacts.show');
    Route::put('/customer-contacts/{id}', 'CustomerContactController@update')->name('api.test.customer.contacts.update');
    Route::delete('/customer-contacts/{id}', 'CustomerContactController@destroy')->name('api.test.customer.contacts.destroy');
    Route::get('/customer-contacts-options', 'CustomerContactController@getCustomerOptions')->name('api.test.customer.contacts.options');

    Route::get('/customer-applicants', 'CustomerApplicantController@index')->name('api.test.customer.applicants.index');
    Route::post('/customer-applicants', 'CustomerApplicantController@store')->name('api.test.customer.applicants.store');
    Route::get('/customer-applicants/{id}', 'CustomerApplicantController@show')->name('api.test.customer.applicants.show');
    Route::put('/customer-applicants/{id}', 'CustomerApplicantController@update')->name('api.test.customer.applicants.update');
    Route::delete('/customer-applicants/{id}', 'CustomerApplicantController@destroy')->name('api.test.customer.applicants.destroy');

    Route::get('/customer-inventors', 'CustomerInventorController@index')->name('api.test.customer.inventors.index');
    Route::post('/customer-inventors', 'CustomerInventorController@store')->name('api.test.customer.inventors.store');
    Route::get('/customer-inventors/{id}', 'CustomerInventorController@show')->name('api.test.customer.inventors.show');
    Route::put('/customer-inventors/{id}', 'CustomerInventorController@update')->name('api.test.customer.inventors.update');
    Route::delete('/customer-inventors/{id}', 'CustomerInventorController@destroy')->name('api.test.customer.inventors.destroy');

    // 三个数据配置页面测试路由（临时）
    Route::get('/test-commission-settings', 'CommissionSettingsController@index')->name('api.test.commission.settings.index');
    Route::post('/test-commission-settings', 'CommissionSettingsController@store')->name('api.test.commission.settings.store');
    Route::get('/test-commission-settings/{id}', 'CommissionSettingsController@show')->name('api.test.commission.settings.show');
    Route::put('/test-commission-settings/{id}', 'CommissionSettingsController@update')->name('api.test.commission.settings.update');

    Route::get('/test-manuscript-scoring-items', 'ManuscriptScoringItemsController@index')->name('api.test.manuscript.scoring.items.index');
    Route::post('/test-manuscript-scoring-items', 'ManuscriptScoringItemsController@store')->name('api.test.manuscript.scoring.items.store');
    Route::get('/test-manuscript-scoring-items/{id}', 'ManuscriptScoringItemsController@show')->name('api.test.manuscript.scoring.items.show');
    Route::put('/test-manuscript-scoring-items/{id}', 'ManuscriptScoringItemsController@update')->name('api.test.manuscript.scoring.items.update');

    Route::get('/test-protection-centers', 'ProtectionCentersController@index')->name('api.test.protection.centers.index');
    Route::post('/test-protection-centers', 'ProtectionCentersController@store')->name('api.test.protection.centers.store');
    Route::get('/test-protection-centers/{id}', 'ProtectionCentersController@show')->name('api.test.protection.centers.show');
    Route::put('/test-protection-centers/{id}', 'ProtectionCentersController@update')->name('api.test.protection.centers.update');

    // 价格指数设置（移到公开路由，前端可以直接访问）
    Route::get('/data-config/price-indices', 'PriceIndicesController@index')->name('api.price.indices.index');
    Route::get('/data-config/price-indices/options', 'PriceIndicesController@options')->name('api.price.indices.options');
    Route::post('/data-config/price-indices', 'PriceIndicesController@store')->name('api.price.indices.store');
    Route::get('/data-config/price-indices/{id}', 'PriceIndicesController@show')->name('api.price.indices.show');
    Route::put('/data-config/price-indices/{id}', 'PriceIndicesController@update')->name('api.price.indices.update');
    Route::delete('/data-config/price-indices/{id}', 'PriceIndicesController@destroy')->name('api.price.indices.destroy');
    Route::post('/data-config/price-indices/batch-status', 'PriceIndicesController@batchUpdateStatus')->name('api.price.indices.batch.status');

    // 创新指数设置（移到公开路由，前端可以直接访问）
    Route::get('/data-config/innovation-indices', 'InnovationIndicesController@index')->name('api.innovation.indices.index');
    Route::get('/data-config/innovation-indices/options', 'InnovationIndicesController@options')->name('api.innovation.indices.options');
    Route::post('/data-config/innovation-indices', 'InnovationIndicesController@store')->name('api.innovation.indices.store');
    Route::get('/data-config/innovation-indices/{id}', 'InnovationIndicesController@show')->name('api.innovation.indices.show');
    Route::put('/data-config/innovation-indices/{id}', 'InnovationIndicesController@update')->name('api.innovation.indices.update');
    Route::delete('/data-config/innovation-indices/{id}', 'InnovationIndicesController@destroy')->name('api.innovation.indices.destroy');
    Route::post('/data-config/innovation-indices/batch-status', 'InnovationIndicesController@batchUpdateStatus')->name('api.innovation.indices.batch.status');

    // 产品设置（移到公开路由，前端可以直接访问）
    Route::get('/data-config/products', 'ProductController@index')->name('api.products.index');
    Route::get('/data-config/products/options', 'ProductController@options')->name('api.products.options');
    Route::post('/data-config/products', 'ProductController@store')->name('api.products.store');
    Route::get('/data-config/products/{id}', 'ProductController@show')->name('api.products.show');
    Route::put('/data-config/products/{id}', 'ProductController@update')->name('api.products.update');
    Route::delete('/data-config/products/{id}', 'ProductController@destroy')->name('api.products.destroy');
    Route::post('/data-config/products/batch-status', 'ProductController@batchUpdateStatus')->name('api.products.batch.status');

    // 业务服务类型设置（移到公开路由，前端可以直接访问）
    Route::get('/data-config/business-service-types', 'BusinessServiceTypesController@index')->name('api.business.service.types.index');//
    Route::get('/data-config/business-service-types/options', 'BusinessServiceTypesController@options')->name('api.business.service.types.options');
    Route::post('/data-config/business-service-types', 'BusinessServiceTypesController@store')->name('api.business.service.types.store');
    Route::get('/data-config/business-service-types/{id}', 'BusinessServiceTypesController@show')->name('api.business.service.types.show');
    Route::put('/data-config/business-service-types/{id}', 'BusinessServiceTypesController@update')->name('api.business.service.types.update');
    Route::delete('/data-config/business-service-types/{id}', 'BusinessServiceTypesController@destroy')->name('api.business.service.types.destroy');
    Route::post('/data-config/business-service-types/batch-status', 'BusinessServiceTypesController@batchUpdateStatus')->name('api.business.service.types.batch.status');

    // 处理事项类型设置（移到公开路由，前端可以直接访问）
    Route::get('/data-config/process-types', 'ProcessTypesController@index')->name('api.process.types.index');
    Route::get('/data-config/process-types/options', 'ProcessTypesController@options')->name('api.process.types.options');
    Route::post('/data-config/process-types', 'ProcessTypesController@store')->name('api.process.types.store');
    Route::get('/data-config/process-types/{id}', 'ProcessTypesController@show')->name('api.process.types.show');
    Route::put('/data-config/process-types/{id}', 'ProcessTypesController@update')->name('api.process.types.update');
    Route::delete('/data-config/process-types/{id}', 'ProcessTypesController@destroy')->name('api.process.types.destroy');

    // 客户管理（临时移到公开路由用于测试）
    // 注意：具体的路由必须放在参数路由之前
    Route::get('/test-customers/user-options', 'CustomerController@getUserOptions')->name('api.test.customers.user.options');
    Route::get('/test-customers/export', 'CustomerController@export')->name('api.test.customers.export');
    Route::get('/test-customers/download-template', 'CustomerController@downloadTemplate')->name('api.test.customers.download.template');
    Route::post('/test-customers/import', 'CustomerController@import')->name('api.test.customers.import');
    Route::post('/test-customers/batch-transfer-business', 'CustomerController@batchTransferBusiness')->name('api.test.customers.batch.transfer.business');
    Route::post('/test-customers/batch-add-business', 'CustomerController@batchAddBusiness')->name('api.test.customers.batch.add.business');
    Route::post('/test-customers/transfer', 'CustomerController@transfer')->name('api.test.customers.transfer');
    Route::post('/test-customers/move-to-public', 'CustomerController@moveToPublic')->name('api.test.customers.move.to.public');
    Route::post('/test-customers/batch-destroy', 'CustomerController@batchDestroy')->name('api.test.customers.batch.destroy');

    // 基础CRUD路由放在最后
    Route::get('/test-customers', 'CustomerController@index')->name('api.test.customers.index');
    Route::post('/test-customers', 'CustomerController@store')->name('api.test.customers.store');
    Route::get('/test-customers/{id}', 'CustomerController@show')->name('api.test.customers.show');
    Route::put('/test-customers/{id}', 'CustomerController@update')->name('api.test.customers.update');
    Route::delete('/test-customers/{id}', 'CustomerController@destroy')->name('api.test.customers.destroy');

    // 前端页面调用的路由（临时移到公开区域）
    // 具体路由必须在参数路由之前
    Route::get('/customers/user-options', 'CustomerController@getUserOptions')->name('api.customers.user.options');
    Route::get('/customers/organization-tree', 'CustomerController@getOrganizationTree')->name('api.customers.organization.tree');
    Route::get('/customers/config-data', 'CustomerController@getConfigData')->name('api.customers.config.data');
    Route::post('/customers/check-customer-code-unique', 'CustomerController@checkCustomerCodeUnique')->name('api.customers.check.customer.code.unique');
    Route::get('/customers/export', 'CustomerController@export')->name('api.customers.export');
    Route::get('/customers/download-template', 'CustomerController@downloadTemplate')->name('api.customers.download.template');
    Route::post('/customers/import', 'CustomerController@import')->name('api.customers.import');
    Route::post('/customers/batch-transfer-business', 'CustomerController@batchTransferBusiness')->name('api.customers.batch.transfer.business');
    Route::post('/customers/batch-add-business', 'CustomerController@batchAddBusiness')->name('api.customers.batch.add.business');
    Route::post('/customers/transfer', 'CustomerController@transfer')->name('api.customers.transfer');
    Route::post('/customers/move-to-public', 'CustomerController@moveToPublic')->name('api.customers.move.to.public');
    Route::post('/customers/batch-destroy', 'CustomerController@batchDestroy')->name('api.customers.batch.destroy');

    // 客户排序功能
    Route::post('/customers/sort/save', 'CustomerController@saveCustomerSort')->name('api.customers.sort.save');
    Route::get('/customers/sort', 'CustomerController@getCustomerSort')->name('api.customers.sort.get');
    Route::post('/customers/sort/reset', 'CustomerController@resetCustomerSort')->name('api.customers.sort.reset');
    Route::post('/customers/sort/set', 'CustomerController@setCustomerSort')->name('api.customers.sort.set');

    // 客户业务员信息
    Route::get('/customers/business-persons', 'CustomerController@getCustomerBusinessPersons')->name('api.customers.business.persons');

    // 客户数据导出
    Route::post('/customers/export', 'CustomerController@export')->name('api.customers.export');

    // 测试客户等级和规模数据
    Route::get('/customers/test-level-scale', 'CustomerController@testLevelAndScale')->name('api.customers.test.level.scale');

    // 修复客户等级和规模数据
    Route::post('/customers/fix-level-scale', 'CustomerController@fixLevelAndScaleData')->name('api.customers.fix.level.scale');

    // 从合同项目记录创建正式项目
    Route::post('/customers/contract-case-records/{id}/create-case', 'CustomerController@createCaseFromRecord')->name('api.customers.contract.case.records.create.case');

    // 撤销立项
    Route::post('/customers/contract-case-records/{id}/cancel-case', 'CustomerController@cancelCaseFromRecord')->name('api.customers.contract.case.records.cancel.case');

    // 文件管理查询（公开访问）
    Route::get('/search/files', 'FileSearchController@search')->name('api.search.files');
    Route::post('/search/files/export', 'FileSearchController@export')->name('api.search.files.export');
    Route::post('/search/files/batch-download', 'FileSearchController@batchDownload')->name('api.search.files.batch.download');
    Route::get('/search/files/options', 'FileSearchController@getOptions')->name('api.search.files.options');
    Route::get('/search/files/download/{id}', 'FileSearchController@downloadFile')->name('api.search.files.download');

    // 基础路由放在最后
    Route::get('/customers', 'CustomerController@index')->name('api.customers.index');
    Route::post('/customers', 'CustomerController@store')->name('api.customers.store');
    Route::get('/customers/{id}', 'CustomerController@show')->name('api.customers.show');
    Route::put('/customers/{id}', 'CustomerController@update')->name('api.customers.update');
    Route::delete('/customers/{id}', 'CustomerController@destroy')->name('api.customers.destroy');

    // 客户Tab页面相关API（移到公开区域用于前端测试）
    // 客户联系人管理
    Route::get('/customer-contacts', 'CustomerContactController@index')->name('api.customer.contacts.index');
    Route::post('/customer-contacts', 'CustomerContactController@store')->name('api.customer.contacts.store');
    Route::get('/customer-contacts/{id}', 'CustomerContactController@show')->name('api.customer.contacts.show');
    Route::put('/customer-contacts/{id}', 'CustomerContactController@update')->name('api.customer.contacts.update');
    Route::delete('/customer-contacts/{id}', 'CustomerContactController@destroy')->name('api.customer.contacts.destroy');

    // 客户申请人管理
    Route::get('/customer-applicants', 'CustomerApplicantController@index')->name('api.customer.applicants.index');
    Route::post('/customer-applicants', 'CustomerApplicantController@store')->name('api.customer.applicants.store');
    Route::get('/customer-applicants/{id}', 'CustomerApplicantController@show')->name('api.customer.applicants.show');
    Route::put('/customer-applicants/{id}', 'CustomerApplicantController@update')->name('api.customer.applicants.update');
    Route::delete('/customer-applicants/{id}', 'CustomerApplicantController@destroy')->name('api.customer.applicants.destroy');

    // 客户发明人管理
    Route::get('/customer-inventors', 'CustomerInventorController@index')->name('api.customer.inventors.index');
    Route::post('/customer-inventors', 'CustomerInventorController@store')->name('api.customer.inventors.store');
    Route::get('/customer-inventors/{id}', 'CustomerInventorController@show')->name('api.customer.inventors.show');
    Route::put('/customer-inventors/{id}', 'CustomerInventorController@update')->name('api.customer.inventors.update');
    Route::delete('/customer-inventors/{id}', 'CustomerInventorController@destroy')->name('api.customer.inventors.destroy');

    // 客户合同管理
    Route::get('/customer-contracts', 'CustomerContractController@index')->name('api.customer.contracts.index');
    Route::post('/customer-contracts', 'CustomerContractController@store')->name('api.customer.contracts.store');
    Route::get('/customer-contracts/{id}', 'CustomerContractController@show')->name('api.customer.contracts.show');
    Route::put('/customer-contracts/{id}', 'CustomerContractController@update')->name('api.customer.contracts.update');
    Route::delete('/customer-contracts/{id}', 'CustomerContractController@destroy')->name('api.customer.contracts.destroy');

    // 客户相关人员管理
    Route::get('/customer-related-persons', 'CustomerRelatedPersonController@index')->name('api.customer.related.persons.index');
    Route::post('/customer-related-persons', 'CustomerRelatedPersonController@store')->name('api.customer.related.persons.store');
    // 具体路由必须放在 {id} 路由之前
    Route::get('/customer-related-persons/person-types', 'CustomerRelatedPersonController@getPersonTypes')->name('api.customer.related.persons.person.types');
    Route::get('/customer-related-persons/business-persons', 'CustomerRelatedPersonController@getCustomerBusinessPersons')->name('api.customer.related.persons.business.persons');
    Route::get('/customer-related-persons/search-users', 'CustomerRelatedPersonController@searchUsers')->name('api.customer.related.persons.search.users');
    Route::post('/customer-related-persons/add-business-person', 'CustomerRelatedPersonController@addCustomerBusinessPerson')->name('api.customer.related.persons.add.business.person');
    Route::post('/customer-related-persons/remove-business-person', 'CustomerRelatedPersonController@removeCustomerBusinessPerson')->name('api.customer.related.persons.remove.business.person');
    // 通用 {id} 路由放在最后
    Route::get('/customer-related-persons/{id}', 'CustomerRelatedPersonController@show')->name('api.customer.related.persons.show');
    Route::put('/customer-related-persons/{id}', 'CustomerRelatedPersonController@update')->name('api.customer.related.persons.update');
    Route::delete('/customer-related-persons/{id}', 'CustomerRelatedPersonController@destroy')->name('api.customer.related.persons.destroy');

    // 案例监控管理（临时移到公开路由用于测试）
    Route::get('/case-monitor/item-monitor', 'CaseMonitorController@itemMonitor')->name('api.case.monitor.item');//事项监控（查询案例处理事项列表）
    Route::post('/case-monitor/item-monitor/export', 'CaseMonitorController@exportItemMonitor')->name('api.case.monitor.item.export');// 导出事项监控数据
    Route::get('/case-monitor/fee-monitor', 'CaseMonitorController@feeMonitor')->name('api.case.monitor.fee');//官费监控列表查询
    Route::get('/case-monitor/fee-stats', 'CaseMonitorController@feeStats')->name('api.case.monitor.fee.stats');// 获取费用统计数据
    Route::post('/case-monitor/fee-monitor/export', 'CaseMonitorController@exportFeeMonitor')->name('api.case.monitor.fee.export');//导出官费监控数据
    Route::get('/case-monitor/abnormal-fee', 'CaseMonitorController@abnormalFee')->name('api.case.monitor.abnormal.fee');//官费监控（费用管理查询）
    Route::post('/case-monitor/abnormal-fee/export', 'CaseMonitorController@exportAbnormalFee')->name('api.case.monitor.abnormal.fee.export');//导出异常官费数据
    Route::post('/case-monitor/abnormal-fee/mark-processed', 'CaseMonitorController@markAbnormalFeeProcessed')->name('api.case.monitor.abnormal.fee.mark.processed');//  标记异常费用已处理

    // 请款管理（临时移到公开路由用于测试）
    Route::get('/payment-requests', 'PaymentRequestController@index')->name('api.payment.requests.index');
    Route::get('/payment-requests/statistics', 'PaymentRequestController@statistics')->name('api.payment.requests.statistics');
    Route::get('/payment-requests/{id}', 'PaymentRequestController@show')->name('api.payment.requests.show');
    Route::post('/payment-requests', 'PaymentRequestController@store')->name('api.payment.requests.store');
    Route::put('/payment-requests/{id}', 'PaymentRequestController@update')->name('api.payment.requests.update');
    Route::delete('/payment-requests/{id}', 'PaymentRequestController@destroy')->name('api.payment.requests.destroy');
    Route::post('/payment-requests/{id}/submit', 'PaymentRequestController@submit')->name('api.payment.requests.submit');
    Route::post('/payment-requests/{id}/withdraw', 'PaymentRequestController@withdraw')->name('api.payment.requests.withdraw');
    Route::post('/payment-requests/{id}/approve', 'PaymentRequestController@approve')->name('api.payment.requests.approve');
    Route::post('/payment-requests/export', 'PaymentRequestController@export')->name('api.payment.requests.export');

    // 到款管理（临时移到公开路由用于测试）
    Route::get('/payment-receiveds', 'PaymentReceivedController@index')->name('api.payment.receiveds.index');
    Route::get('/payment-receiveds/statistics', 'PaymentReceivedController@statistics')->name('api.payment.receiveds.statistics');
    Route::get('/payment-receiveds/{id}', 'PaymentReceivedController@show')->name('api.payment.receiveds.show');
    Route::post('/payment-receiveds', 'PaymentReceivedController@store')->name('api.payment.receiveds.store');
    Route::put('/payment-receiveds/{id}', 'PaymentReceivedController@update')->name('api.payment.receiveds.update');
    Route::delete('/payment-receiveds/{id}', 'PaymentReceivedController@destroy')->name('api.payment.receiveds.destroy');
    Route::post('/payment-receiveds/{id}/submit', 'PaymentReceivedController@submit')->name('api.payment.receiveds.submit');
    Route::post('/payment-receiveds/{id}/claim', 'PaymentReceivedController@claim')->name('api.payment.receiveds.claim');
    Route::post('/payment-receiveds/export', 'PaymentReceivedController@export')->name('api.payment.receiveds.export');

    // 核销管理（临时移到公开路由用于测试）
    Route::get('/write-offs/pending', 'WriteOffController@getPendingList')->name('api.write-offs.pending');
    Route::get('/write-offs/pending/statistics', 'WriteOffController@getPendingStatistics')->name('api.write-offs.pending.statistics');
    Route::post('/write-offs/write-off', 'WriteOffController@writeOff')->name('api.write-offs.write-off');
    Route::post('/write-offs/batch-write-off', 'WriteOffController@batchWriteOff')->name('api.write-offs.batch-write-off');
    Route::get('/write-offs/completed', 'WriteOffController@getCompletedList')->name('api.write-offs.completed');
    Route::get('/write-offs/completed/statistics', 'WriteOffController@getCompletedStatistics')->name('api.write-offs.completed.statistics');
    Route::get('/write-offs/write-off-completed', 'WriteOffController@getWriteOffCompletedList')->name('api.write-offs.write-off-completed');
    Route::get('/write-offs/write-off-completed/statistics', 'WriteOffController@getWriteOffCompletedStatistics')->name('api.write-offs.write-off-completed.statistics');
    Route::post('/write-offs/{id}/revert', 'WriteOffController@revertWriteOff')->name('api.write-offs.revert');
    Route::post('/write-offs/batch-revert', 'WriteOffController@batchRevertWriteOff')->name('api.write-offs.batch-revert');
    Route::get('/write-offs/{id}', 'WriteOffController@show')->name('api.write-offs.show');
    Route::get('/write-offs/export/pending', 'WriteOffController@exportPending')->name('api.write-offs.export.pending');
    Route::get('/write-offs/export/completed', 'WriteOffController@exportCompleted')->name('api.write-offs.export.completed');

    // 用户和客户API（临时移到公开路由用于测试）
    Route::get('/users', 'UserController@index')->name('api.users.index.public');
    Route::get('/customers', 'CustomerController@index')->name('api.customers.index.public');

    // 分配管理（临时移到公开路由用于测试）
    Route::get('/assignment/new-applications', 'AssignmentController@newApplications')->name('api.assignment.new.applications');// 获取新申请待分配列表
    Route::get('/assignment/middle-cases', 'AssignmentController@middleCases')->name('api.assignment.middle.cases');//获取中间案待分配列表
    Route::get('/assignment/tech-service-cases', 'AssignmentController@techServiceCases')->name('api.assignment.tech.service.cases');//   获取科服待分配列表
    Route::get('/assignment/assigned-cases', 'AssignmentController@assignedCases')->name('api.assignment.assigned.cases');// 获取已分配列表
    Route::post('/assignment/batch-assign', 'AssignmentController@batchAssign')->name('api.assignment.batch.assign');//批量分配处理事项
    Route::post('/assignment/direct-assign', 'AssignmentController@directAssign')->name('api.assignment.direct.assign');// 直接分配（单个处理事项分配）
    Route::post('/assignment/withdraw-assignment', 'AssignmentController@withdrawAssignment')->name('api.assignment.withdraw');//   撤回分配
    Route::get('/assignment/assignable-users', 'AssignmentController@getAssignableUsers')->name('api.assignment.assignable.users');//获取可分配的用户列表
    Route::get('/assignment/process-detail/{id}', 'AssignmentController@getProcessDetail')->name('api.assignment.process.detail'); //获取处理事项详情

    // 提成管理（临时移到公开路由用于测试）
    Route::get('/commission/stats', 'CommissionController@getCommissionStats')->name('api.commission.stats');
    Route::get('/commission/user-summary', 'CommissionController@getUserCommissionSummary')->name('api.commission.user.summary');
    Route::get('/commission/config', 'CommissionController@getCommissionConfig')->name('api.commission.config');

    // 核稿管理（临时移到公开路由用于测试）- 使用修复版控制器
    Route::get('/review/draft-list', 'ReviewControllerFixed@getDraftList')->name('api.review.draft.list');
    Route::get('/review/to-be-start-list', 'ReviewControllerFixed@getToBeStartList')->name('api.review.to.be.start.list');
    Route::get('/review/in-review-list', 'ReviewControllerFixed@getInReviewList')->name('api.review.in.review.list');
    Route::get('/review/completed-list', 'ReviewControllerFixed@getCompletedList')->name('api.review.completed.list');
    Route::get('/review/detail/{id}', 'ReviewControllerFixed@getReviewDetail')->name('api.review.detail');
    Route::post('/review/transfer', 'ReviewControllerFixed@transferProcess')->name('api.review.transfer');
    Route::post('/review/return', 'ReviewControllerFixed@returnProcess')->name('api.review.return');

});

// 需要认证的路由
Route::group(['middleware' => ['auth:sanctum'], 'namespace' => 'Api'], function () {
    // 认证相关
    Route::post('/logout', 'AuthController@logout')->name('api.logout');//用户登出
    Route::get('/user/profile', 'AuthController@profile')->name('api.user.profile');//获取当前登录用户信息接口
    Route::put('/user/profile', 'AuthController@updateProfile')->name('api.user.profile.update');//更新个人信息接口
    Route::put('/user/change-password', 'AuthController@changePassword')->name('api.user.change.password');// 修改密码接口

    // 通用文件上传
    Route::post('/upload', 'FileUploadController@upload')->name('api.upload');

    // 客户文件管理
    Route::get('/customer-files', 'CustomerFileController@index')->name('api.customer.files.index');
    Route::post('/customer-files/upload', 'CustomerFileController@upload')->name('api.customer.files.upload');
    Route::get('/customer-files/{id}', 'CustomerFileController@show')->name('api.customer.files.show');
    Route::put('/customer-files/{id}', 'CustomerFileController@update')->name('api.customer.files.update');
    Route::delete('/customer-files/{id}', 'CustomerFileController@destroy')->name('api.customer.files.destroy');
    Route::get('/customer-files/{id}/download', 'CustomerFileController@download')->name('api.customer.files.download');
    Route::get('/customer-files/file-categories', 'CustomerFileController@getFileCategories')->name('api.customer.files.file.categories');

    // 用户管理（需要权限）
    Route::group(['middleware' => 'permission:system.user'], function () {
        Route::get('/users', 'UserController@index')->name('api.users.index');
        Route::get('/users/{id}', 'UserController@show')->name('api.users.show');
        Route::post('/users', 'UserController@store')->name('api.users.store');
        Route::put('/users/{id}', 'UserController@update')->name('api.users.update');
        Route::delete('/users/{id}', 'UserController@destroy')->name('api.users.destroy');
        Route::put('/users/{id}/reset-password', 'UserController@resetPassword')->name('api.users.reset.password');
        Route::get('/users/{id}/roles', 'UserController@getUserRoles')->name('api.users.roles');
        Route::post('/users/{id}/roles', 'UserController@assignRoles')->name('api.users.assign.roles');
        Route::post('/users/batch-delete', 'UserController@batchDelete')->name('api.users.batch.delete');
        Route::put('/users/{id}/toggle-status', 'UserController@toggleStatus')->name('api.users.toggle.status');
    });

    // 部门管理（需要权限）
    Route::group(['middleware' => 'permission:system.department'], function () {
        Route::get('/departments', 'DepartmentController@index')->name('api.departments.index');
        Route::get('/departments/managers', 'DepartmentController@getManagers')->name('api.departments.managers');
        Route::post('/departments', 'DepartmentController@store')->name('api.departments.store');
        Route::get('/departments/{id}', 'DepartmentController@show')->name('api.departments.show');
        Route::put('/departments/{id}', 'DepartmentController@update')->name('api.departments.update');
        Route::delete('/departments/{id}', 'DepartmentController@destroy')->name('api.departments.destroy');
    });

    // 角色管理（需要权限）
    Route::group(['middleware' => 'permission:system.role'], function () {
        Route::get('/roles', 'RoleController@index')->name('api.roles.index');
        Route::post('/roles', 'RoleController@store')->name('api.roles.store');
        Route::get('/roles/{id}', 'RoleController@show')->name('api.roles.show');
        Route::put('/roles/{id}', 'RoleController@update')->name('api.roles.update');
        Route::delete('/roles/{id}', 'RoleController@destroy')->name('api.roles.destroy');
        Route::get('/roles/{id}/permissions', 'RoleController@getPermissions')->name('api.roles.permissions');
        Route::post('/roles/{id}/permissions', 'RoleController@assignPermissions')->name('api.roles.assign.permissions');
    });

    // 权限管理（需要权限）
    Route::group(['middleware' => 'permission:system.permission'], function () {
        Route::get('/permissions', 'PermissionController@index')->name('api.permissions.index');
        Route::get('/permissions/types', 'PermissionController@getTypes')->name('api.permissions.types');
        Route::get('/permissions/parent-options', 'PermissionController@getParentOptions')->name('api.permissions.parent.options');
        Route::post('/permissions', 'PermissionController@store')->name('api.permissions.store');
        Route::get('/permissions/{id}', 'PermissionController@show')->name('api.permissions.show');
        Route::put('/permissions/{id}', 'PermissionController@update')->name('api.permissions.update');
        Route::delete('/permissions/{id}', 'PermissionController@destroy')->name('api.permissions.destroy');
    });

    // 系统配置模块API（管理员可访问）
    // 日志管理（注意路由顺序，具体路径要在参数路径之前）
    Route::get('/logs', 'LogsController@index')->name('api.logs.index');
    Route::get('/logs/types', 'LogsController@getTypes')->name('api.logs.types');
    Route::get('/logs/users', 'LogsController@getUsers')->name('api.logs.users');
    Route::get('/logs/export', 'LogsController@export')->name('api.logs.export');
    Route::delete('/logs/clear', 'LogsController@clear')->name('api.logs.clear');
    Route::post('/logs/batch-delete', 'LogsController@batchDelete')->name('api.logs.batch.delete');
    Route::get('/logs/{id}', 'LogsController@show')->name('api.logs.show');
    Route::delete('/logs/{id}', 'LogsController@destroy')->name('api.logs.destroy');

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
    Route::get('/contracts', 'ContractController@index')->name('api.contracts.index');
    Route::post('/contracts', 'ContractController@store')->name('api.contracts.store');
    Route::post('/contracts/export', 'ContractController@export')->name('api.contracts.export');

    // 合同工作流管理（特殊路由需要放在 {id} 路由之前）
    Route::get('/contracts/progress', 'ContractController@getContractProgress')->name('api.contracts.progress');
    Route::get('/contracts/pending-count', 'ContractController@getPendingCount')->name('api.contracts.pending.count');

    // 合同CRUD（带ID参数的路由）
    Route::get('/contracts/{id}', 'ContractController@show')->name('api.contracts.show');
    Route::put('/contracts/{id}', 'ContractController@update')->name('api.contracts.update');
    Route::delete('/contracts/{id}', 'ContractController@destroy')->name('api.contracts.destroy');

    // 合同附件管理
    Route::post('/contracts/{id}/attachments', 'ContractController@uploadAttachment')->name('api.contracts.attachments.upload');
    Route::delete('/contracts/{id}/attachments/{attachmentId}', 'ContractController@deleteAttachment')->name('api.contracts.attachments.delete');
    Route::get('/contracts/{id}/attachments/{attachmentId}/download', 'ContractController@downloadAttachment')->name('api.contracts.attachments.download');

    // 合同工作流管理（带ID参数的路由）
    Route::get('/contracts/{id}/workflow-status', 'ContractController@workflowStatus')->name('api.contracts.workflow.status');
    Route::post('/contracts/{id}/start-workflow', 'ContractController@startWorkflow')->name('api.contracts.start.workflow');
    Route::post('/contracts/{id}/restart-workflow', 'ContractController@restartWorkflow')->name('api.contracts.restart.workflow');
    Route::post('/contracts/{id}/start-workflow-with-assignees', 'ContractController@startWorkflowWithAssignees')->name('api.contracts.start.workflow.with.assignees');
    Route::get('/contracts/{id}/workflow-status', 'ContractController@getWorkflowStatus')->name('api.contracts.workflow.status');

    // 工作流实例管理（需要认证）
    Route::post('/workflow-instances/start', 'WorkflowInstanceController@start')->name('api.workflow.instances.start');
    Route::post('/workflow-instances/process/{processId}', 'WorkflowInstanceController@process')->name('api.workflow.instances.process');
    Route::get('/workflow-instances/my-tasks', 'WorkflowInstanceController@myTasks')->name('api.workflow.instances.my.tasks');
    Route::put('/workflow-instances/{instanceId}/cancel', 'WorkflowInstanceController@cancel')->name('api.workflow.instances.cancel');
    Route::get('/workflow-instances/{instanceId}/history', 'WorkflowInstanceController@history')->name('api.workflow.instances.history');
    // 注意：assignable-users 接口已移至公开路由组，避免重复定义

    // 项目管理（需要权限）
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('/cases', 'CaseController@index')->name('api.cases.index');
        Route::post('/cases', 'CaseController@store')->name('api.cases.store');
        Route::get('/cases/{id}', 'CaseController@show')->name('api.cases.show');
        Route::put('/cases/{id}', 'CaseController@update')->name('api.cases.update');
        Route::delete('/cases/{id}', 'CaseController@destroy')->name('api.cases.destroy');

        // 项目费用明细
        Route::get('/cases/{id}/fees', 'CaseFeeController@index')->name('api.cases.fees.index');//获取指定案件的费用列表
        Route::post('/cases/{id}/fees', 'CaseFeeController@store')->name('api.cases.fees.store');//为指定案件创建费用记录
        Route::put('/case-fees/{feeId}', 'CaseFeeController@update')->name('api.case.fees.update');//    更新指定的案件费用记录
        Route::delete('/case-fees/{feeId}', 'CaseFeeController@destroy')->name('api.case.fees.destroy');//删除指定的案件费用记录

        // 项目附件明细
        Route::get('/cases/{id}/attachments', 'CaseAttachmentController@index')->name('api.cases.attachments.index');//获取项目附件列表
        Route::post('/cases/{id}/attachments', 'CaseAttachmentController@store')->name('api.cases.attachments.store');// 创建项目附件记录
        Route::post('/cases/{id}/attachments/upload', 'CaseAttachmentController@upload')->name('api.cases.attachments.upload');//上传项目附件
        Route::delete('/case-attachments/{id}', 'CaseAttachmentController@destroy')->name('api.case.attachments.destroy');// 删除项目附件记录

        // 个人项目管理
        Route::get('/personal-cases', 'PersonalCaseController@index')->name('api.personal.cases.index');
        Route::get('/personal-cases/pending', 'PersonalCaseController@pending')->name('api.personal.cases.pending');
        Route::get('/personal-cases/pending-project', 'PersonalCaseController@pendingProject')->name('api.personal.cases.pending.project');
        Route::get('/personal-cases/completed', 'PersonalCaseController@completed')->name('api.personal.cases.completed');
        Route::get('/personal-cases/completed-project', 'PersonalCaseController@completedProject')->name('api.personal.cases.completed.project');
        Route::get('/personal-cases/department', 'PersonalCaseController@department')->name('api.personal.cases.department');

        // 个人项目操作
        Route::post('/personal-cases/modify-estimated-time', 'PersonalCaseController@modifyEstimatedTime')->name('api.personal.cases.modify.estimated.time');
        Route::post('/personal-cases/add-complete', 'PersonalCaseController@addComplete')->name('api.personal.cases.add.complete');
        Route::post('/personal-cases/add-process-note', 'PersonalCaseController@addProcessNote')->name('api.personal.cases.add.process.note');
        Route::post('/personal-cases/start-draft', 'PersonalCaseController@startDraft')->name('api.personal.cases.start.draft');
        Route::post('/personal-cases/start-supplement', 'PersonalCaseController@startSupplement')->name('api.personal.cases.start.supplement');
        Route::post('/personal-cases/add-process-follow', 'PersonalCaseController@addProcessFollow')->name('api.personal.cases.add.process.follow');
        Route::post('/personal-cases/add-received-time', 'PersonalCaseController@addReceivedTime')->name('api.personal.cases.add.received.time');
        Route::post('/personal-cases/add-deadline', 'PersonalCaseController@addDeadline')->name('api.personal.cases.add.deadline');
        Route::post('/personal-cases/add-remark', 'PersonalCaseController@addRemark')->name('api.personal.cases.add.remark');
        Route::post('/personal-cases/export', 'PersonalCaseController@export')->name('api.personal.cases.export');
        Route::post('/personal-cases/modify-process-item', 'PersonalCaseController@modifyProcessItem')->name('api.personal.cases.modify.process.item');

    });







    // 数据配置模块（暂时移除权限检查）
    // 客户等级设置
    Route::get('/config/customer-levels', 'CustomerLevelsController@index')->name('api.config.customer.levels.index');
    Route::post('/config/customer-levels', 'CustomerLevelsController@store')->name('api.config.customer.levels.store');
    Route::get('/config/customer-levels/{id}', 'CustomerLevelsController@show')->name('api.config.customer.levels.show');
    Route::put('/config/customer-levels/{id}', 'CustomerLevelsController@update')->name('api.config.customer.levels.update');
    Route::delete('/config/customer-levels/{id}', 'CustomerLevelsController@destroy')->name('api.config.customer.levels.destroy');
    Route::get('/config/customer-levels/options', 'CustomerLevelsController@options')->name('api.config.customer.levels.options');

    // 相关类型设置
    Route::get('/config/related-types', 'RelatedTypesController@index')->name('api.config.related.types.index');
    Route::post('/config/related-types', 'RelatedTypesController@store')->name('api.config.related.types.store');
    Route::get('/config/related-types/{id}', 'RelatedTypesController@show')->name('api.config.related.types.show');
    Route::put('/config/related-types/{id}', 'RelatedTypesController@update')->name('api.config.related.types.update');
    Route::delete('/config/related-types/{id}', 'RelatedTypesController@destroy')->name('api.config.related.types.destroy');
    Route::get('/config/related-types/options', 'RelatedTypesController@options')->name('api.config.related.types.options');
    Route::get('/config/related-types/case-type-options', 'RelatedTypesController@getCaseTypeOptions')->name('api.config.related.types.case.type.options');

    // 开票服务类型设置
    Route::get('/config/invoice-services', 'InvoiceServicesController@index')->name('api.config.invoice.services.index');
    Route::post('/config/invoice-services', 'InvoiceServicesController@store')->name('api.config.invoice.services.store');
    Route::get('/config/invoice-services/{id}', 'InvoiceServicesController@show')->name('api.config.invoice.services.show');
    Route::put('/config/invoice-services/{id}', 'InvoiceServicesController@update')->name('api.config.invoice.services.update');
    Route::delete('/config/invoice-services/{id}', 'InvoiceServicesController@destroy')->name('api.config.invoice.services.destroy');
    Route::get('/config/invoice-services/options', 'InvoiceServicesController@options')->name('api.config.invoice.services.options');

    // 园区名称设置
    Route::get('/config/parks', 'ParksConfigController@index')->name('api.config.parks.index');
    Route::post('/config/parks', 'ParksConfigController@store')->name('api.config.parks.store');
    Route::get('/config/parks/{id}', 'ParksConfigController@show')->name('api.config.parks.show');
    Route::put('/config/parks/{id}', 'ParksConfigController@update')->name('api.config.parks.update');
    Route::delete('/config/parks/{id}', 'ParksConfigController@destroy')->name('api.config.parks.destroy');
    Route::get('/config/parks/options', 'ParksConfigController@options')->name('api.config.parks.options');
    Route::post('/notification-rules/batch', 'NotificationRuleController@batchOperation')->name('api.notification.rules.batch');

    // 流程规则管理路由已移至上方，避免重复

    // 代理机构管理已移至公开路由

    // 代理师管理
    Route::get('/agents', 'AgentController@index')->name('api.agents.index');//获取代理师列表
    Route::get('/agents/agencies', 'AgentController@getAgencies')->name('api.agents.agencies');//获取用户的所有权限
    Route::post('/agents', 'AgentController@store')->name('api.agents.store');//创建代理师
    Route::get('/agents/{id}', 'AgentController@show')->name('api.agents.show');//获取代理师详情
    Route::put('/agents/{id}', 'AgentController@update')->name('api.agents.update');//更新代理师
    Route::delete('/agents/{id}', 'AgentController@destroy')->name('api.agents.destroy');//删除代理师

    // 数据配置管理
    // 申请类型设置
    Route::get('/data-config/apply-types', 'ApplyTypeController@index')->name('api.apply.types.index');// 获取申请类型列表（重写方法）
    Route::get('/data-config/apply-types/options', 'ApplyTypeController@options')->name('api.apply.types.options');//无
    Route::post('/data-config/apply-types', 'ApplyTypeController@store')->name('api.apply.types.store');//创建申请类型
    Route::get('/data-config/apply-types/{id}', 'ApplyTypeController@show')->name('api.apply.types.show');// 获取申请类型详情
    Route::put('/data-config/apply-types/{id}', 'ApplyTypeController@update')->name('api.apply.types.update');//更新申请类型
    Route::delete('/data-config/apply-types/{id}', 'ApplyTypeController@destroy')->name('api.apply.types.destroy');//无
    Route::post('/data-config/apply-types/batch-status', 'ApplyTypeController@batchUpdateStatus')->name('api.apply.types.batch.status');//无
    Route::get('/data-config/apply-types/all/{caseType}', 'ApplyTypeController@all')->name('api.apply.types.all');// 根据案件类型获取所有申请类型选项
    Route::get('/data-config/apply-types/all-by-country/{country}', 'ApplyTypeController@allByCountry')->name('api.apply.types.all.by.country');// 根据国家 / 地区获取所有申请类型选项

    // 处理事项状态设置
    Route::get('/data-config/process-statuses', 'ProcessStatusController@index')->name('api.process.statuses.index');
    Route::get('/data-config/process-statuses/options', 'ProcessStatusController@options')->name('api.process.statuses.options');
    Route::post('/data-config/process-statuses', 'ProcessStatusController@store')->name('api.process.statuses.store');
    Route::get('/data-config/process-statuses/{id}', 'ProcessStatusController@show')->name('api.process.statuses.show');
    Route::put('/data-config/process-statuses/{id}', 'ProcessStatusController@update')->name('api.process.statuses.update');
    Route::delete('/data-config/process-statuses/{id}', 'ProcessStatusController@destroy')->name('api.process.statuses.destroy');
    Route::post('/data-config/process-statuses/batch-status', 'ProcessStatusController@batchUpdateStatus')->name('api.process.statuses.batch.status');

    // 费用配置设置
    Route::get('/data-config/fee-configs', 'FeeConfigController@index')->name('api.fee.configs.index');
    Route::get('/data-config/fee-configs/options', 'FeeConfigController@options')->name('api.fee.configs.options');
    Route::post('/data-config/fee-configs', 'FeeConfigController@store')->name('api.fee.configs.store');
    Route::get('/data-config/fee-configs/{id}', 'FeeConfigController@show')->name('api.fee.configs.show');
    Route::put('/data-config/fee-configs/{id}', 'FeeConfigController@update')->name('api.fee.configs.update');
    Route::delete('/data-config/fee-configs/{id}', 'FeeConfigController@destroy')->name('api.fee.configs.destroy');
    Route::post('/data-config/fee-configs/batch-status', 'FeeConfigController@batchUpdateStatus')->name('api.fee.configs.batch.status');

    // 客户等级设置
    Route::get('/data-config/customer-levels', 'CustomerLevelController@index')->name('api.customer.levels.index');
    Route::get('/data-config/customer-levels/options', 'CustomerLevelController@options')->name('api.customer.levels.options');
    Route::post('/data-config/customer-levels', 'CustomerLevelController@store')->name('api.customer.levels.store');
    Route::get('/data-config/customer-levels/{id}', 'CustomerLevelController@show')->name('api.customer.levels.show');
    Route::put('/data-config/customer-levels/{id}', 'CustomerLevelController@update')->name('api.customer.levels.update');
    Route::delete('/data-config/customer-levels/{id}', 'CustomerLevelController@destroy')->name('api.customer.levels.destroy');
    Route::post('/data-config/customer-levels/batch-status', 'CustomerLevelController@batchUpdateStatus')->name('api.customer.levels.batch.status');

    // 跟进方式设置
    Route::get('/data-config/follow-up-methods', 'FollowUpMethodController@index')->name('api.follow.up.methods.index');
    Route::get('/data-config/follow-up-methods/options', 'FollowUpMethodController@options')->name('api.follow.up.methods.options');
    Route::post('/data-config/follow-up-methods', 'FollowUpMethodController@store')->name('api.follow.up.methods.store');
    Route::get('/data-config/follow-up-methods/{id}', 'FollowUpMethodController@show')->name('api.follow.up.methods.show');
    Route::put('/data-config/follow-up-methods/{id}', 'FollowUpMethodController@update')->name('api.follow.up.methods.update');
    Route::delete('/data-config/follow-up-methods/{id}', 'FollowUpMethodController@destroy')->name('api.follow.up.methods.destroy');
    Route::post('/data-config/follow-up-methods/batch-status', 'FollowUpMethodController@batchUpdateStatus')->name('api.follow.up.methods.batch.status');

    // 跟进类型设置
    Route::get('/data-config/follow-up-types', 'FollowUpTypeController@index')->name('api.follow.up.types.index');
    Route::get('/data-config/follow-up-types/options', 'FollowUpTypeController@options')->name('api.follow.up.types.options');
    Route::post('/data-config/follow-up-types', 'FollowUpTypeController@store')->name('api.follow.up.types.store');
    Route::get('/data-config/follow-up-types/{id}', 'FollowUpTypeController@show')->name('api.follow.up.types.show');
    Route::put('/data-config/follow-up-types/{id}', 'FollowUpTypeController@update')->name('api.follow.up.types.update');
    Route::delete('/data-config/follow-up-types/{id}', 'FollowUpTypeController@destroy')->name('api.follow.up.types.destroy');
    Route::post('/data-config/follow-up-types/batch-status', 'FollowUpTypeController@batchUpdateStatus')->name('api.follow.up.types.batch.status');

    // 商机状态设置
    Route::get('/data-config/business-statuses', 'BusinessStatusController@index')->name('api.business.statuses.index');// 获取商机状态列表
    Route::get('/data-config/business-statuses/options', 'BusinessStatusController@options')->name('api.business.statuses.options');//获取商机状态选项列表（用于下拉框等场景）
    Route::post('/data-config/business-statuses', 'BusinessStatusController@store')->name('api.business.statuses.store');//创建商机状态
    Route::get('/data-config/business-statuses/{id}', 'BusinessStatusController@show')->name('api.business.statuses.show');//获取商机状态详情
    Route::put('/data-config/business-statuses/{id}', 'BusinessStatusController@update')->name('api.business.statuses.update');//更新商机状态
    Route::delete('/data-config/business-statuses/{id}', 'BusinessStatusController@destroy')->name('api.business.statuses.destroy');//删除商机状态
    Route::post('/data-config/business-statuses/batch-status', 'BusinessStatusController@batchUpdateStatus')->name('api.business.statuses.batch.status');//无

    // 商机类型设置
    Route::get('/data-config/opportunity-types', 'OpportunityTypeController@index')->name('api.opportunity.types.index');
    Route::get('/data-config/opportunity-types/options', 'OpportunityTypeController@options')->name('api.opportunity.types.options');
    Route::post('/data-config/opportunity-types', 'OpportunityTypeController@store')->name('api.opportunity.types.store');
    Route::get('/data-config/opportunity-types/{id}', 'OpportunityTypeController@show')->name('api.opportunity.types.show');
    Route::put('/data-config/opportunity-types/{id}', 'OpportunityTypeController@update')->name('api.opportunity.types.update');
    Route::delete('/data-config/opportunity-types/{id}', 'OpportunityTypeController@destroy')->name('api.opportunity.types.destroy');
    Route::post('/data-config/opportunity-types/batch-status', 'OpportunityTypeController@batchUpdateStatus')->name('api.opportunity.types.batch.status');

    // 跟进进度设置
    Route::get('/data-config/follow-up-progresses', 'FollowUpProgressController@index')->name('api.follow.up.progresses.index');
    Route::get('/data-config/follow-up-progresses/options', 'FollowUpProgressController@options')->name('api.follow.up.progresses.options');
    Route::post('/data-config/follow-up-progresses', 'FollowUpProgressController@store')->name('api.follow.up.progresses.store');
    Route::get('/data-config/follow-up-progresses/{id}', 'FollowUpProgressController@show')->name('api.follow.up.progresses.show');
    Route::put('/data-config/follow-up-progresses/{id}', 'FollowUpProgressController@update')->name('api.follow.up.progresses.update');
    Route::delete('/data-config/follow-up-progresses/{id}', 'FollowUpProgressController@destroy')->name('api.follow.up.progresses.destroy');
    Route::post('/data-config/follow-up-progresses/batch-status', 'FollowUpProgressController@batchUpdateStatus')->name('api.follow.up.progresses.batch.status');

    // 客户规模设置
    Route::get('/data-config/customer-scales', 'CustomerScaleController@index')->name('api.customer.scales.index');
    Route::get('/data-config/customer-scales/options', 'CustomerScaleController@options')->name('api.customer.scales.options');
    Route::post('/data-config/customer-scales', 'CustomerScaleController@store')->name('api.customer.scales.store');
    Route::get('/data-config/customer-scales/{id}', 'CustomerScaleController@show')->name('api.customer.scales.show');
    Route::put('/data-config/customer-scales/{id}', 'CustomerScaleController@update')->name('api.customer.scales.update');
    Route::delete('/data-config/customer-scales/{id}', 'CustomerScaleController@destroy')->name('api.customer.scales.destroy');
    Route::post('/data-config/customer-scales/batch-status', 'CustomerScaleController@batchUpdateStatus')->name('api.customer.scales.batch.status');



    // 处理事项信息设置
    Route::get('/data-config/process-informations', 'ProcessInformationController@index')->name('api.process.informations.index');
    Route::get('/data-config/process-informations/options', 'ProcessInformationController@options')->name('api.process.informations.options');
    Route::get('/data-config/process-informations/filtered-options', 'ProcessInformationController@getFilteredOptions')->name('api.process.informations.filtered.options');
    Route::get('/data-config/process-informations/by-apply-type', 'ProcessInformationController@getByApplyType')->name('api.process.informations.by.apply.type');
    Route::post('/data-config/process-informations', 'ProcessInformationController@store')->name('api.process.informations.store');
    Route::get('/data-config/process-informations/{id}', 'ProcessInformationController@show')->name('api.process.informations.show');
    Route::put('/data-config/process-informations/{id}', 'ProcessInformationController@update')->name('api.process.informations.update');
    Route::delete('/data-config/process-informations/{id}', 'ProcessInformationController@destroy')->name('api.process.informations.destroy');
    Route::post('/data-config/process-informations/batch-status', 'ProcessInformationController@batchUpdateStatus')->name('api.process.informations.batch.status');

    // 处理事项系数设置
    Route::get('/data-config/process-coefficients', 'ProcessCoefficientsController@index')->name('api.process.coefficients.index');
    Route::get('/data-config/process-coefficients/options', 'ProcessCoefficientsController@options')->name('api.process.coefficients.options');
    Route::post('/data-config/process-coefficients', 'ProcessCoefficientsController@store')->name('api.process.coefficients.store');
    Route::get('/data-config/process-coefficients/{id}', 'ProcessCoefficientsController@show')->name('api.process.coefficients.show');
    Route::put('/data-config/process-coefficients/{id}', 'ProcessCoefficientsController@update')->name('api.process.coefficients.update');
    Route::delete('/data-config/process-coefficients/{id}', 'ProcessCoefficientsController@destroy')->name('api.process.coefficients.destroy');
    Route::post('/data-config/process-coefficients/batch-status', 'ProcessCoefficientsController@batchUpdateStatus')->name('api.process.coefficients.batch.status');

    // 项目处理事项管理
    Route::get('/case-processes', 'CaseProcessController@index')->name('api.case.processes.index');//获取处理事项列表
    Route::post('/case-processes', 'CaseProcessController@store')->name('api.case.processes.store');// 创建处理事项
    Route::get('/case-processes/{id}', 'CaseProcessController@show')->name('api.case.processes.show');// 获取处理事项详情
    Route::put('/case-processes/{id}', 'CaseProcessController@update')->name('api.case.processes.update');//更新处理事项
    Route::delete('/case-processes/{id}', 'CaseProcessController@destroy')->name('api.case.processes.destroy');//删除处理事项
    Route::get('/case-processes/case/{caseId}', 'CaseProcessController@getByCaseId')->name('api.case.processes.by.case');//根据项目 ID 获取处理事项列表

    // 项目系数设置
    Route::get('/data-config/case-coefficients', 'CaseCoefficientController@index')->name('api.case.coefficients.index');//获取项目附件列表
    Route::get('/data-config/case-coefficients/options', 'CaseCoefficientController@options')->name('api.case.coefficients.options');//
    Route::post('/data-config/case-coefficients', 'CaseCoefficientController@store')->name('api.case.coefficients.store');// 创建项目附件记录
    Route::get('/data-config/case-coefficients/{id}', 'CaseCoefficientController@show')->name('api.case.coefficients.show');//   获取项目系数详情
    Route::put('/data-config/case-coefficients/{id}', 'CaseCoefficientController@update')->name('api.case.coefficients.update');//更新项目系数
    Route::delete('/data-config/case-coefficients/{id}', 'CaseCoefficientController@destroy')->name('api.case.coefficients.destroy');//删除项目系数
    Route::post('/data-config/case-coefficients/batch-status', 'CaseCoefficientController@batchUpdateStatus')->name('api.case.coefficients.batch.status');//批量更新项目系数状态



    // 相关类型设置
    Route::get('/data-config/related-types', 'RelatedTypesController@index')->name('api.related.types.index');
    Route::get('/data-config/related-types/options', 'RelatedTypesController@options')->name('api.related.types.options');
    Route::post('/data-config/related-types', 'RelatedTypesController@store')->name('api.related.types.store');
    Route::get('/data-config/related-types/{id}', 'RelatedTypesController@show')->name('api.related.types.show');
    Route::put('/data-config/related-types/{id}', 'RelatedTypesController@update')->name('api.related.types.update');
    Route::delete('/data-config/related-types/{id}', 'RelatedTypesController@destroy')->name('api.related.types.destroy');
    Route::post('/data-config/related-types/batch-status', 'RelatedTypesController@batchUpdateStatus')->name('api.related.types.batch.status');

    // 开票服务类型设置
    Route::get('/data-config/invoice-service-types', 'InvoiceServiceTypesController@index')->name('api.invoice.service.types.index');
    Route::get('/data-config/invoice-service-types/options', 'InvoiceServiceTypesController@options')->name('api.invoice.service.types.options');
    Route::post('/data-config/invoice-service-types', 'InvoiceServiceTypesController@store')->name('api.invoice.service.types.store');
    Route::get('/data-config/invoice-service-types/{id}', 'InvoiceServiceTypesController@show')->name('api.invoice.service.types.show');
    Route::put('/data-config/invoice-service-types/{id}', 'InvoiceServiceTypesController@update')->name('api.invoice.service.types.update');
    Route::delete('/data-config/invoice-service-types/{id}', 'InvoiceServiceTypesController@destroy')->name('api.invoice.service.types.destroy');
    Route::post('/data-config/invoice-service-types/batch-status', 'InvoiceServiceTypesController@batchUpdateStatus')->name('api.invoice.service.types.batch.status');



    // 文件分类设置
    Route::get('/data-config/file-categories', 'FileCategoriesController@index')->name('api.file.categories.index');
    Route::get('/data-config/file-categories/options', 'FileCategoriesController@options')->name('api.file.categories.options');
    Route::post('/data-config/file-categories', 'FileCategoriesController@store')->name('api.file.categories.store');
    Route::get('/data-config/file-categories/{id}', 'FileCategoriesController@show')->name('api.file.categories.show');
    Route::put('/data-config/file-categories/{id}', 'FileCategoriesController@update')->name('api.file.categories.update');
    Route::delete('/data-config/file-categories/{id}', 'FileCategoriesController@destroy')->name('api.file.categories.destroy');
    Route::post('/data-config/file-categories/batch-status', 'FileCategoriesController@batchUpdateStatus')->name('api.file.categories.batch.status');

    // 文件描述设置
    Route::get('/data-config/file-descriptions', 'FileDescriptionsController@index')->name('api.file.descriptions.index');
    Route::get('/data-config/file-descriptions/options', 'FileDescriptionsController@options')->name('api.file.descriptions.options');
    Route::get('/data-config/file-descriptions/tree', 'FileDescriptionsController@getTree')->name('api.file.descriptions.tree');
    Route::get('/data-config/file-descriptions/file-category-major', 'FileDescriptionsController@getFileCategoryMajor')->name('api.file.descriptions.file.category.major');
    Route::get('/data-config/file-descriptions/file-category-minor', 'FileDescriptionsController@getFileCategoryMinor')->name('api.file.descriptions.file.category.minor');
    Route::post('/data-config/file-descriptions', 'FileDescriptionsController@store')->name('api.file.descriptions.store');
    Route::get('/data-config/file-descriptions/{id}', 'FileDescriptionsController@show')->name('api.file.descriptions.show');
    Route::put('/data-config/file-descriptions/{id}', 'FileDescriptionsController@update')->name('api.file.descriptions.update');
    Route::delete('/data-config/file-descriptions/{id}', 'FileDescriptionsController@destroy')->name('api.file.descriptions.destroy');
    Route::post('/data-config/file-descriptions/batch-status', 'FileDescriptionsController@batchUpdateStatus')->name('api.file.descriptions.batch.status');



    // 版权加快类型设置
    Route::get('/data-config/copyright-expedite-types', 'CopyrightExpediteTypesController@index')->name('api.copyright.expedite.types.index');
    Route::get('/data-config/copyright-expedite-types/options', 'CopyrightExpediteTypesController@options')->name('api.copyright.expedite.types.options');
    Route::post('/data-config/copyright-expedite-types', 'CopyrightExpediteTypesController@store')->name('api.copyright.expedite.types.store');
    Route::get('/data-config/copyright-expedite-types/{id}', 'CopyrightExpediteTypesController@show')->name('api.copyright.expedite.types.show');
    Route::put('/data-config/copyright-expedite-types/{id}', 'CopyrightExpediteTypesController@update')->name('api.copyright.expedite.types.update');
    Route::delete('/data-config/copyright-expedite-types/{id}', 'CopyrightExpediteTypesController@destroy')->name('api.copyright.expedite.types.destroy');

    // 我方公司设置
    Route::get('/data-config/our-companies', 'OurCompaniesController@index')->name('api.our.companies.index');
    Route::get('/data-config/our-companies/options', 'OurCompaniesController@options')->name('api.our.companies.options');
    Route::post('/data-config/our-companies', 'OurCompaniesController@store')->name('api.our.companies.store');
    Route::get('/data-config/our-companies/{id}', 'OurCompaniesController@show')->name('api.our.companies.show');
    Route::put('/data-config/our-companies/{id}', 'OurCompaniesController@update')->name('api.our.companies.update');
    Route::delete('/data-config/our-companies/{id}', 'OurCompaniesController@destroy')->name('api.our.companies.destroy');

    // 提成类型设置
    Route::get('/data-config/commission-types', 'CommissionTypesController@index')->name('api.commission.types.index');
    Route::get('/data-config/commission-types/options', 'CommissionTypesController@options')->name('api.commission.types.options');
    Route::post('/data-config/commission-types', 'CommissionTypesController@store')->name('api.commission.types.store');
    Route::get('/data-config/commission-types/{id}', 'CommissionTypesController@show')->name('api.commission.types.show');
    Route::put('/data-config/commission-types/{id}', 'CommissionTypesController@update')->name('api.commission.types.update');
    Route::delete('/data-config/commission-types/{id}', 'CommissionTypesController@destroy')->name('api.commission.types.destroy');

    // 提成配置设置
    Route::get('/data-config/commission-settings', 'CommissionSettingsController@index')->name('api.commission.settings.index');
    Route::get('/data-config/commission-settings/options', 'CommissionSettingsController@options')->name('api.commission.settings.options');
    Route::post('/data-config/commission-settings', 'CommissionSettingsController@store')->name('api.commission.settings.store');
    Route::get('/data-config/commission-settings/{id}', 'CommissionSettingsController@show')->name('api.commission.settings.show');
    Route::put('/data-config/commission-settings/{id}', 'CommissionSettingsController@update')->name('api.commission.settings.update');
    Route::delete('/data-config/commission-settings/{id}', 'CommissionSettingsController@destroy')->name('api.commission.settings.destroy');



    // 处理事项系数设置
    Route::get('/data-config/process-coefficients', 'ProcessCoefficientsController@index')->name('api.process.coefficients.index');
    Route::get('/data-config/process-coefficients/options', 'ProcessCoefficientsController@options')->name('api.process.coefficients.options');
    Route::post('/data-config/process-coefficients', 'ProcessCoefficientsController@store')->name('api.process.coefficients.store');
    Route::get('/data-config/process-coefficients/{id}', 'ProcessCoefficientsController@show')->name('api.process.coefficients.show');
    Route::put('/data-config/process-coefficients/{id}', 'ProcessCoefficientsController@update')->name('api.process.coefficients.update');
    Route::delete('/data-config/process-coefficients/{id}', 'ProcessCoefficientsController@destroy')->name('api.process.coefficients.destroy');
    Route::post('/data-config/process-coefficients/batch-status', 'ProcessCoefficientsController@batchUpdateStatus')->name('api.process.coefficients.batch.status');



    // 专利年费配置
    Route::get('/data-config/patent-annual-fees', 'PatentAnnualFeesController@index')->name('api.patent.annual.fees.index');
    Route::get('/data-config/patent-annual-fees/options', 'PatentAnnualFeesController@options')->name('api.patent.annual.fees.options');
    Route::post('/data-config/patent-annual-fees', 'PatentAnnualFeesController@store')->name('api.patent.annual.fees.store');
    Route::get('/data-config/patent-annual-fees/{id}', 'PatentAnnualFeesController@show')->name('api.patent.annual.fees.show');
    Route::put('/data-config/patent-annual-fees/{id}', 'PatentAnnualFeesController@update')->name('api.patent.annual.fees.update');
    Route::delete('/data-config/patent-annual-fees/{id}', 'PatentAnnualFeesController@destroy')->name('api.patent.annual.fees.destroy');

    // 专利年费详情管理
    Route::get('/data-config/patent-annual-fees/{id}/details', 'PatentAnnualFeesController@getFeeDetails')->name('api.patent.annual.fees.details');
    Route::post('/data-config/patent-annual-fee-details', 'PatentAnnualFeesController@createFeeDetail')->name('api.patent.annual.fee.details.store');
    Route::put('/data-config/patent-annual-fee-details/{id}', 'PatentAnnualFeesController@updateFeeDetail')->name('api.patent.annual.fee.details.update');
    Route::delete('/data-config/patent-annual-fee-details/{id}', 'PatentAnnualFeesController@deleteFeeDetail')->name('api.patent.annual.fee.details.destroy');

    // 科技服务类型设置
    Route::get('/data-config/tech-service-types', 'TechServiceTypesController@index')->name('api.tech.service.types.index');
    Route::get('/data-config/tech-service-types/options', 'TechServiceTypesController@options')->name('api.tech.service.types.options');
    Route::post('/data-config/tech-service-types', 'TechServiceTypesController@store')->name('api.tech.service.types.store');
    Route::get('/data-config/tech-service-types/{id}', 'TechServiceTypesController@show')->name('api.tech.service.types.show');
    Route::put('/data-config/tech-service-types/{id}', 'TechServiceTypesController@update')->name('api.tech.service.types.update');
    Route::delete('/data-config/tech-service-types/{id}', 'TechServiceTypesController@destroy')->name('api.tech.service.types.destroy');
    Route::post('/data-config/tech-service-types/batch-status', 'TechServiceTypesController@batchUpdateStatus')->name('api.tech.service.types.batch.status');

    // 科技服务事项设置
    Route::get('/data-config/tech-service-items', 'TechServiceItemsController@index')->name('api.tech.service.items.index');
    Route::get('/data-config/tech-service-items/type/{typeId}', 'TechServiceItemsController@getByTypeId')->name('api.tech.service.items.by.type');

    // 查询功能API
    Route::prefix('search')->group(function () {
        // 专利查询
        Route::get('/patents', 'SearchController@searchPatents')->name('api.search.patents');
        Route::post('/patents/export', 'SearchController@exportPatents')->name('api.search.patents.export');

        // 商标查询
        Route::get('/trademarks', 'SearchController@searchTrademarks')->name('api.search.trademarks');
        Route::post('/trademarks/export', 'SearchController@exportTrademarks')->name('api.search.trademarks.export');

        // 版权查询
        Route::get('/copyrights', 'SearchController@searchCopyrights')->name('api.search.copyrights');
        Route::post('/copyrights/export', 'SearchController@exportCopyrights')->name('api.search.copyrights.export');

        // 科服查询
        Route::get('/projects', 'SearchController@searchProjects')->name('api.search.projects');
        Route::post('/projects/export', 'SearchController@exportProjects')->name('api.search.projects.export');

        // 详情页API
        Route::get('/patents/{id}/detail', 'SearchController@getPatentDetail')->name('api.search.patents.detail');
        Route::get('/trademarks/{id}/detail', 'SearchController@getTrademarkDetail')->name('api.search.trademarks.detail');
        Route::get('/copyrights/{id}/detail', 'SearchController@getCopyrightDetail')->name('api.search.copyrights.detail');
        Route::get('/projects/{id}/detail', 'SearchController@getProjectDetail')->name('api.search.projects.detail');

        // 文件管理查询（已移至公开路由）

        // 辅助数据接口
        Route::get('/business-persons', 'SearchController@getBusinessPersons')->name('api.search.business.persons');
        Route::get('/case-handlers', 'SearchController@getCaseHandlers')->name('api.search.case.handlers');
        Route::get('/tech-leaders', 'SearchController@getTechLeaders')->name('api.search.tech.leaders');
        Route::get('/regions', 'SearchController@getRegions')->name('api.search.regions');
        Route::get('/agencies', 'SearchController@getAgencies')->name('api.search.agencies');
        Route::get('/departments', 'SearchController@getDepartments')->name('api.search.departments');
        Route::get('/countries', 'SearchController@getCountries')->name('api.search.countries');
    });
    Route::get('/data-config/tech-service-items/region/{regionId}', 'TechServiceItemsController@getByRegionId')->name('api.tech.service.items.by.region');
    Route::post('/data-config/tech-service-items', 'TechServiceItemsController@store')->name('api.tech.service.items.store');
    Route::get('/data-config/tech-service-items/{id}', 'TechServiceItemsController@show')->name('api.tech.service.items.show');
    Route::put('/data-config/tech-service-items/{id}', 'TechServiceItemsController@update')->name('api.tech.service.items.update');
    Route::delete('/data-config/tech-service-items/{id}', 'TechServiceItemsController@destroy')->name('api.tech.service.items.destroy');

    // 科技服务地区设置
    Route::get('/data-config/tech-service-regions', 'TechServiceRegionsController@index')->name('api.tech.service.regions.index');
    Route::get('/data-config/tech-service-regions/tree', 'TechServiceRegionsController@getTreeData')->name('api.tech.service.regions.tree');
    Route::post('/data-config/tech-service-regions', 'TechServiceRegionsController@store')->name('api.tech.service.regions.store');
    Route::get('/data-config/tech-service-regions/{id}', 'TechServiceRegionsController@show')->name('api.tech.service.regions.show');
    Route::put('/data-config/tech-service-regions/{id}', 'TechServiceRegionsController@update')->name('api.tech.service.regions.update');
    Route::delete('/data-config/tech-service-regions/{id}', 'TechServiceRegionsController@destroy')->name('api.tech.service.regions.destroy');

    // 审核打分项设置
    Route::get('/data-config/manuscript-scoring-items', 'ManuscriptScoringItemsController@index')->name('api.manuscript.scoring.items.index');
    Route::get('/data-config/manuscript-scoring-items/options', 'ManuscriptScoringItemsController@options')->name('api.manuscript.scoring.items.options');
    Route::post('/data-config/manuscript-scoring-items', 'ManuscriptScoringItemsController@store')->name('api.manuscript.scoring.items.store');
    Route::get('/data-config/manuscript-scoring-items/{id}', 'ManuscriptScoringItemsController@show')->name('api.manuscript.scoring.items.show');
    Route::put('/data-config/manuscript-scoring-items/{id}', 'ManuscriptScoringItemsController@update')->name('api.manuscript.scoring.items.update');
    Route::delete('/data-config/manuscript-scoring-items/{id}', 'ManuscriptScoringItemsController@destroy')->name('api.manuscript.scoring.items.destroy');
    Route::post('/data-config/manuscript-scoring-items/batch-status', 'ManuscriptScoringItemsController@batchUpdateStatus')->name('api.manuscript.scoring.items.batch.status');

    // 保护中心设置
    Route::get('/data-config/protection-centers', 'ProtectionCentersController@index')->name('api.protection.centers.index');
    Route::get('/data-config/protection-centers/options', 'ProtectionCentersController@options')->name('api.protection.centers.options');
    Route::post('/data-config/protection-centers', 'ProtectionCentersController@store')->name('api.protection.centers.store');
    Route::get('/data-config/protection-centers/{id}', 'ProtectionCentersController@show')->name('api.protection.centers.show');
    Route::put('/data-config/protection-centers/{id}', 'ProtectionCentersController@update')->name('api.protection.centers.update');
    Route::delete('/data-config/protection-centers/{id}', 'ProtectionCentersController@destroy')->name('api.protection.centers.destroy');
    Route::post('/data-config/protection-centers/batch-status', 'ProtectionCentersController@batchUpdateStatus')->name('api.protection.centers.batch.status');

    // 合同项目管理
    Route::get('/contract-cases', 'ContractCaseController@index')->name('api.contract.cases.index');
    Route::get('/contract-cases/{id}', 'ContractCaseController@show')->name('api.contract.cases.show');
    Route::post('/contract-cases', 'ContractCaseController@store')->name('api.contract.cases.store');
    Route::put('/contract-cases/{id}', 'ContractCaseController@update')->name('api.contract.cases.update');
    Route::delete('/contract-cases/{id}', 'ContractCaseController@destroy')->name('api.contract.cases.destroy');
    Route::post('/contract-cases/{id}/start-workflow', 'ContractCaseController@startWorkflow')->name('api.contract.cases.start.workflow');
    Route::get('/contract-cases/{id}/workflow', 'ContractCaseController@getWorkflow')->name('api.contract.cases.workflow');
    Route::get('/contract-cases/debug/pending', 'ContractCaseController@debugPendingData')->name('api.contract.cases.debug.pending');

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
});

// 测试路由
Route::get('/test-workflow-config', function(\Illuminate\Http\Request $request) {
    $controller = new \App\Http\Controllers\Api\WorkflowConfigController();
    return $controller->getList($request);
});
    Route::get('/contract-cases/options/case-types', 'ContractCaseController@getCaseTypeOptions')->name('api.contract.cases.options.case.types');
    Route::get('/contract-cases/options/case-statuses', 'ContractCaseController@getCaseStatusOptions')->name('api.contract.cases.options.case.statuses');

    // 合同项目记录管理
    Route::get('/contract-case-records', 'ContractCaseRecordController@index')->name('api.contract.case.records.index');
    Route::get('/contract-case-records/{id}', 'ContractCaseRecordController@show')->name('api.contract.case.records.show');
    Route::get('/contract-case-records/by-case/{caseId}', 'ContractCaseRecordController@getByCaseId')->name('api.contract.case.records.by.case');
    Route::post('/contract-case-records', 'ContractCaseRecordController@store')->name('api.contract.case.records.store');
    Route::put('/contract-case-records/{id}', 'ContractCaseRecordController@update')->name('api.contract.case.records.update');
    Route::delete('/contract-case-records/{id}', 'ContractCaseRecordController@destroy')->name('api.contract.case.records.destroy');
    Route::post('/contract-case-records/{id}/file', 'ContractCaseRecordController@file')->name('api.contract.case.records.file');

    // 项目处理事项更新管理
    Route::get('/case-processes/update-list', 'CaseProcessController@getUpdateList')->name('api.case.processes.update.list');
    Route::get('/case-processes/case/{caseId}/detail', 'CaseProcessController@getCaseDetail')->name('api.case.processes.case.detail');
    Route::get('/case-processes/case/{caseId}', 'CaseProcessController@getCaseProcesses')->name('api.case.processes.case.processes');
    Route::post('/case-processes/case/{caseId}/update', 'CaseProcessController@updateCaseProcesses')->name('api.case.processes.case.update');
    Route::post('/case-processes/batch-update', 'CaseProcessController@batchUpdate')->name('api.case.processes.batch.update');

});



