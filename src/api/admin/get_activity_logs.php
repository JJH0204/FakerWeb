<?php
require_once '../db_connect.php';
session_start();

// 세션 체크
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// 페이지네이션 파라미터
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page_size = 10;
$offset = ($page - 1) * $page_size;

// 필터 파라미터
$activity_type = isset($_GET['activity_type']) ? $_GET['activity_type'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

try {
    // 쿼리 생성
    $where_clauses = [];
    $params = [];
    $types = '';
    
    if ($activity_type !== 'all') {
        $where_clauses[] = 'activity_type = ?';
        $params[] = $activity_type;
        $types .= 's';
    }
    
    if ($status !== 'all') {
        $where_clauses[] = 'status = ?';
        $params[] = $status;
        $types .= 's';
    }
    
    $where_sql = '';
    if (!empty($where_clauses)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
    }
    
    // 로그 조회 쿼리
    $query = "
        SELECT * FROM activity_logs
        $where_sql
        ORDER BY timestamp DESC
        LIMIT ? OFFSET ?
    ";
    
    // 전체 개수 조회 쿼리
    $count_query = "
        SELECT COUNT(*) as total
        FROM activity_logs
        $where_sql
    ";
    
    // 스테이트먼트 준비
    $stmt = $conn->prepare($query);
    $count_stmt = $conn->prepare($count_query);
    
    // 파라미터 바인딩
    $params[] = $page_size;
    $params[] = $offset;
    $types .= 'ii';
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
        $count_stmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
    }
    
    // 쿼리 실행
    $stmt->execute();
    $result = $stmt->get_result();
    
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
    
    // 결과 가공
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = [
            'timestamp' => $row['timestamp'],
            'activity_type' => $row['activity_type'],
            'ip_address' => $row['ip_address'],
            'status' => $row['status'],
            'details' => $row['details']
        ];
    }
    
    echo json_encode([
        'logs' => $logs,
        'total' => $total,
        'has_more' => ($offset + $page_size) < $total
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
