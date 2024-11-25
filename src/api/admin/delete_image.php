<?php
session_start();
require_once '../auth/check_admin.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['imageName'])) {
        throw new Exception('이미지 이름이 제공되지 않았습니다.');
    }

    $imageName = $data['imageName'];
    $imagePath = '../../image/share/' . $imageName;

    // 파일 경로 검증
    if (strpos(realpath($imagePath), realpath('../../image/share/')) !== 0) {
        throw new Exception('잘못된 파일 경로입니다.');
    }

    if (!file_exists($imagePath)) {
        throw new Exception('파일을 찾을 수 없습니다.');
    }

    if (unlink($imagePath)) {
        $response['success'] = true;
        $response['message'] = '이미지가 성공적으로 삭제되었습니다.';
    } else {
        throw new Exception('이미지 삭제에 실패했습니다.');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response); 