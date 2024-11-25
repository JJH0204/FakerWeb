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

    // 허용된 MIME 타입 정의
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

    // 업로드된 파일의 실제 MIME 타입 확인
    if (!isset($file['tmp_name'][0]) || empty($file['tmp_name'][0])) {
        $response['message'] = '업로드된 파일이 없습니다.';
    } else {
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = @finfo_file($fileInfo, $file['tmp_name'][0]); // @ 연산자로 경고 억제
        finfo_close($fileInfo);

        if ($detectedType === false) {
            $response['message'] = '파일 형식을 확인할 수 없습니다.';
        } else if (!in_array($detectedType, $allowedTypes)) {
            $response['message'] = '허용되지 않는 파일 형식입니다.';
        } else {
            // 파일 확장자 가져오기
            $extension = strtolower(pathinfo($file['name'][0], PATHINFO_EXTENSION));
            
            // 허용된 확장자 확인
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $allowedExtensions)) {
                $response['message'] = '허용되지 않는 파일 확장자입니다.';
            } else {
                // 파일 이름 생성
                $newFileName = 'image' . time() . '.' . $extension;

                // 사용자 지정 파일 이름 허용 (취약점 포함)
                if (isset($_POST['custom_file_name']) && !empty($_POST['custom_file_name'])) {
                    $newFileName = $_POST['custom_file_name']; // 사용자 지정 이름
                }

                $targetPath = $uploadDir . $newFileName;

                // 파일 이동
                if (move_uploaded_file($file['tmp_name'][0], $targetPath)) {
                    $response['success'] = true;
                    $response['message'] = '업로드 성공';
                    $response['fileName'] = $newFileName;
                } else {
                    $response['message'] = '파일 업로드 실패';
                }
            }
        }
    }

} else {
    $response['message'] = '업로드된 파일이 없습니다.';
}


// JSON 응답 출력
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit();
?>