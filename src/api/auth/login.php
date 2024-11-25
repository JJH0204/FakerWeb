<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("Login process started");

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_connect.php';  // 절대 경로로 수정

try {
    if (!isset($conn)) {
        throw new Exception("데이터베이스 연결이 설정되지 않았습니다.");
    }

    // POST 데이터 받기
    $input = file_get_contents('php://input');
    error_log("Received raw input: " . $input);
    
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('잘못된 JSON 형식: ' . json_last_error_msg());
    }
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    
    error_log("Attempting login for user: " . $username);

    if (empty($username) || empty($password)) {
        throw new Exception('아이디와 비밀번호를 모두 입력해주세요.');
    }

    $stmt = $conn->prepare("SELECT * FROM USER_info WHERE ID = ?");
    if (!$stmt) {
        throw new Exception("쿼리 준비 실패: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        throw new Exception("쿼리 실행 실패: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("결과 가져오기 실패: " . $stmt->error);
    }

    $user = $result->fetch_assoc();
    error_log("User data retrieved: " . ($user ? "true" : "false"));
    
    if ($user && $password === $user['PASSWORD'] && $user['ACCESS'] == 1) {
        error_log("Login successful for user: " . $username);
        $_SESSION['admin'] = true;
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['username'] = $user['ID'];
        $_SESSION['user_id'] = $user['ID'];
        
        echo json_encode([
            'success' => true,
            'message' => '로그인 성공'
        ]);
    } else {
        error_log("Login failed for user: " . $username);
        echo json_encode([
            'success' => false,
            'message' => '아이디 또는 비밀번호가 올바르지 않습니다.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
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