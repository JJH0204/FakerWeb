<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();

require_once('../login/check_session.php');

if (!isValidAdminSession()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once('../db_connect.php');

try {
    $conn = connectDB();
    
    // 페이지네이션 파라미터
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $size = isset($_GET['size']) ? (int)$_GET['size'] : 10;
    $offset = ($page - 1) * $size;

    // 필터 파라미터
    $activity_type = isset($_GET['activity_type']) ? $_GET['activity_type'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';

    // 기본 쿼리
    $query = "SELECT * FROM activity_logs";
    $countQuery = "SELECT COUNT(*) as total FROM activity_logs";
    $params = [];
    $whereConditions = [];

    // 필터 조건 추가
    if ($activity_type) {
        $whereConditions[] = "activity_type = ?";
        $params[] = $activity_type;
    }
    if ($status) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }

    // WHERE 절 구성
    if (!empty($whereConditions)) {
        $whereClause = " WHERE " . implode(" AND ", $whereConditions);
        $query .= $whereClause;
        $countQuery .= $whereClause;
    }

    // 정렬 및 페이지네이션
    $query .= " ORDER BY timestamp DESC LIMIT ? OFFSET ?";
    $params[] = $size;
    $params[] = $offset;

    // 전체 레코드 수 조회
    $countStmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        $types = str_repeat("s", count($params) - 2); // size와 offset 제외
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $totalResult = $countStmt->get_result()->fetch_assoc();
    $total = $totalResult['total'];

    // 로그 데이터 조회
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $types = str_repeat("s", count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = [
            'timestamp' => $row['timestamp'],
            'activity_type' => $row['activity_type'],
            'ip_address' => $row['ip_address'],
            'status' => $row['status'],
            'user_id' => $row['user_id'],
            'details' => $row['details']
        ];
    }

    // 응답 데이터 구성
    $totalPages = ceil($total / $size);
    $hasMore = $page < $totalPages;

    echo json_encode([
        'success' => true,
        'logs' => $logs,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'hasMore' => $hasMore,
        'totalRecords' => $total
    ]);

} catch (Exception $e) {
    error_log("Activity logs error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load activity logs'
    ]);
} finally {
    if (isset($conn)) {
        $conn = null;
    }
}
?>
