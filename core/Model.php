<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = false;
    protected static string $tenantColumn = 'shop_id';
    protected static array $fillable = [];

    public static function find(int|string $id): ?array
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id";
        $params = ['id' => $id];
        static::applyTenantScope($sql, $params);
        return $db->fetch($sql, $params);
    }

    public static function findAll(array $conditions = [], string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table;
        $params = [];

        $where = static::buildWhere($conditions, $params);
        if ($where) {
            $sql .= " WHERE {$where}";
        }

        static::applyTenantScope($sql, $params, 'AND');

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
            if ($offset > 0) {
                $sql .= " OFFSET {$offset}";
            }
        }

        return $db->fetchAll($sql, $params);
    }

    public static function findWhere(string $column, mixed $value): ?array
    {
        return static::findAll([$column => $value], '', 1)[0] ?? null;
    }

    public static function count(array $conditions = []): int
    {
        $db = Database::getInstance();
        $params = [];

        $sql = "SELECT COUNT(*) as count FROM " . static::$table;
        $where = static::buildWhere($conditions, $params);
        if ($where) {
            $sql .= " WHERE {$where}";
        }

        static::applyTenantScope($sql, $params, $where ? 'AND' : 'WHERE');

        $result = $db->fetch($sql, $params);
        return (int) ($result['count'] ?? 0);
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();

        $filtered = static::filterFillable($data);

        if (static::$tenantAware && !isset($filtered[static::$tenantColumn])) {
            $shopId = AuthContext::shopId();
            if ($shopId) {
                $filtered[static::$tenantColumn] = $shopId;
            }
        }

        return $db->insert(static::$table, $filtered);
    }

    public static function update(int|string $id, array $data): int
    {
        $db = Database::getInstance();
        $filtered = static::filterFillable($data);

        $sets = [];
        $params = [];
        foreach ($filtered as $column => $value) {
            $key = 'set_' . $column;
            $sets[] = "{$column} = :{$key}";
            $params[$key] = $value;
        }

        if (empty($sets)) {
            return 0;
        }

        $setClause = implode(', ', $sets);
        $sql = "UPDATE " . static::$table . " SET {$setClause} WHERE " . static::$primaryKey . " = :where_id";
        $params['where_id'] = $id;

        static::applyTenantScope($sql, $params, 'AND');

        return $db->query($sql, $params)->rowCount();
    }

    public static function delete(int|string $id): int
    {
        $db = Database::getInstance();
        $params = ['id' => $id];
        $sql = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id";
        static::applyTenantScope($sql, $params, 'AND');
        return $db->query($sql, $params)->rowCount();
    }

    public static function paginate(int $page = 1, int $perPage = 25, array $conditions = [], string $orderBy = ''): array
    {
        $total = static::count($conditions);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $items = static::findAll($conditions, $orderBy, $perPage, $offset);

        return [
            'items' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
        ];
    }

    public static function query(string $sql, array $params = []): array
    {
        $db = Database::getInstance();
        static::applyTenantScopeRaw($sql, $params);

        if (stripos(trim($sql), 'SELECT') === 0) {
            return $db->fetchAll($sql, $params);
        }

        return [];
    }

    private static function isValidColumn(string $name): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name) === 1;
    }

    private static function buildWhere(array $conditions, array &$params): string
    {
        if (empty($conditions)) {
            return '';
        }

        $clauses = [];
        foreach ($conditions as $column => $value) {
            if (!static::isValidColumn($column)) {
                continue;
            }

            if (is_array($value)) {
                $placeholders = [];
                foreach ($value as $i => $v) {
                    $key = "{$column}_{$i}";
                    $placeholders[] = ":{$key}";
                    $params[$key] = $v;
                }
                $clauses[] = "{$column} IN (" . implode(', ', $placeholders) . ")";
            } else {
                $clauses[] = "{$column} = :where_{$column}";
                $params["where_{$column}"] = $value;
            }
        }

        return implode(' AND ', $clauses);
    }

    private static function applyTenantScope(string &$sql, array &$params, string $operator = 'WHERE'): void
    {
        if (!static::$tenantAware) {
            return;
        }

        $shopId = AuthContext::shopId();
        if (!$shopId) {
            return;
        }

        $hasWhere = stripos(trim($sql), 'WHERE') !== false;
        $clause = $hasWhere ? " AND " : " {$operator} ";
        $sql .= "{$clause}" . static::$tenantColumn . " = :tenant_shop_id";
        $params['tenant_shop_id'] = $shopId;
    }

    private static function applyTenantScopeRaw(string &$sql, array &$params): void
    {
        if (!static::$tenantAware) {
            return;
        }

        $shopId = AuthContext::shopId();
        if (!$shopId) {
            return;
        }

        $params['tenant_shop_id'] = $shopId;
    }

    private static function filterFillable(array $data): array
    {
        if (empty(static::$fillable)) {
            return $data;
        }

        $filtered = [];
        foreach (static::$fillable as $field) {
            if (array_key_exists($field, $data)) {
                $filtered[$field] = $data[$field];
            }
        }
        return $filtered;
    }

    public static function getTable(): string
    {
        return static::$table;
    }

    public static function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }
}
