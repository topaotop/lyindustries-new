<?php
declare(strict_types=1);

/**
 * Thin sqlsrv wrapper. The connection comes from connectgrp.php (picks LYI / test_LYI by host)
 * and is opened lazily, only when a query actually runs.
 * SQL Server 2012 / compatibility level 100: no JSON functions, no OFFSET…FETCH.
 */

/** @return resource sqlsrv connection */
function db()
{
    static $conn = null;
    if ($conn !== null) {
        return $conn;
    }
    $file = APP_ROOT . '/connectgrp.php';
    if (!is_file($file)) {
        throw new RuntimeException('connectgrp.php is missing');
    }
    if (!function_exists('sqlsrv_connect')) {
        throw new RuntimeException('PHP extension sqlsrv is not loaded');
    }
    // connectgrp.php defines $conn in the including scope and throws when it cannot connect.
    $conn = (static function (string $file) {
        require $file;
        return $conn;
    })($file);

    return $conn;
}

/**
 * Run a parameterised SELECT and return all rows as associative arrays.
 *
 * @param list<mixed> $params
 * @return list<array<string, mixed>>
 */
function db_rows(string $sql, array $params = []): array
{
    $stmt = sqlsrv_query(db(), $sql, $params);
    if ($stmt === false) {
        throw new RuntimeException('Query failed: ' . print_r(sqlsrv_errors(), true));
    }
    $rows = [];
    while (($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) !== null) {
        if ($row === false) {
            throw new RuntimeException('Fetch failed: ' . print_r(sqlsrv_errors(), true));
        }
        $rows[] = $row;
    }
    sqlsrv_free_stmt($stmt);

    return $rows;
}
