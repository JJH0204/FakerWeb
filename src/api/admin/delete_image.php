<?php
// 세션 시작 전에 출력 버퍼 초기화
ob_start();
session_start();
require_once '../auth/check_session.php';

// 응답 헤더 설정
header('Content-Type: application/json; charset=utf-8');

// 이전 출력 버퍼 제거
ob_clean();

// 응답 초기화
$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['imageName']) || empty($data['imageName'])) {
        throw new Exception('이미지 이름이 제공되지 않았습니다.');
    }

    $imageName = basename($data['imageName']); // 경로 주입 방지
    $uploadDir = '../../image/share/';
    $imagePath = $uploadDir . $imageName;

    // 허용된 이미지 확장자 검사
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception('허용되지 않는 파일 형식입니다.');
    }

    // 파일 경로 검증
    $realUploadDir = realpath($uploadDir);
    $realImagePath = realpath($imagePath);
    
    if ($realImagePath === false || strpos($realImagePath, $realUploadDir) !== 0) {
        throw new Exception('잘못된 파일 경로입니다.');
    }

    if (!file_exists($imagePath)) {
        throw new Exception('파일을 찾을 수 없습니다.');
    }

    if (!is_writable($imagePath)) {
        throw new Exception('파일 삭제 권한이 없습니다.');
    }

    if (unlink($imagePath)) {
        $response['success'] = true;
        $response['message'] = '이미지가 성공적으로 삭제되었습니다.';
    } else {
        throw new Exception('파일 삭제 중 오류가 발생했습니다.');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('이미지 삭제 오류: ' . $e->getMessage());
}

// JSON 응답 전에 마지막으로 출력 버퍼 정리
ob_clean();

// JSON 응답 출력
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit();