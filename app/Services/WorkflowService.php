<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Models\WorkflowProcess;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class WorkflowService
{
    /**
     * 启动工作流
     *
     * @param string $businessType 业务类型
     * @param int $businessId 业务ID
     * @param string $businessTitle 业务标题
     * @param int $workflowId 工作流ID
     * @param int $createdBy 创建人ID
     * @return WorkflowInstance
     * @throws Exception
     */
    public function startWorkflow(
        $businessType,
        $businessId,
        $businessTitle,
        $workflowId,
        $createdBy
    ) {
        try {
            DB::beginTransaction();

            // 检查是否已存在进行中的工作流
            $existingInstance = WorkflowInstance::where('business_type', $businessType)
                ->where('business_id', $businessId)
                ->where('status', WorkflowInstance::STATUS_PENDING)
                ->first();

            if ($existingInstance) {
                // 如果工作流在第0个节点，检查是否已经有实际处理过的节点
                if ($existingInstance->current_node_index === 0) {
                    // 检查是否有任何已处理的记录（除了自动处理的启动节点）
                    $hasProcessedTask = WorkflowProcess::where('instance_id', $existingInstance->id)
                        ->where('node_index', '>', 0)
                        ->whereIn('action', [WorkflowProcess::ACTION_APPROVE, WorkflowProcess::ACTION_REJECT])
                        ->exists();
                    
                    if (!$hasProcessedTask) {
                        // 如果没有任何已处理的非起始节点，说明是被驳回的或从未真正开始，可以取消
                        $existingInstance->update(['status' => WorkflowInstance::STATUS_CANCELLED]);
                        Log::info('自动取消已退回到起始节点的工作流', [
                            'instance_id' => $existingInstance->id,
                            'business_type' => $businessType,
                            'business_id' => $businessId,
                            'current_node_index' => $existingInstance->current_node_index,
                            'reason' => '工作流已退回到起始节点或从未开始，允许重新发起'
                        ]);
                    } else {
                        throw new Exception('该业务已存在进行中的工作流');
                    }
                } else {
                    throw new Exception('该业务已存在进行中的工作流');
                }
            }

            // 获取工作流配置
            $workflow = Workflow::findOrFail($workflowId);
            $nodes = $workflow->nodes;

            if (empty($nodes)) {
                throw new Exception('工作流配置无效');
            }

            // 创建工作流实例
            $instance = WorkflowInstance::create([
                'workflow_id' => $workflowId,
                'business_id' => $businessId,
                'business_type' => $businessType,
                'business_title' => $businessTitle,
                'current_node_index' => 0,
                'status' => WorkflowInstance::STATUS_PENDING,
                'created_by' => $createdBy,
            ]);

            // 创建所有节点的处理记录
            foreach ($nodes as $index => $node) {
                $assigneeIds = $node['assignee'] ?? [];
                
                // 如果节点不需要审核，创建为待处理状态，后续会自动处理
                if (!($node['required'] ?? true)) {
                    WorkflowProcess::create([
                        'instance_id' => $instance->id,
                        'node_index' => $index,
                        'node_name' => $node['name'],
                        'assignee_id' => null,
                        'processor_id' => null,
                        'action' => WorkflowProcess::ACTION_PENDING,
                        'comment' => null,
                        'processed_at' => null,
                    ]);
                } else {
                    // 为每个指定的处理人创建处理记录
                    if (empty($assigneeIds)) {
                        // 如果没有指定处理人，创建一个待分配的记录
                        WorkflowProcess::create([
                            'instance_id' => $instance->id,
                            'node_index' => $index,
                            'node_name' => $node['name'],
                            'assignee_id' => null,
                            'processor_id' => null,
                            'action' => WorkflowProcess::ACTION_PENDING,
                            'comment' => null,
                            'processed_at' => null,
                        ]);
                    } else {
                        // 为第一个处理人创建待处理记录，其他人暂时不创建
                        $firstAssigneeId = $assigneeIds[0];
                        WorkflowProcess::create([
                            'instance_id' => $instance->id,
                            'node_index' => $index,
                            'node_name' => $node['name'],
                            'assignee_id' => $firstAssigneeId,
                            'processor_id' => null,
                            'action' => WorkflowProcess::ACTION_PENDING,
                            'comment' => null,
                            'processed_at' => null,
                        ]);
                    }
                }
            }

            // 同步业务状态（工作流启动后状态变为审批中）
            $this->syncBusinessStatus($instance);
            
            // 自动处理第一个节点（如果是自动通过的话）
            $this->processNextNode($instance);

            DB::commit();

            Log::info('工作流启动成功', [
                'instance_id' => $instance->id,
                'business_type' => $businessType,
                'business_id' => $businessId,
                'workflow_id' => $workflowId,
            ]);

            return $instance->fresh(['workflow', 'creator', 'processes']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('工作流启动失败', [
                'error' => $e->getMessage(),
                'business_type' => $businessType,
                'business_id' => $businessId,
                'workflow_id' => $workflowId,
            ]);
            throw $e;
        }
    }

    /**
     * 处理工作流节点
     *
     * @param int $processId 处理记录ID
     * @param string $action 处理动作 (approve/reject)
     * @param string|null $comment 处理备注
     * @param int $processorId 处理人ID
     * @return WorkflowProcess
     * @throws Exception
     */
    public function processNode(
        $processId,
        $action,
        $comment,
        $processorId,
        $backToNodeIndex = null
    ) {
        try {
            DB::beginTransaction();

            $process = WorkflowProcess::with(['instance.workflow'])->findOrFail($processId);
            $instance = $process->instance;

            // 检查工作流状态
            if ($instance->status !== WorkflowInstance::STATUS_PENDING) {
                throw new Exception('工作流已结束，无法处理');
            }

            // 检查是否为当前节点
            if ($process->node_index !== $instance->current_node_index) {
                throw new Exception('只能处理当前节点');
            }

            // 检查处理权限
            if ($process->assignee_id && $process->assignee_id !== $processorId) {
                throw new Exception('无权限处理此节点');
            }

            // 检查是否已处理
            if ($process->isProcessed()) {
                throw new Exception('该节点已处理');
            }

            // 根据动作处理
            if ($action === 'back') {
                // 退回到指定节点
                if ($backToNodeIndex === null) {
                    throw new Exception('退回操作需要指定退回节点');
                }

                // 更新处理记录 - 退回操作在数据库中存储为reject
                $process->update([
                    'processor_id' => $processorId,
                    'action' => WorkflowProcess::ACTION_REJECT,
                    'comment' => $comment,
                    'processed_at' => now(),
                ]);

                $this->backToNode($instance, $backToNodeIndex, $comment, $processorId);
            } elseif ($action === WorkflowProcess::ACTION_REJECT) {
                // 直接驳回，结束工作流
                $process->update([
                    'processor_id' => $processorId,
                    'action' => $action,
                    'comment' => $comment,
                    'processed_at' => now(),
                ]);

                $instance->update(['status' => WorkflowInstance::STATUS_REJECTED]);
                $this->syncBusinessStatus($instance);
            } else {
                // 通过，处理下一个节点
                $process->update([
                    'processor_id' => $processorId,
                    'action' => $action,
                    'comment' => $comment,
                    'processed_at' => now(),
                ]);

                $this->processNextNode($instance);
            }

            DB::commit();

            Log::info('工作流节点处理成功', [
                'process_id' => $processId,
                'instance_id' => $instance->id,
                'action' => $action,
                'processor_id' => $processorId,
            ]);

            return $process->fresh(['instance', 'assignee', 'processor']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('工作流节点处理失败', [
                'error' => $e->getMessage(),
                'process_id' => $processId,
                'action' => $action,
                'processor_id' => $processorId,
            ]);
            throw $e;
        }
    }

    /**
     * 处理下一个节点
     *
     * @param WorkflowInstance $instance
     * @return void
     */
    private function processNextNode($instance)
    {
        $nodes = $instance->workflow->nodes;
        $currentIndex = $instance->current_node_index;

        // 首先检查当前节点是否为自动通过节点
        $currentNode = $nodes[$currentIndex];
        if (!($currentNode['required'] ?? true)) {
            // 当前节点是自动通过的，先处理它
            $currentProcess = WorkflowProcess::where('instance_id', $instance->id)
                ->where('node_index', $currentIndex)
                ->first();
                
            if ($currentProcess && $currentProcess->isPending()) {
                $currentProcess->update([
                    'action' => WorkflowProcess::ACTION_AUTO,
                    'comment' => '自动通过',
                    'processed_at' => now(),
                ]);
            }
        }

        // 检查当前节点是否已处理
        $currentProcess = WorkflowProcess::where('instance_id', $instance->id)
            ->where('node_index', $currentIndex)
            ->first();

        if (!$currentProcess || !$currentProcess->isProcessed()) {
            return; // 当前节点未处理，不能进入下一节点
        }

        // 如果当前节点被驳回，不处理下一节点
        if ($currentProcess->isRejected()) {
            return;
        }

        // 移动到下一个节点
        $nextIndex = $currentIndex + 1;

        if ($nextIndex >= count($nodes)) {
            // 已到达最后一个节点，完成工作流
            $instance->update(['status' => WorkflowInstance::STATUS_COMPLETED]);
            $this->syncBusinessStatus($instance);
            return;
        }

        // 更新当前节点索引
        $instance->update(['current_node_index' => $nextIndex]);

        // 检查下一个节点是否为自动通过
        $nextNode = $nodes[$nextIndex];
        if (!($nextNode['required'] ?? true)) {
            // 自动通过下一个节点
            $nextProcess = WorkflowProcess::where('instance_id', $instance->id)
                ->where('node_index', $nextIndex)
                ->first();

            if ($nextProcess && $nextProcess->isPending()) {
                $nextProcess->update([
                    'action' => WorkflowProcess::ACTION_AUTO,
                    'comment' => '自动通过',
                    'processed_at' => now(),
                ]);

                // 递归处理下一个节点
                $this->processNextNode($instance);
            }
        }
    }

    /**
     * 启动工作流（带用户选择的处理人）
     *
     * @param string $businessType
     * @param int $businessId
     * @param string $businessTitle
     * @param int $workflowId
     * @param int $createdBy
     * @param array $selectedAssignees 用户选择的处理人 [nodeIndex => userId]
     * @return WorkflowInstance
     * @throws Exception
     */
    public function startWorkflowWithAssignees(
        $businessType,
        $businessId,
        $businessTitle,
        $workflowId,
        $createdBy,
        $selectedAssignees = []
    ) {
        DB::beginTransaction();

        try {
            // 检查是否已存在进行中的工作流
            $existingInstance = WorkflowInstance::where('business_type', $businessType)
                ->where('business_id', $businessId)
                ->where('status', WorkflowInstance::STATUS_PENDING)
                ->first();

            if ($existingInstance) {
                // 如果工作流在第0个节点，检查是否已经有实际处理过的节点
                if ($existingInstance->current_node_index === 0) {
                    // 检查是否有任何已处理的记录（除了自动处理的启动节点）
                    $hasProcessedTask = WorkflowProcess::where('instance_id', $existingInstance->id)
                        ->where('node_index', '>', 0)
                        ->whereIn('action', [WorkflowProcess::ACTION_APPROVE, WorkflowProcess::ACTION_REJECT])
                        ->exists();
                    
                    if (!$hasProcessedTask) {
                        // 如果没有任何已处理的非起始节点，说明是被驳回的或从未真正开始，可以取消
                        $existingInstance->update(['status' => WorkflowInstance::STATUS_CANCELLED]);
                        Log::info('自动取消已退回到起始节点的工作流（带选择处理人）', [
                            'instance_id' => $existingInstance->id,
                            'business_type' => $businessType,
                            'business_id' => $businessId,
                            'current_node_index' => $existingInstance->current_node_index,
                            'reason' => '工作流已退回到起始节点或从未开始，允许重新发起'
                        ]);
                    } else {
                        throw new Exception('该业务已存在进行中的工作流');
                    }
                } else {
                    throw new Exception('该业务已存在进行中的工作流');
                }
            }

            // 获取工作流配置
            $workflow = Workflow::findOrFail($workflowId);
            $nodes = $workflow->nodes;

            if (empty($nodes)) {
                throw new Exception('工作流配置无效');
            }

            // 创建工作流实例
            $instance = WorkflowInstance::create([
                'workflow_id' => $workflowId,
                'business_id' => $businessId,
                'business_type' => $businessType,
                'business_title' => $businessTitle,
                'current_node_index' => 0,
                'status' => WorkflowInstance::STATUS_PENDING,
                'created_by' => $createdBy,
            ]);

            // 添加日志
            \Log::info('WorkflowService: startWorkflowWithAssignees', [
                'selectedAssignees' => $selectedAssignees,
                'nodeCount' => count($nodes)
            ]);

            // 创建所有节点的处理记录
            foreach ($nodes as $index => $node) {
                // 如果用户选择了处理人，使用用户选择的
                if (isset($selectedAssignees[$index])) {
                    $assigneeIds = [$selectedAssignees[$index]];
                    \Log::info('WorkflowService: 使用用户选择的处理人', [
                        'nodeIndex' => $index,
                        'nodeName' => $node['name'],
                        'selectedAssignee' => $selectedAssignees[$index]
                    ]);
                } else {
                    // 否则使用配置的候选人池
                    $assigneeIds = $node['assignee'] ?? [];
                    \Log::info('WorkflowService: 使用配置的候选人池', [
                        'nodeIndex' => $index,
                        'nodeName' => $node['name'],
                        'assigneeIds' => $assigneeIds
                    ]);
                }

                // 为每个处理人创建处理记录
                if (!empty($assigneeIds)) {
                    foreach ($assigneeIds as $assigneeId) {
                        WorkflowProcess::create([
                            'instance_id' => $instance->id,
                            'node_index' => $index,
                            'node_name' => $node['name'],
                            'assignee_id' => $assigneeId,
                            'action' => WorkflowProcess::ACTION_PENDING,
                        ]);
                    }
                } else {
                    // 没有处理人的节点（如自动通过节点）
                    WorkflowProcess::create([
                        'instance_id' => $instance->id,
                        'node_index' => $index,
                        'node_name' => $node['name'],
                        'assignee_id' => null,
                        'action' => WorkflowProcess::ACTION_PENDING,
                    ]);
                }
            }

            // 同步业务状态（工作流启动后状态变为审批中）
            $this->syncBusinessStatus($instance);
            
            // 自动处理第一个节点（如果是自动通过的话）
            $this->processNextNode($instance);

            DB::commit();

            Log::info('工作流启动成功（带处理人选择）', [
                'instance_id' => $instance->id,
                'business_type' => $businessType,
                'business_id' => $businessId,
                'workflow_id' => $workflowId,
                'selected_assignees' => $selectedAssignees,
            ]);

            return $instance->fresh(['workflow', 'creator', 'processes']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('工作流启动失败（带处理人选择）', [
                'error' => $e->getMessage(),
                'business_type' => $businessType,
                'business_id' => $businessId,
                'workflow_id' => $workflowId,
                'selected_assignees' => $selectedAssignees,
            ]);
            throw $e;
        }
    }

    /**
     * 退回到指定节点
     *
     * @param WorkflowInstance $instance
     * @param int $backToNodeIndex 退回到的节点索引
     * @param string $comment 退回原因
     * @param int $processorId 处理人ID
     * @return void
     * @throws Exception
     */
    private function backToNode($instance, $backToNodeIndex, $comment, $processorId)
    {
        $currentIndex = $instance->current_node_index;

        // 验证退回节点索引
        if ($backToNodeIndex >= $currentIndex) {
            throw new Exception('只能退回到之前的节点');
        }

        if ($backToNodeIndex < 0) {
            throw new Exception('无效的退回节点索引');
        }

        // 更新工作流实例的当前节点索引
        // 如果退回到第0个节点（启动节点），将状态设置为rejected以允许重新发起
        $newStatus = ($backToNodeIndex === 0) ? WorkflowInstance::STATUS_REJECTED : WorkflowInstance::STATUS_PENDING;
        
        $instance->update([
            'current_node_index' => $backToNodeIndex,
            'status' => $newStatus
        ]);
        
        \Log::info('工作流退回状态更新', [
            'instance_id' => $instance->id,
            'backToNodeIndex' => $backToNodeIndex,
            'newStatus' => $newStatus,
            'reason' => $backToNodeIndex === 0 ? '退回到启动节点，设置为rejected允许重新发起' : '退回到中间节点，保持pending状态'
        ]);

        // 重置从退回节点开始的所有后续节点状态
        WorkflowProcess::where('instance_id', $instance->id)
            ->where('node_index', '>=', $backToNodeIndex)
            ->update([
                'action' => WorkflowProcess::ACTION_PENDING,
                'processor_id' => null,
                'processed_at' => null,
                'comment' => null
            ]);

        Log::info('工作流退回成功', [
            'instance_id' => $instance->id,
            'from_node' => $currentIndex,
            'to_node' => $backToNodeIndex,
            'processor_id' => $processorId,
            'comment' => $comment
        ]);
    }

    /**
     * 同步业务状态
     * 根据工作流状态更新相关业务对象的状态
     *
     * @param WorkflowInstance $instance
     * @return void
     */
    private function syncBusinessStatus($instance)
    {
        try {
            if ($instance->business_type === 'contract') {
                $contract = \App\Models\Contract::find($instance->business_id);
                if ($contract) {
                    $contract->updateStatusByWorkflow($instance->status);
                }
            } elseif ($instance->business_type === 'case') {
                // 处理案件类型的状态同步
                $case = \App\Models\Cases::find($instance->business_id);
                if ($case) {
                    // 根据工作流状态更新案件状态
                    switch ($instance->status) {
                        case \App\Models\WorkflowInstance::STATUS_PENDING:
                            $case->update(['case_status' => \App\Models\Cases::STATUS_TO_BE_FILED]);
                            break;
                        case \App\Models\WorkflowInstance::STATUS_COMPLETED:
                            $case->update(['case_status' => \App\Models\Cases::STATUS_COMPLETED]);
                            break;
                        case \App\Models\WorkflowInstance::STATUS_REJECTED:
                            $case->update(['case_status' => \App\Models\Cases::STATUS_DRAFT]);
                            break;
                    }
                    
                    \Log::info('案件状态同步成功', [
                        'case_id' => $case->id,
                        'old_status' => $case->getOriginal('case_status'),
                        'new_status' => $case->case_status,
                        'workflow_status' => $instance->status
                    ]);
                }
            }
            // 可以在这里添加其他业务类型的状态同步逻辑

            Log::info('业务状态同步成功', [
                'instance_id' => $instance->id,
                'business_type' => $instance->business_type,
                'business_id' => $instance->business_id,
                'workflow_status' => $instance->status
            ]);
        } catch (Exception $e) {
            Log::error('业务状态同步失败', [
                'instance_id' => $instance->id,
                'business_type' => $instance->business_type,
                'business_id' => $instance->business_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 获取用户的待处理任务
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingTasks($userId)
    {
        return WorkflowProcess::with(['instance.workflow', 'instance.creator'])
            ->where('assignee_id', $userId)
            ->where('action', WorkflowProcess::ACTION_PENDING)
            ->whereHas('instance', function ($query) {
                $query->where('status', WorkflowInstance::STATUS_PENDING);
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * 获取业务的工作流状态
     *
     * @param string $businessType
     * @param int $businessId
     * @return WorkflowInstance|null
     */
    public function getBusinessWorkflowStatus($businessType, $businessId)
    {
        return WorkflowInstance::with(['workflow', 'processes.assignee', 'processes.processor'])
            ->where('business_type', $businessType)
            ->where('business_id', $businessId)
            ->latest()
            ->first();
    }

    /**
     * 取消工作流
     *
     * @param int $instanceId
     * @param int $userId
     * @return WorkflowInstance
     * @throws Exception
     */
    public function cancelWorkflow($instanceId, $userId)
    {
        $instance = WorkflowInstance::findOrFail($instanceId);

        if ($instance->status !== WorkflowInstance::STATUS_PENDING) {
            throw new Exception('只能取消进行中的工作流');
        }

        if ($instance->created_by !== $userId) {
            throw new Exception('只能取消自己创建的工作流');
        }

        $instance->update(['status' => WorkflowInstance::STATUS_CANCELLED]);

        Log::info('工作流已取消', [
            'instance_id' => $instanceId,
            'cancelled_by' => $userId,
        ]);

        return $instance;
    }
}
