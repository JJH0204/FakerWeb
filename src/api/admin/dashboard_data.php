<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();

require_once(__DIR__ . '/../auth/check_session.php');

if (!isValidAdminSession()) {
    error_log("관리자 세션 검증 실패");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    require_once(__DIR__ . '/../auth/db_connect.php');
    
    error_log("대시보드 데이터 조회 시작");
    
    // 활성 사용자 수 조회 (최근 24시간 내 로그인)
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT user_id) as active_users 
        FROM activity_logs 
        WHERE activity_type = 'LOGIN' 
        AND timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    
    if (!$stmt->execute()) {
        throw new Exception("활성 사용자 조회 실패: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $activeUsers = $result->fetch_assoc()['active_users'];
    $stmt->close();
    
    // 오늘의 총 방문자 수
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT user_id) as today_visitors 
        FROM activity_logs 
        WHERE DATE(timestamp) = CURDATE()
    ");
    
    if (!$stmt->execute()) {
        throw new Exception("오늘의 방문자 조회 실패: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $todayVisitors = $result->fetch_assoc()['today_visitors'];
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'active_users' => (int)$activeUsers,
            'today_visitors' => (int)$todayVisitors
        ]
    ]);

} catch (Exception $e) {
    error_log("대시보드 데이터 조회 에러: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '서버 오류가 발생했습니다: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>
