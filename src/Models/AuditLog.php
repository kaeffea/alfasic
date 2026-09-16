<?php

declare(strict_types=1);

namespace Alfasic\Models;

use Alfasic\Core\Database;
use PDO;
use DateTime;
use Exception;

/**
 * Model da Trilha de Auditoria Universal & Motor Time-Travel (AuditLog)
 */
class AuditLog extends Model
{
    protected static string $table = 'audit_logs';

    /**
     * Dicionário Amigável de Campos em Português
     */
    public static array $fieldLabels = [
        'name' => 'Razão Social / Nome',
        'trade_name' => 'Nome Fantasia',
        'document' => 'CPF / CNPJ',
        'phone' => 'Telefone',
        'email' => 'E-mail',
        'payment_terms' => 'Condições de Pagamento',
        'credit_limit' => 'Limite de Crédito',
        'city' => 'Cidade',
        'state' => 'Estado / UF',
        'is_active' => 'Status Ativo/Inativo',
        'deleted_at' => 'Data de Exclusão',
        'standard_price' => 'Preço Padrão',
        'rental_fee' => 'Taxa de Locação',
        'role_title' => 'Cargo / Função',
        'driver_license' => 'CNH',
        'driver_license_category' => 'Categoria CNH',
        'has_mopp' => 'Possui MOPP',
        'branch' => 'Filial / Unidade',
        'username' => 'Usuário de Acesso',
        'role_id' => 'Perfil de Acesso',
        'code' => 'Código Interno',
        'capacity' => 'Capacidade Nominal',
        'unit' => 'Unidade de Medida',
        'unit_price' => 'Preço Unitário',
        'working_pressure_bar' => 'Pressão de Trabalho (bar)',
        'tare_weight_kg' => 'Tara (kg)',
        'replacement_value' => 'Valor de Reposição (R$)',
        'cylinder_type_id' => 'Tipo de Cilindro Vinculado',
        'product_id' => 'Produto Vinculado'
    ];

    /**
     * Mapeamento de Entidades para Classes Model
     */
    public static array $entityModelMap = [
        'clients' => Client::class,
        'suppliers' => Supplier::class,
        'employees' => Employee::class,
        'products' => Product::class,
        'cylinders' => Cylinder::class,
        'users' => User::class,
        'roles' => Role::class
    ];

    /**
     * Mapeamento de Entidades para Rótulos Amigáveis de Módulo
     */
    public static array $moduleLabels = [
        'clients' => 'Clientes',
        'suppliers' => 'Fornecedores',
        'employees' => 'Funcionários',
        'products' => 'Produtos',
        'cylinders' => 'Cilindros',
        'users' => 'Usuários',
        'roles' => 'Perfis',
        'audit' => 'Auditoria'
    ];

    /**
     * Grava um novo evento na Trilha de Auditoria
     */
    public static function record(
        string $entity,
        int $entityId,
        string $action,
        ?string $reason = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $recordLabel = null,
        ?string $contextModule = null
    ): int {
        $pdo = Database::getConnection();

        // 1. Identifica o autor da ação
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;
        $userName = $_SESSION['user_name'] ?? 'Administrador Master';
        $userRole = $_SESSION['user_role'] ?? 'Administrador Master';

        // 2. Metadados de rede
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Navegador Web';

        // 3. Módulo de contexto
        $module = $contextModule ?? (self::$moduleLabels[$entity] ?? ucfirst($entity));

        // 4. Determina campos modificados e resumo amigável
        $diffFields = [];
        $diffSummaryParts = [];

        // Limpa campos internos antes de calcular o diff
        $cleanOld = $oldValues;
        $cleanNew = $newValues;
        $ignoreKeys = ['csrf_token', '_method', 'created_at', 'updated_at', 'password'];

        if ($cleanOld) {
            foreach ($ignoreKeys as $ik) unset($cleanOld[$ik]);
        }
        if ($cleanNew) {
            foreach ($ignoreKeys as $ik) unset($cleanNew[$ik]);
        }

        if ($action === 'create' && $cleanNew) {
            foreach ($cleanNew as $k => $v) {
                if ($v !== null && $v !== '' && $k !== 'id') {
                    $diffSummaryParts[] = self::$fieldLabels[$k] ?? $k;
                }
            }
        } elseif ($cleanOld && $cleanNew) {
            $allKeys = array_unique(array_merge(array_keys($cleanOld), array_keys($cleanNew)));
            foreach ($allKeys as $k) {
                if ($k === 'id') continue;
                $ov = $cleanOld[$k] ?? null;
                $nv = $cleanNew[$k] ?? null;
                if (json_encode($ov) !== json_encode($nv)) {
                    $diffFields[] = $k;
                    $diffSummaryParts[] = self::$fieldLabels[$k] ?? $k;
                }
            }
        }

        $diffSummary = !empty($diffSummaryParts) ? implode(', ', $diffSummaryParts) : null;
        $isReversible = ($action !== 'force_delete') ? 1 : 0;

        // 5. Salva na tabela audit_logs
        $sql = "INSERT INTO audit_logs (
            user_id, user_name, user_role, entity, entity_id, action, record_label,
            reason, old_values, new_values, diff_fields, diff_summary, context_module,
            ip_address, user_agent, is_reversible, created_at
        ) VALUES (
            :user_id, :user_name, :user_role, :entity, :entity_id, :action, :record_label,
            :reason, :old_values, :new_values, :diff_fields, :diff_summary, :context_module,
            :ip_address, :user_agent, :is_reversible, NOW()
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId ? (int)$userId : null, $userId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':user_name', $userName);
        $stmt->bindValue(':user_role', $userRole);
        $stmt->bindValue(':entity', $entity);
        $stmt->bindValue(':entity_id', $entityId, PDO::PARAM_INT);
        $stmt->bindValue(':action', $action);
        $stmt->bindValue(':record_label', $recordLabel);
        $stmt->bindValue(':reason', $reason);
        $stmt->bindValue(':old_values', $cleanOld ? json_encode($cleanOld, JSON_UNESCAPED_UNICODE) : null);
        $stmt->bindValue(':new_values', $cleanNew ? json_encode($cleanNew, JSON_UNESCAPED_UNICODE) : null);
        $stmt->bindValue(':diff_fields', !empty($diffFields) ? json_encode($diffFields, JSON_UNESCAPED_UNICODE) : null);
        $stmt->bindValue(':diff_summary', $diffSummary);
        $stmt->bindValue(':context_module', $module);
        $stmt->bindValue(':ip_address', $ip);
        $stmt->bindValue(':user_agent', $userAgent);
        $stmt->bindValue(':is_reversible', $isReversible, PDO::PARAM_INT);

        $stmt->execute();
        return (int) $pdo->lastInsertId();
    }

    /**
     * Régua Estrita de Formatação de Tempo Relativo e Data Exata
     */
    public static function formatRelativeTime(string|int $timestamp): array
    {
        date_default_timezone_set('America/Maceio');

        if (is_numeric($timestamp)) {
            $dt = new DateTime();
            $dt->setTimestamp((int)$timestamp);
        } else {
            $dt = new DateTime((string)$timestamp);
        }

        $nowDt = new DateTime();
        $now = $nowDt->getTimestamp();
        $time = $dt->getTimestamp();
        $diff = max(0, $now - $time);

        if ($diff < 60) {
            $relative = $diff <= 1 ? 'há 1 segundo' : "há {$diff} segundos";
        } elseif ($diff < 3600) {
            $mins = max(1, (int) floor($diff / 60));
            $relative = $mins === 1 ? 'há 1 minuto' : "há {$mins} minutos";
        } elseif ($diff < 86400) {
            $hours = max(1, (int) floor($diff / 3600));
            $relative = $hours === 1 ? 'há 1 hora' : "há {$hours} horas";
        } elseif ($diff < 604800) { // até 7 dias
            $days = max(1, (int) floor($diff / 86400));
            $relative = $days === 1 ? 'há 1 dia' : "há {$days} dias";
        } elseif ($diff < 2419200) { // até 4 semanas (28 dias)
            $weeks = max(1, (int) floor($diff / 604800));
            $relative = $weeks === 1 ? 'há 1 semana' : "há {$weeks} semanas";
        } elseif ($diff < 31536000) { // até 12 meses
            $months = max(1, (int) floor($diff / 2592000));
            $relative = $months === 1 ? 'há 1 mês' : "há {$months} meses";
        } else {
            $years = max(1, (int) floor($diff / 31536000));
            $relative = $years === 1 ? 'há 1 ano' : "há {$years} anos";
        }

        $exact = $dt->format('d/m/Y \à\s H:i:s');
        $dateOnly = $dt->format('d/m/Y');

        return [
            'relative' => $relative,
            'exact' => $exact,
            'date' => $dateOnly
        ];
    }

    /**
     * Retorna a Linha do Tempo de Auditoria estruturada para a View
     */
    public static function getTimeline(array $filters = [], int $limit = 500, int $offset = 0): array
    {
        $pdo = Database::getConnection();
        $where = [];
        $params = [];

        if (!empty($filters['entity'])) {
            $where[] = "entity = :entity";
            $params[':entity'] = $filters['entity'];
        }

        if (!empty($filters['action'])) {
            $where[] = "action = :action";
            $params[':action'] = $filters['action'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = "user_id = :user_id";
            $params[':user_id'] = (int) $filters['user_id'];
        }

        if (!empty($filters['date'])) {
            // Suporta DD/MM/AAAA ou YYYY-MM-DD
            $d = $filters['date'];
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $d, $m)) {
                $d = "{$m[3]}-{$m[2]}-{$m[1]}";
            }
            $where[] = "DATE(created_at) = :date";
            $params[':date'] = $d;
        }

        if (!empty($filters['search'])) {
            $where[] = "(record_label LIKE :search OR reason LIKE :search OR user_name LIKE :search OR diff_summary LIKE :search OR context_module LIKE :search)";
            $params[':search'] = '%' . trim($filters['search']) . '%';
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT * FROM audit_logs {$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $colorMap = [
            'create' => '#10b981',       // Verde
            'update' => '#0284c7',       // Azul
            'activate' => '#0d9488',     // Teal
            'deactivate' => '#f59e0b',   // Âmbar
            'soft_delete' => '#ef4444',  // Vermelho
            'force_delete' => '#475569', // Ardósia/Slate
            'rollback' => '#6366f1'      // Índigo
        ];

        $labelMap = [
            'create' => 'Cadastro',
            'update' => 'Edição',
            'activate' => 'Ativação',
            'deactivate' => 'Inativação',
            'soft_delete' => 'Exclusão Parcial',
            'force_delete' => 'Exclusão Permanente',
            'rollback' => 'Reversão'
        ];

        // Agrupa entity_ids por entidade para verificar em lote quais registros estão atualmente em Soft Delete
        $entitiesToCheck = [];
        foreach ($rows as $r) {
            $ent = $r['entity'];
            $entId = (int) $r['entity_id'];
            if ($entId > 0 && isset(self::$entityModelMap[$ent])) {
                $entitiesToCheck[$ent][$entId] = true;
            }
        }

        $softDeletedMap = [];
        $existsMap = [];
        foreach ($entitiesToCheck as $entTable => $idMap) {
            $ids = array_keys($idMap);
            if (empty($ids)) continue;
            $idList = implode(',', array_map('intval', $ids));
            try {
                $stmtDel = $pdo->query("SELECT id FROM `{$entTable}` WHERE id IN ({$idList}) AND deleted_at IS NOT NULL");
                $delIds = $stmtDel ? $stmtDel->fetchAll(PDO::FETCH_COLUMN) : [];
                foreach ($delIds as $delId) {
                    $softDeletedMap[$entTable][(int)$delId] = true;
                }
            } catch (\Throwable $e) {
                // Silencioso se a tabela não tiver deleted_at
            }
            try {
                $stmtEx = $pdo->query("SELECT id FROM `{$entTable}` WHERE id IN ({$idList})");
                $exIds = $stmtEx ? $stmtEx->fetchAll(PDO::FETCH_COLUMN) : [];
                foreach ($exIds as $exId) {
                    $existsMap[$entTable][(int)$exId] = true;
                }
            } catch (\Throwable $e) {
                // Silencioso se a tabela não existir
            }
        }

        // Logs de exclusão permanente posteriores, por (entidade, id) -> maior id de force_delete
        $forceDeletedAfter = [];
        if (!empty($rows)) {
            try {
                // Monta (entity, entity_id) únicos para o IN composto
                $seen = [];
                $tuples = [];
                foreach ($rows as $r) {
                    $k = $r['entity'] . '#' . (int) $r['entity_id'];
                    if (!isset($seen[$k])) {
                        $seen[$k] = true;
                        $tuples[] = '(' . $pdo->quote((string) $r['entity']) . ',' . (int) $r['entity_id'] . ')';
                    }
                }
                if (!empty($tuples)) {
                    $stmtFd = $pdo->query("SELECT entity, entity_id, MAX(id) AS max_id FROM audit_logs WHERE action = 'force_delete' AND (entity, entity_id) IN (" . implode(',', $tuples) . ") GROUP BY entity, entity_id");
                    foreach (($stmtFd ? $stmtFd->fetchAll(PDO::FETCH_ASSOC) : []) as $fd) {
                        $forceDeletedAfter[$fd['entity'] . '#' . (int) $fd['entity_id']] = (int) $fd['max_id'];
                    }
                }
            } catch (\Throwable $e) {
                // Sem bloqueio por segurança: mantém comportamento anterior
            }
        }

        $events = [];
        foreach ($rows as $r) {
            $t = self::formatRelativeTime($r['created_at']);
            $action = $r['action'];
            $isSoftDeleted = !empty($softDeletedMap[$r['entity']][(int)$r['entity_id']]);
            $recordExists = !empty($existsMap[$r['entity']][(int)$r['entity_id']]);
            // Reversão impossível se o registro sumiu do disco depois (force delete posterior).
            $fdAfter = $forceDeletedAfter[$r['entity'] . '#' . (int) $r['entity_id']] ?? 0;
            $canRollback = (int) ($r['is_reversible'] ?? 0) === 1
                && $recordExists
                && !($fdAfter > (int) $r['id']);

            $events[] = [
                'id' => (int) $r['id'],
                'user_id' => $r['user_id'] ? (int)$r['user_id'] : null,
                'user_name' => $r['user_name'],
                'user_role' => $r['user_role'] ?? 'Usuário',
                'entity' => $r['entity'],
                'entity_label' => $r['record_label'] ?? ($r['context_module'] . ' #' . $r['entity_id']),
                'entity_id' => (int) $r['entity_id'],
                'action' => $action,
                'action_label' => $labelMap[$action] ?? ucfirst($action),
                'dot_color' => $colorMap[$action] ?? '#64748b',
                'context_module' => $r['context_module'],
                'reason' => $r['reason'],
                'diff_summary' => $r['diff_summary'],
                'diff_fields' => $r['diff_fields'] ? json_decode($r['diff_fields'], true) : null,
                'old_values' => $r['old_values'] ? json_decode($r['old_values'], true) : null,
                'new_values' => $r['new_values'] ? json_decode($r['new_values'], true) : null,
                'ip_address' => $r['ip_address'],
                'user_agent' => $r['user_agent'],
                'is_reversible' => (int) $r['is_reversible'],
                'is_soft_deleted' => $isSoftDeleted,
                'record_exists' => $recordExists,
                'force_deleted_after' => $fdAfter > (int) $r['id'] ? $fdAfter : null,
                'can_rollback' => $canRollback,
                'created_at_date' => $t['date'],
                'created_at_relative' => $t['relative'],
                'created_at_exact' => $t['exact']
            ];
        }

        return $events;
    }

    /**
     * Retorna métricas numéricas consolidadas para os cards do topo
     */
    public static function getStats(): array
    {
        $pdo = Database::getConnection();

        $totalCount = (int) $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
        $countToday = (int) $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $countCritical = (int) $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE action IN ('deactivate', 'soft_delete', 'force_delete')")->fetchColumn();
        $countRollbacks = (int) $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE action = 'rollback'")->fetchColumn();

        return [
            'total' => $totalCount,
            'today' => $countToday,
            'critical' => $countCritical,
            'rollbacks' => $countRollbacks
        ];
    }

    /**
     * Busca um registro de auditoria por ID (audit_logs é imutável e não possui soft delete)
     */
    public static function find(int $id, bool $withTrashed = true): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM audit_logs WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Colunas UNIQUE por entidade (para detectar conflito de reversão/restauração)
     */
    private static array $uniqueColumns = [
        'clients' => ['document', 'email'],
        'suppliers' => ['document', 'email'],
        'employees' => ['document', 'email'],
        'users' => ['username', 'email'],
        'roles' => ['name', 'slug'],
        'products' => [],
    ];

    /**
     * Executa a Reversão (Rollback) de um Log de Auditoria com proteção anti-quebra
     */
    public static function rollback(int $logId, string $reason): array
    {
        $pdo = Database::getConnection();
        $log = self::find($logId);

        if (!$log) {
            return ['success' => false, 'message' => 'Registro de auditoria não encontrado.'];
        }

        if (!(int)$log['is_reversible']) {
            return ['success' => false, 'message' => 'Esta operação não permite reversão (Exclusão Permanente).'];
        }

        $entity = $log['entity'];
        $entityId = (int) $log['entity_id'];
        $action = $log['action'];
        $modelClass = self::$entityModelMap[$entity] ?? null;

        if (!$modelClass || !class_exists($modelClass)) {
            return ['success' => false, 'message' => "Entidade '{$entity}' não suporta reversão automática."];
        }

        $oldValues = $log['old_values'] ? json_decode($log['old_values'], true) : null;
        $newValues = $log['new_values'] ? json_decode($log['new_values'], true) : null;

        $currentRecord = $modelClass::find($entityId, withTrashed: true);

        // 0. Registro sumiu do disco depois (exclusão permanente)? Reversão impossível.
        if ($currentRecord === null) {
            try {
                $stmtFd = $pdo->prepare("SELECT MAX(id) FROM audit_logs WHERE entity = :entity AND entity_id = :eid AND action = 'force_delete' AND id > :lid");
                $stmtFd->execute([':entity' => $entity, ':eid' => $entityId, ':lid' => $logId]);
                $fdId = (int) ($stmtFd->fetchColumn() ?? 0);
            } catch (\Throwable $e) {
                $fdId = 0;
            }
            if ($fdId > 0) {
                return [
                    'success' => false,
                    'message' => "Reversão impossível: o registro foi excluído permanentemente depois (log #{$fdId}). Não há o que restaurar."
                ];
            }
            return [
                'success' => false,
                'message' => 'Reversão impossível: o registro não existe mais no banco de dados.'
            ];
        }

        // 0b. Detecta reversão antiga: houve mudança posterior no mesmo registro?
        try {
            $stmtNewer = $pdo->prepare("SELECT id, action FROM audit_logs WHERE entity = :entity AND entity_id = :eid AND id > :lid ORDER BY id ASC LIMIT 5");
            $stmtNewer->execute([':entity' => $entity, ':eid' => $entityId, ':lid' => $logId]);
            $newerLogs = $stmtNewer->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            $newerLogs = [];
        }

        if (!empty($newerLogs) && $newValues !== null && $currentRecord !== null) {
            $drift = [];
            foreach ($newValues as $k => $v) {
                if ($k === 'id' || $k === 'updated_at' || $k === 'created_at') {
                    continue;
                }
                if (json_encode($currentRecord[$k] ?? null) !== json_encode($v)) {
                    $drift[] = self::$fieldLabels[$k] ?? $k;
                }
            }
            if (!empty($drift)) {
                $ids = implode(', #', array_column($newerLogs, 'id'));
                return [
                    'success' => false,
                    'message' => "Reversão bloqueada: registro foi alterado depois (logs #{$ids}). Campos divergentes: " . implode(', ', $drift) . ". Reverta primeiro os logs mais recentes."
                ];
            }
        }

        // 0c. Detecta conflito de unicidade ao reaplicar snapshot antigo
        $snapshot = ($action === 'update' || $action === 'rollback') ? $oldValues : null;
        if ($snapshot) {
            foreach (self::$uniqueColumns[$entity] ?? [] as $col) {
                $val = trim((string)($snapshot[$col] ?? ''));
                if ($val === '') {
                    continue;
                }
                try {
                    $stmtU = $pdo->prepare("SELECT id FROM `{$entity}` WHERE `{$col}` = :val AND id != :eid AND deleted_at IS NULL LIMIT 1");
                    $stmtU->execute([':val' => $val, ':eid' => $entityId]);
                    if ($stmtU->fetchColumn()) {
                        return ['success' => false, 'message' => "Reversão bloqueada: valor '{$val}' de '" . (self::$fieldLabels[$col] ?? $col) . "' já pertence a outro registro ativo."];
                    }
                } catch (\Throwable $e) {}
            }
        }

        try {
            // 1. Reversão de Cadastro -> Faz soft delete
            if ($action === 'create') {
                if ($currentRecord) {
                    $modelClass::delete($entityId);
                }
            }
            // 2. Reversão de Soft Delete ou Inativação -> Restaura para ativo
            elseif ($action === 'soft_delete' || $action === 'deactivate') {
                $modelClass::restore($entityId);
            }
            // 3. Reversão de Ativação -> Desativa
            elseif ($action === 'activate') {
                $modelClass::toggleActive($entityId);
            }
            // 4. Reversão de Edição ou Rollback anterior -> Reaplica o snapshot anterior
            elseif ($oldValues) {
                $modelClass::update($entityId, $oldValues);
            } else {
                return ['success' => false, 'message' => 'Log sem snapshot anterior para reversão.'];
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Reversão falhou (possível conflito de unicidade no banco): ' . $e->getMessage()];
        }

        $restoredRecord = $modelClass::find($entityId, withTrashed: true);

        // 5. Registra o novo evento de reversão no topo da timeline
        $newLogId = self::record(
            entity: $entity,
            entityId: $entityId,
            action: 'rollback',
            reason: $reason,
            oldValues: $currentRecord,
            newValues: $restoredRecord,
            recordLabel: $log['record_label'],
            contextModule: 'Auditoria'
        );

        // Marca a data de reversão no log de origem
        $stmt = $pdo->prepare("UPDATE audit_logs SET reverted_at = NOW(), reverted_by_log_id = :new_id WHERE id = :id");
        $stmt->execute([':new_id' => $newLogId, ':id' => $logId]);

        return [
            'success' => true,
            'message' => "Reversão da operação #{$logId} executada com sucesso! Log de reversão #{$newLogId} gerado."
        ];
    }

    /**
     * Verifica dependências relacionais ativas antes de permitir Hard Delete
     */
    public static function checkDependencies(string $entity, int $entityId): array
    {
        $pdo = Database::getConnection();
        $blockingReasons = [];
        $relationsCount = 0;

        $countWhere = function (string $table, string $where, array $params = []) use ($pdo): int {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE {$where}");
                $stmt->execute($params);
                return (int) $stmt->fetchColumn();
            } catch (\Throwable $e) {
                return 0;
            }
        };

        switch ($entity) {
            case 'clients':
                $n = $countWhere('client_addresses', 'client_id = :id AND deleted_at IS NULL', [':id' => $entityId]);
                if ($n > 0) {
                    $blockingReasons[] = "Possui {$n} endereço(s) vinculado(s) — apague em Dados & Cadastro antes.";
                    $relationsCount += $n;
                }
                $n = $countWhere('client_contacts', 'client_id = :id AND deleted_at IS NULL', [':id' => $entityId]);
                if ($n > 0) {
                    $blockingReasons[] = "Possui {$n} contato(s) vinculado(s) — apague em Dados & Cadastro antes.";
                    $relationsCount += $n;
                }
                $n = $countWhere('client_prices', 'client_id = :id AND deleted_at IS NULL', [':id' => $entityId]);
                if ($n > 0) {
                    $blockingReasons[] = "Possui {$n} preço(s) negociado(s) em Tabela de Preços — remova antes.";
                    $relationsCount += $n;
                }
                break;

            case 'suppliers':
                $n = $countWhere('supplier_contacts', 'supplier_id = :id AND deleted_at IS NULL', [':id' => $entityId]);
                if ($n > 0) {
                    $blockingReasons[] = "Possui {$n} contato(s) vinculado(s) — apague na ficha do fornecedor antes.";
                    $relationsCount += $n;
                }
                break;

            case 'employees':
                $n = $countWhere('users', 'employee_id = :id AND deleted_at IS NULL', [':id' => $entityId]);
                if ($n > 0) {
                    $blockingReasons[] = "Possui {$n} usuário(s) do sistema vinculado(s) — desvincule/exclua em Usuários antes.";
                    $relationsCount += $n;
                }
                break;

            case 'products':
                $n = $countWhere('client_prices', 'product_id = :id AND deleted_at IS NULL', [':id' => $entityId]);
                if ($n > 0) {
                    $blockingReasons[] = "Possui {$n} preço(s) negociado(s) com clientes — remova em Tabela de Preços antes.";
                    $relationsCount += $n;
                }
                try {
                    $pdo2 = Database::getConnection();
                    $stmtT = $pdo2->prepare("SELECT cylinder_type_id FROM products WHERE id = :id LIMIT 1");
                    $stmtT->execute([':id' => $entityId]);
                    if ($stmtT->fetchColumn()) {
                        $blockingReasons[] = "Possui tipo de cilindro vinculado (1:1) — desvincule em Cilindros antes.";
                        $relationsCount++;
                    }
                } catch (\Throwable $e) {}
                break;

            case 'cylinders':
                $n = $countWhere('products', 'cylinder_type_id = :id AND deleted_at IS NULL', [':id' => $entityId]);
                if ($n > 0) {
                    $blockingReasons[] = "Possui {$n} gás vinculado (1:1) — desvincule em Produtos antes.";
                    $relationsCount += $n;
                }
                break;

            case 'users':
                $currentUserId = $_SESSION['user_id'] ?? 0;
                if ($entityId === (int)$currentUserId) {
                    $blockingReasons[] = "Você não pode excluir permanentemente o próprio usuário com sessão ativa.";
                    $relationsCount++;
                }
                break;

            case 'roles':
                $n = $countWhere('users', 'role_id = :id AND deleted_at IS NULL', [':id' => $entityId]);
                if ($n > 0) {
                    $blockingReasons[] = "Possui {$n} usuário(s) vinculado(s) a este perfil — troque o perfil deles antes.";
                    $relationsCount += $n;
                }
                break;
        }

        return [
            'can_force_delete' => empty($blockingReasons),
            'blocking_reasons' => $blockingReasons,
            'relations_count' => $relationsCount
        ];
    }

    /**
     * Executa Exclusão Permanente Segura
     */
    public static function forceDeleteRecord(string $entity, int $entityId, string $reason): array
    {
        $check = self::checkDependencies($entity, $entityId);
        if (!$check['can_force_delete']) {
            return [
                'success' => false,
                'message' => 'Exclusão permanente bloqueada por integridade relacional: ' . implode(' ', $check['blocking_reasons'])
            ];
        }

        $modelClass = self::$entityModelMap[$entity] ?? null;
        if (!$modelClass || !class_exists($modelClass)) {
            return ['success' => false, 'message' => "Entidade '{$entity}' inválida."];
        }

        $oldRecord = $modelClass::find($entityId, withTrashed: true);
        if (!$oldRecord) {
            return ['success' => false, 'message' => 'Registro não encontrado para exclusão física.'];
        }

        // Executa exclusão física no banco
        $modelClass::forceDelete($entityId);

        // Registra o Hard Delete na auditoria
        self::record(
            entity: $entity,
            entityId: $entityId,
            action: 'force_delete',
            reason: $reason,
            oldValues: $oldRecord,
            newValues: null,
            recordLabel: $oldRecord['name'] ?? ($entity . ' #' . $entityId),
            contextModule: self::$moduleLabels[$entity] ?? ucfirst($entity)
        );

        return [
            'success' => true,
            'message' => 'Registro excluído permanentemente do banco de dados com registro forense de auditoria.'
        ];
    }
}
