<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();

require_once(__DIR__ . '/../auth/check_session.php');  // 경로 수정

if (!isValidAdminSession()) {
    error_log("관리자 세션 검증 실패");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$imageDir = __DIR__ . '/../../image/share/';
$response = ['success' => false, 'images' => [], 'message' => ''];

try {
    error_log("이미지 디렉토리 경로: " . $imageDir);
    
    if (!is_dir($imageDir)) {
        if (!mkdir($imageDir, 0777, true)) {
            throw new Exception('이미지 디렉토리 생성 실패');
        }
    }

    $files = scandir($imageDir);
    if ($files === false) {
        throw new Exception('디렉토리 스캔 실패');
    }
    
    $images = [];
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $fileInfo = pathinfo($file);
            $extension = strtolower($fileInfo['extension'] ?? '');
            
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $images[] = [
                    'name' => $file,
                    'url' => '../image/share/' . $file
                ];
            }
        }
    }
    
    $response['success'] = true;
    $response['images'] = $images;
    
} catch (Exception $e) {
    error_log("이미지 목록 조회 에러: " . $e->getMessage());
    $response['message'] = '이미지 목록을 불러오는데 실패했습니다: ' . $e->getMessage();
} finally {
    ob_clean();  // 출력 버퍼 정리
    echo json_encode($response);
    exit;
} 