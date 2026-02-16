<?php
require_once __DIR__ . '/../config/database.php';

function dbQuery($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        // Log error to file for debugging (not shown to user)
        error_log("Database error: " . $e->getMessage() . " | SQL: " . $sql);
        // Return a proper error response
        if (function_exists('errorResponse')) {
            errorResponse('Database error occurred', 500);
        }
        throw $e;
    }
}

function dbFetchAll($sql, $params = []) {
    return dbQuery($sql, $params)->fetchAll();
}

function dbFetchOne($sql, $params = []) {
    return dbQuery($sql, $params)->fetch();
}

function dbInsert($table, $data) {
    $pdo = getDBConnection();
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
    return $pdo->lastInsertId();
}

function dbUpdate($table, $data, $where, $whereParams = []) {
    $pdo = getDBConnection();
    $setParts = [];
    $values = [];
    foreach ($data as $key => $value) {
        $setParts[] = "{$key} = ?";
        $values[] = $value;
    }
    $setClause = implode(', ', $setParts);
    $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
    $values = array_merge($values, $whereParams);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
    return $stmt->rowCount();
}

function dbDelete($table, $where, $params = []) {
    $pdo = getDBConnection();
    $sql = "DELETE FROM {$table} WHERE {$where}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

function dbCount($table, $where = '1=1', $params = []) {
    $result = dbFetchOne("SELECT COUNT(*) as cnt FROM {$table} WHERE {$where}", $params);
    return (int)$result['cnt'];
}

function logActivity($userId, $action, $entityType, $entityId = null, $details = null) {
    dbInsert('activity_log', [
        'user_id' => $userId,
        'action' => $action,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'details' => $details ? json_encode($details) : null
    ]);
}
