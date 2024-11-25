<?php
ob_start();
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
    
    // stats_cache 테이블에서 통계 데이터 조회
    $query = "SELECT stat_name, stat_value FROM stats_cache 
              WHERE stat_name IN ('total_visitors', 'today_visitors', 'active_users')";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("통계 데이터 조회 실패: " . $conn->error);
    }
    
    $stats = [
        'total_visitors' => 0,
        'today_visitors' => 0,
        'active_users' => 0
    ];
    
    while ($row = $result->fetch_assoc()) {
        $stats[$row['stat_name']] = (int)$row['stat_value'];
    }
    
    // 방문자 차트 데이터 (최근 24시간)
    $chart_query = "
        SELECT 
            DATE_FORMAT(visit_time, '%Y-%m-%d %H:00:00') as hour,
            COUNT(DISTINCT ip_address) as visitor_count
        FROM visitor_logs
        WHERE visit_time >= NOW() - INTERVAL 24 HOUR
        GROUP BY hour
        ORDER BY hour ASC
    ";
    
    $chart_result = $conn->query($chart_query);
    
    if (!$chart_result) {
        throw new Exception("차트 데이터 조회 실패: " . $conn->error);
    }
    
    $chart_data = [
        'labels' => [],
        'values' => []
    ];
    
    while ($row = $chart_result->fetch_assoc()) {
        $chart_data['labels'][] = date('H:i', strtotime($row['hour']));
        $chart_data['values'][] = (int)$row['visitor_count'];
    }
    
    echo json_encode([
        'success' => true,
        'totalVisitors' => $stats['total_visitors'],
        'todayVisitors' => $stats['today_visitors'],
        'activeUsers' => $stats['active_users'],
        'visitorData' => $chart_data
    ]);

} catch (Exception $e) {
    error_log("대시보드 데이터 조회 에러: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '서버 오류가 발생했습니다: ' . $e->getMessage()
    ]);
} finally {
    if (isset($result)) {
        $result->close();
    }
    if (isset($chart_result)) {
        $chart_result->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
