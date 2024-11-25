<?php
$host = 'localhost';
$dbname = 'fakerDB';
$username = 'faker';
$password = 'F4k3r_1s_H4rd_T0_Gu3ss';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    $conn->set_charset("utf8");
    
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        throw new Exception("데이터베이스 연결 실패");
    }
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    die("데이터베이스 연결 오류가 발생했습니다.");
}
?>
