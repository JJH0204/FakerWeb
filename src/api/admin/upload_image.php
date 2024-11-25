<?php
ob_start();
session_start();

// 이전 출력 버퍼 제거
ob_clean();

// 헤더 설정
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// 업로드 디렉토리 설정
$uploadDir = '../../image/share/';

// 응답 초기화
$response = ['success' => false, 'message' => ''];

// 파일 업로드 처리
if (isset($_FILES['images'])) {
    $file = $_FILES['images'];
    
    // 파일 확장자 확인
    $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'];
    $fileType = $file['type'][0];
    
    if (!in_array($fileType, $allowedTypes)) {
        $response['message'] = '허용되지 않는 파일 형식입니다.';
    } else {
        // 파일 이름 생성 및 저장
        $fileName = $file['name'][0];
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'][0], $targetPath)) {
            $response['success'] = true;
            $response['message'] = '업로드 성공';
            $response['fileName'] = $fileName;
        } else {
            $response['message'] = '파일 업로드 실패';
        }
    }
} else {
    $response['message'] = '업로드된 파일이 없습니다.';
}

// JSON 응답 출력
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit();
?> 