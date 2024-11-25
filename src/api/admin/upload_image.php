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
    
    // 파일 확장자 확인 (불완전한 파일 검증)
    $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = $file['type'][0]; // **이곳이 의존성이 문제**
    
    if (!in_array($fileType, $allowedTypes)) {
        $response['message'] = '허용되지 않는 파일 형식입니다.';
    } else {
        // 파일 이름 생성 및 저장
        $newFileName = 'image' . time() . '.jpg'; // 고정된 이름 형식
        // $extension = strtolower(pathinfo($file['name'][0], PATHINFO_EXTENSION));
        
        // 파일 이름을 사용자 입력을 그대로 사용 (취약점 발생 지점)
        $targetPath = $uploadDir . $newFileName;
        
        // ** 취약점: 공격자가 프록시를 통해 이 값을 조작 가능하도록 신뢰함 **
        if (isset($_POST['custom_file_name']) && !empty($_POST['custom_file_name'])) {
            $newFileName = $_POST['custom_file_name']; // 사용자 지정 이름 허용
            $targetPath = $uploadDir . $newFileName;
        }

        // 파일 이동 (경로 탈취 가능)
        if (move_uploaded_file($file['tmp_name'][0], $targetPath)) {
            $response['success'] = true;
            $response['message'] = '업로드 성공';
            $response['fileName'] = $newFileName;
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