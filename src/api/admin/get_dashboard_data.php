<?php
require_once '../db_connect.php';
session_start();

// 세션 체크
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // 통계 데이터 가져오기
    $stats_query = "SELECT stat_name, stat_value FROM stats_cache";
    $stats_result = $conn->query($stats_query);
    
    $stats = [];
    while ($row = $stats_result->fetch_assoc()) {
        $stats[$row['stat_name']] = $row['stat_value'];
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
    
    $chart_data = [
        'labels' => [],
        'values' => []
    ];
    
    while ($row = $chart_result->fetch_assoc()) {
        $chart_data['labels'][] = date('H:i', strtotime($row['hour']));
        $chart_data['values'][] = (int)$row['visitor_count'];
    }
    
    echo json_encode([
        'stats' => $stats,
        'visitor_chart_data' => $chart_data
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
