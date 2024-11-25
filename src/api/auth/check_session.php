<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("세션 체크 시작");

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_connect.php';

function isValidAdminSession() {
    error_log("세션 검증 함수 시작");
    error_log("세션 상태: " . print_r($_SESSION, true));
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin_logged_in'])) {
        error_log("필수 세션 변수 없음");
        return false;
    }

    try {
        global $conn;
        
        if (!$conn) {
            error_log("데이터베이스 연결 실패");
            return false;
        }

        $query = "SELECT ACCESS FROM USER_info WHERE ID = ? AND ACCESS = 1 LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("쿼리 준비 실패: " . $conn->error);
            return false;
        }

        $stmt->bind_param("s", $_SESSION['user_id']);
        if (!$stmt->execute()) {
            error_log("쿼리 실행 실패: " . $stmt->error);
            return false;
        }

        $result = $stmt->get_result();
        if (!$result) {
            error_log("결과 가져오기 실패: " . $stmt->error);
            return false;
        }

        $row = $result->fetch_assoc();
        $stmt->close();
        
        error_log("쿼리 결과: " . print_r($row, true));
        return $row ? true : false;

    } catch (Exception $e) {
        error_log("세션 검증 중 예외 발생: " . $e->getMessage());
        return false;
    }
}

try {
    error_log("세션 체크 엔드포인트 시작");
    $isValid = isValidAdminSession();
    error_log("세션 검증 결과: " . ($isValid ? "유효" : "무효"));
    
    if ($isValid) {
        echo json_encode([
            'success' => true,
            'isAdmin' => true,
            'message' => 'Valid session'
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'isAdmin' => false,
            'message' => 'Invalid session'
        ]);
        
        session_destroy();
    }
} catch (Exception $e) {
    error_log("세션 체크 엔드포인트 에러: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'isAdmin' => false,
        'message' => 'Server error during session check'
    ]);
}
?>
