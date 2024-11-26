document.getElementById('loginForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const errorMessage = document.getElementById('errorMessage');

    console.log('로그인 시도:', { username });

    try {
        console.log('API 요청 시작...');
        const response = await fetch('/api/auth/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                username: username,
                password: password
            })
        });

        console.log('서버 응답 상태:', response.status);
        console.log('응답 헤더:', Object.fromEntries(response.headers.entries()));

        // 응답 텍스트 먼저 확인
        const responseText = await response.text();
        console.log('서버 응답 원본:', responseText);

        // 서버 오류 상태 코드 확인
        if (!response.ok) {
            throw new Error(`HTTP 오류! 상태: ${response.status}, 메시지: ${responseText}`);
        }

        let data;
        try {
            // 빈 응답 체크
            if (!responseText.trim()) {
                throw new Error('서버가 빈 응답을 반환했습니다.');
            }
            
            // Content-Type 헤더 확인
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                console.warn('서버가 JSON이 아닌 응답을 반환했습니다:', contentType);
            }

            data = JSON.parse(responseText);
            console.log('파싱된 응답 데이터:', data);
        } catch (parseError) {
            console.error('JSON 파싱 에러:', parseError);
            console.error('응답 내용:', responseText);
            throw new Error('서버 응답을 처리할 수 없습니다. 관리자에게 문의하세요.');
        }

        if (data.success) {
            console.log('로그인 성공, 리다이렉트 시작...');
            window.location.href = 'index.html';
        } else {
            console.error('로그인 실패:', data.message);
            errorMessage.textContent = data.message || '로그인에 실패했습니다.';
            errorMessage.style.display = 'block';
        }
    } catch (error) {
        console.error('로그인 에러 상세:', {
            name: error.name,
            message: error.message,
            stack: error.stack
        });
        errorMessage.textContent = '서버 오류가 발생했습니다: ' + error.message;
        errorMessage.style.display = 'block';
    }
});

// 애니메이션 적용
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => {
        document.querySelectorAll('.fade-in, .slide-in').forEach(el => {
            el.classList.add('visible');
        });
    }, 100);
});