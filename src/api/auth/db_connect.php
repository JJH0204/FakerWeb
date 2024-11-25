<?php
$host = 'fakerdb';     // Docker 서비스 이름을 호스트명으로 사용
$dbname = 'fakerDB';   // SQL 파일의 데이터베이스 이름과 일치
$username = 'faker';   // docker-compose.yml의 MYSQL_USER와 일치
$password = 'F4k3r_1s_H4rd_T0_Gu3ss';  // docker-compose.yml의 MYSQL_PASSWORD와 일치

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        throw new Exception("데이터베이스 연결 실패: " . $conn->connect_error);
    }
    
    if (!$conn->set_charset("utf8")) {
        throw new Exception("문자셋 설정 실패: " . $conn->error);
    }
    
    error_log("Database connected successfully");
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '데이터베이스 연결 오류가 발생했습니다.'
    ]);
    exit;
}
?>
