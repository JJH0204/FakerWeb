<?php
header('Content-Type: application/json');

// 에러 로깅 활성화
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once '/db_connect.php';

try {
    session_start();
    
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        
        // 활동 로그 기록
        $ip = $_SERVER['REMOTE_ADDR'];
        $log_stmt = $conn->prepare(
            "INSERT INTO activity_logs (activity_type, ip_address, status, user_id, details) 
             VALUES (?, ?, ?, ?, ?)"
        );
        if (!$log_stmt) {
            error_log("Log prepare failed: " . $conn->error);
        } else {
            $activity_type = 'LOGOUT';
            $status = 'SUCCESS';
            $details = '관리자 로그아웃';
            $log_stmt->bind_param("sssss", $activity_type, $ip, $status, $username, $details);
            if (!$log_stmt->execute()) {
                error_log("Log execute failed: " . $log_stmt->error);
            }
        }
        
        // 세션 삭제
        session_unset();
        session_destroy();
        
        echo json_encode([
            'success' => true,
            'message' => '로그아웃 성공'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '로그인된 세션이 없습니다.'
        ]);
    }
} catch (Exception $e) {
    error_log("Logout Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '서버 오류가 발생했습니다: ' . $e->getMessage()
    ]);
}

// 스테이트먼트 정리
if (isset($log_stmt)) {
    $log_stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?>
