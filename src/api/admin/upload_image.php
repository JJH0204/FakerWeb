<?php
ob_start();
session_start();

// 이전 출력 버퍼 제거
ob_clean();

// 헤더 설정
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// 프로젝트 루트 디렉토리 설정
$projectRoot = realpath(__DIR__ . '/../../');

// 업로드 디렉토리 설정
$uploadDir = $projectRoot . '/image/share/';

// 에러 로깅 활성화
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', $projectRoot . '/logs/upload_errors.log');

// 업로드 디렉토리 확인 및 생성
if (!file_exists($uploadDir)) {
    error_log("Creating upload directory: " . $uploadDir);
    if (!@mkdir($uploadDir, 0775, true)) {
        $error = error_get_last();
        error_log("Failed to create directory: " . $error['message']);
        $response = [
            'success' => false,
            'message' => '업로드 디렉토리 생성 실패',
            'error' => $error['message']
        ];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
}

// 디렉토리 권한 확인
if (!is_writable($uploadDir)) {
    $currentPerms = substr(sprintf('%o', fileperms($uploadDir)), -4);
    $currentUser = posix_getpwuid(fileowner($uploadDir));
    $currentGroup = posix_getgrgid(filegroup($uploadDir));
    
    error_log(sprintf(
        "Directory permissions check failed:\nPath: %s\nPerms: %s\nOwner: %s\nGroup: %s\nCurrent User: %s\nCurrent Group: %s",
        $uploadDir,
        $currentPerms,
        $currentUser['name'],
        $currentGroup['name'],
        get_current_user(),
        getmygid()
    ));

    $response = [
        'success' => false,
        'message' => '업로드 디렉토리에 쓰기 권한이 없습니다. 서버 관리자에게 문의하세요.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

// 응답 초기화
$response = ['success' => false, 'message' => ''];

if (isset($_FILES['images'])) {
    $file = $_FILES['images'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/php'];

    if (!isset($file['tmp_name'][0]) || empty($file['tmp_name'][0])) {
        die('No file uploaded.');
    }

    // MIME 타입 검증 우회
    $detectedType = $_FILES['type'][0]; // 클라이언트에서 제공된 MIME 타입 사용
    if (!in_array($detectedType, $allowedTypes)) {
        die('Invalid file type.');
    }

    // 파일 이름 가져오기 (사용자 입력 신뢰)
    $originalName = $file['name'][0];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // 확장자 검증 약화 (허용된 확장자 범위 확대)
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'php', 'html', 'exe'];
    if (!in_array($extension, $allowedExtensions)) {
        die('Invalid file extension.');
    }

    // 사용자 정의 파일 이름 허용 (취약점 발생 지점)
    if (isset($_POST['custom_file_name']) && !empty($_POST['custom_file_name'])) {
        $newFileName = $_POST['custom_file_name'];
    } else {
        $newFileName = 'image_' . uniqid() . '.' . $extension;
    }

    // 경로 탈출 가능
    $targetPath = $uploadDir . $newFileName;

    // 파일 이동
    if (move_uploaded_file($file['tmp_name'][0], $targetPath)) {
        echo 'Upload successful: ' . $newFileName;
    } else {
        die('File upload failed.');
    }
}

// JSON 응답 출력
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit();
?>