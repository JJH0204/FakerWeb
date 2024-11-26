// 애니메이션 요소에 visible 클래스 추가하는 함수
function addVisibleClass(element) {
    if (element && element.classList) {
        element.classList.add('visible');
    }
}

// Intersection Observer for fade-in and slide-in animations
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            addVisibleClass(entry.target);
        }
    });
}, {
    threshold: 0.1,
    rootMargin: '50px'
});

// 호버 효과 추가 함수
function addHoverEffect(elements, transformValue) {
    if (elements.length > 0) {
        elements.forEach(element => {
            element.addEventListener('mouseover', () => {
                element.style.transform = transformValue;
            });
            element.addEventListener('mouseout', () => {
                element.style.transform = 'translateY(0)';
            });
        });
    }
}

// DOM이 로드되면 실행
document.addEventListener('DOMContentLoaded', () => {
    // 히어로 섹션 애니메이션
    const heroContent = document.querySelector('.hero-content');
    if (heroContent) {
        const fadeElements = heroContent.querySelectorAll('.fade-in');
        const slideElements = heroContent.querySelectorAll('.slide-in');
        
        fadeElements.forEach(el => addVisibleClass(el));
        slideElements.forEach(el => addVisibleClass(el));
    }

    // 다른 애니메이션 요소들 관찰
    const animatedElements = document.querySelectorAll('.fade-in:not(.hero-content .fade-in), .slide-in:not(.hero-content .slide-in)');
    animatedElements.forEach(el => observer.observe(el));

    // 업적 카드 호버 효과
    const achievementCards = document.querySelectorAll('.achievement-card');
    addHoverEffect(achievementCards, 'translateY(-10px) scale(1.05)');

    // 챔피언 카드 호버 효과
    const championCards = document.querySelectorAll('.champion-card');
    addHoverEffect(championCards, 'translateY(-10px)');
});

// Smooth scroll for navigation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Parallax effect for hero section
window.addEventListener('scroll', () => {
    const hero = document.querySelector('.hero');
    const scrolled = window.pageYOffset;
    hero.style.backgroundPositionY = scrolled * 0.5 + 'px';
});

async function loadImages() {
    const imageGrid = document.getElementById('imageGrid');
    if (!imageGrid) return;

    try {
        // API를 통해 이미지 목록 가져오기
        const response = await fetch('./api/admin/get_images.php');
        if (!response.ok) {
            throw new Error('Failed to fetch images');
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Failed to load images');
        }

        // 날짜 기준으로 정렬 (YYYYMMDD 형식)
        const sortedImages = data.images.sort((a, b) => {
            const dateA = a.name.match(/\d{8}/);
            const dateB = b.name.match(/\d{8}/);
            
            if (dateA && dateB) {
                return dateB[0].localeCompare(dateA[0]);
            }
            return 0;
        });

        // 최신 5개 이미지만 표시
        const recentImages = sortedImages.slice(0, 5);

        // 기존 이미지 제거
        imageGrid.innerHTML = '';

        // 이미지 요소 생성 및 추가
        recentImages.forEach(image => {
            const imgElement = document.createElement('img');
            imgElement.src = image.url;
            imgElement.alt = `Faker 이미지 ${image.name}`;
            imgElement.className = 'gallery-image';
            
            // 이미지 로드 실패 시 대체 이미지 표시
            imgElement.onerror = () => {
                imgElement.src = './image/default-image.jpg';
                imgElement.alt = '이미지를 불러올 수 없습니다';
            };

            imageGrid.appendChild(imgElement);
        });

    } catch (error) {
        console.error('이미지 로드 중 오류 발생:', error);
        imageGrid.innerHTML = '<p class="error-message">이미지를 불러오는 중 오류가 발생했습니다.</p>';
    }
}

// 모달 관련 코드를 DOMContentLoaded 이벤트 핸들러 안으로 이동
document.addEventListener('DOMContentLoaded', function () {
    // 이미지 로드 함수 호출
    loadImages();

    // 모달 열기
    document.querySelectorAll('.champion-card').forEach(card => {
        card.addEventListener('click', function () {
            const videoId = this.dataset.videoId;
            if (videoId) {
                const modal = document.getElementById('videoModal');
                const videoPlayer = document.getElementById('videoPlayer');
                videoPlayer.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
                modal.style.display = 'block';
            }
        });
    });

    // 닫기 버튼으로 모달 닫기
    const closeBtn = document.querySelector('.close');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    // 모달 외부 클릭으로 닫기
    window.addEventListener('click', function (event) {
        const modal = document.getElementById('videoModal');
        if (event.target === modal) {
            closeModal();
        }
    });
});

// ESC 키로 모달 닫기
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// 모달 닫기 함수
function closeModal() {
    const modal = document.getElementById('videoModal');
    const videoPlayer = document.getElementById('videoPlayer');
    if (modal && videoPlayer) {
        videoPlayer.src = '';
        modal.style.display = 'none';
    }
}

function rotateQuotes() {
    const quotes = document.querySelectorAll('.quote-slide');
    const backgrounds = document.querySelectorAll('.quote-background');
    let currentQuote = 0;
    
    // 초기 상태 설정
    quotes[0].classList.add('active');
    backgrounds[0].classList.add('active');
    
    setInterval(() => {
        quotes[currentQuote].classList.remove('active');
        backgrounds[currentQuote].classList.remove('active');
        currentQuote = (currentQuote + 1) % quotes.length;
        quotes[currentQuote].classList.add('active');
        backgrounds[currentQuote].classList.add('active');
    }, 5000);
}

document.addEventListener('DOMContentLoaded', rotateQuotes);