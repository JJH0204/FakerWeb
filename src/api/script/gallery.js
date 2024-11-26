document.addEventListener('DOMContentLoaded', async function() {
    const galleryGrid = document.getElementById('gallery-grid');
    
    try {
        // 이미지 목록을 가져오는 API 호출
        const response = await fetch('../api/admin/get_images.php');
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || '이미지를 불러올 수 없습니다.');
        }

        // 이미지가 없는 경우
        if (data.images.length === 0) {
            galleryGrid.innerHTML = '<p class="error-message">등록된 이미지가 없습니다.</p>';
            return;
        }

        // 이미지 정렬 (번호순 - 안전하게 처리)
        data.images.sort((a, b) => {
            const matchA = a.name.match(/\d+/);
            const matchB = b.name.match(/\d+/);
            
            // 숫자가 없는 파일은 마지막으로
            if (!matchA) return 1;
            if (!matchB) return -1;
            
            const numA = parseInt(matchA[0]);
            const numB = parseInt(matchB[0]);
            return numA - numB;
        });

        // 이미지 표시 (이미지 파일만 표시)
        data.images.forEach(image => {
            // 이미지 확장자 확인
            const ext = image.name.split('.').pop().toLowerCase();
            const allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (allowedExts.includes(ext)) {
                const galleryItem = document.createElement('div');
                galleryItem.className = 'gallery-item';
                
                galleryItem.innerHTML = `
                    <img src="../image/share/${image.name}" 
                         alt="Faker Image" 
                         loading="lazy"
                         onerror="this.onerror=null; this.src='../assets/images/error.png'; this.setAttribute('data-error', '${image.name}')">
                `;

                galleryGrid.appendChild(galleryItem);
            }
        });

    } catch (error) {
        console.error('갤러리 로드 중 에러:', error);
        galleryGrid.innerHTML = `<p class="error-message">이미지를 불러오는데 실패했습니다: ${error.message}</p>`;
    }
});