// 이미지 삭제 함수
async function deleteImage(imageName) {
    if (!confirm('정말로 이 이미지를 삭제하시겠습니까?')) {
        return;
    }

    try {
        const response = await fetch('../api/admin/delete_image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ imageName: imageName }),
            credentials: 'include'
        });

        const result = await response.json();
        
        if (result.success) {
            // 성공 시 해당 이미지 요소를 직접 제거
            const imageElement = document.querySelector(`.image-item img[alt="${imageName}"]`).closest('.image-item');
            if (imageElement) {
                imageElement.remove();
            }
            alert('이미지가 삭제되었습니다.');
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('이미지 삭제 중 오류:', error);
        alert(error.message || '이미지 삭제 중 오류가 발생했습니다.');
    }
}

async function loadImages() {
    const imageGrid = document.getElementById('imageGrid');
    imageGrid.innerHTML = ''; // 기존 내용 초기화

    try {
        // 이미지 목록 가져오기
        const response = await fetch('../api/admin/get_images.php', {
            credentials: 'include'
        });
        
        // console.log('이미지 목록 응답:', response);
        const data = await response.json();
        // console.log('이미지 데이터:', data);

        if (!data.success) {
            throw new Error(data.message || '이미지 로드 실패');
        }

        // 이미지 이름으로 정렬
        data.images.sort((a, b) => a.name.localeCompare(b.name));

        // 각 이미지에 대해 격자 아이템 생성
        data.images.forEach(image => {
            const imageItem = document.createElement('div');
            imageItem.className = 'image-item';
            
            // 이미지 경로 설정
            const imagePath = `../image/share/${image.name}`;
            // console.log('이미지 경로:', imagePath);

            imageItem.innerHTML = `
                <div class="image-container">
                    <img src="${imagePath}" alt="${image.name}" 
                        onerror="this.onerror=null; this.src='../assets/images/error.png';">
                </div>
                <div class="image-info">
                    <span class="image-name">${image.name}</span>
                    <button class="delete-btn" onclick="deleteImage('${image.name}')">삭제</button>
                </div>
            `;

            imageGrid.appendChild(imageItem);
        });

    } catch (error) {
        console.error('이미지 로드 중 에러:', error);
        imageGrid.innerHTML = `<p class="error-message">이미지를 불러오는데 실패했습니다: ${error.message}</p>`;
    }
}

// 스타일 추가
const style = document.createElement('style');
style.textContent = `
    .image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .image-item {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .image-container {
        width: 100%;
        height: 200px;
        overflow: hidden;
    }

    .image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .image-container img:hover {
        transform: scale(1.05);
    }

    .image-info {
        padding: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8f9fa;
    }

    .image-name {
        font-size: 0.9em;
        color: #333;
        word-break: break-all;
    }

    .delete-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.8em;
    }

    .delete-btn:hover {
        background: #c82333;
    }

    .error-message {
        color: #dc3545;
        text-align: center;
        grid-column: 1 / -1;
        padding: 20px;
    }
`;
document.head.appendChild(style);

// 페이지 로드 시 이미지 로드
window.addEventListener('load', () => {
    loadImages();
});

// 로그아웃
async function logout() {
    try {
        await fetch('../api/auth/logout.php', {
            method: 'POST',
            credentials: 'include'
        });
    } catch (error) {
        console.error('Logout failed:', error);
    } finally {
        window.location.replace('login.html');
    }
}

// 이미지 업로드 폼 제출 처리 부분 수정
document.getElementById('imageUploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    const fileInput = document.getElementById('imageInput');
    const files = fileInput.files;
    
    if (files.length === 0) {
        alert('업로드할 이미지를 선택해주세요.');
        return;
    }

    // 파일 추가
    formData.append('images[]', files[0]);

    try {
        const response = await fetch('../api/admin/upload_image.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });

        const responseText = await response.text();
        
        // 응답 유효성 검사
        let result;
        try {
            // 응답이 비어있는지 확인
            if (!responseText.trim()) {
                throw new Error('빈 응답');
            }
            
            result = JSON.parse(responseText);
            
            // 응답 형식 검증
            if (typeof result !== 'object') {
                throw new Error('잘못된 응답 형식');
            }
        } catch (e) {
            if (responseText.includes('<!DOCTYPE') || responseText.includes('<html')) {
                alert('세션이 만료되었습니다. 다시 로그인해주세요.');
                window.location.href = 'login.html';
                return;
            }
            
            console.error('서버 응답:', responseText);
            console.error('에러 내용:', e);
            
            // 사용자에게 친숙한 에러 메시지 표시
            alert('이미지 처리 중 오류가 발생했습니다. 다시 시도해주세요.');
            return;
        }
        
        if (result.success) {
            alert('이미지 업로드 성공');
            // 이미지 목록 새로고침
            loadImages();
            // 입력 필드 초기화
            fileInput.value = '';
            // 미리보기 초기화
            document.getElementById('uploadPreview').innerHTML = '';
        } else {
            throw new Error(result.message || '업로드 실패');
        }
    } catch (error) {
        console.error('업로드 에러:', error);
        alert('이미지 업로드 중 오류가 발생했습니다: ' + error.message);
    }
});

// 이미지 미리보기 기능
document.getElementById('imageInput').addEventListener('change', function(e) {
    const preview = document.getElementById('uploadPreview');
    preview.innerHTML = '';
    
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '200px';
            img.style.maxHeight = '200px';
            preview.appendChild(img);
        }
        
        reader.readAsDataURL(this.files[0]);
    }
});