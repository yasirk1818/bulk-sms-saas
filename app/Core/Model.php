<?php
namespace App\Core;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';

    public function find(int $id): ?array
    {
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }

    public function findBy(string $column, mixed $value): ?array
    {
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE {$column} = ?",
            [$value]
        );
    }

    public function all(string $orderBy = 'id DESC', int $limit = 100): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} LIMIT ?",
            [$limit]
        );
    }

    public function where(string $conditions, array $params = [], string $orderBy = 'id DESC'): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE {$conditions} ORDER BY {$orderBy}",
            $params
        );
    }

    public function create(array $data): int
    {
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return Database::insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return Database::update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
    }

    public function deleteRecord(int $id): int
    {
        return Database::delete($this->table, "{$this->primaryKey} = ?", [$id]);
    }

    public function count(string $where = '1=1', array $params = []): int
    {
        return Database::count($this->table, $where, $params);
    }

    public function paginate(int $page = 1, int $perPage = 20, string $where = '1=1', array $params = [], string $orderBy = 'id DESC'): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy}";
        return Database::paginate($sql, $params, $page, $perPage);
    }

    public function exists(string $column, mixed $value, ?int $exceptId = null): bool
    {
        $sql = "SELECT COUNT(*) as cnt FROM {$this->table} WHERE {$column} = ?";
        $params = [$value];
        if ($exceptId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $exceptId;
        }
        $result = Database::fetch($sql, $params);
        return ($result['cnt'] ?? 0) > 0;
    }
}
