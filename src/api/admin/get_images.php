<?php
// 모든 출력 버퍼링 시작
ob_start();

// 헤더 설정
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 에러 설정
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 세션 시작
session_start();

require_once(__DIR__ . '/../auth/check_session.php');

if (!isValidAdminSession()) {
    error_log("관리자 세션 검증 실패");
    http_response_code(401);
    ob_end_clean();
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
}

// 기존의 모든 출력 버퍼 제거
while (ob_get_level()) {
    ob_end_clean();
}

// JSON 인코딩 시 에러 체크
$jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE);
if ($jsonResponse === false) {
    error_log("JSON 인코딩 에러: " . json_last_error_msg());
    $jsonResponse = json_encode([
        'success' => false,
        'message' => 'JSON 인코딩 에러: ' . json_last_error_msg()
    ]);
}

// 응답 길이 설정
header('Content-Length: ' . strlen($jsonResponse));

// JSON 응답 전송
echo $jsonResponse;
exit;