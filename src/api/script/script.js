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
    const baseUrl = './image/share/';
    const images = [];
    const imageFormats = ['.jpg', '.webp', '.png', '.jpg_large'];

    // 이미지 파일 존재 여부 확인 함수
    async function checkImageExists(url) {
        try {
            const response = await fetch(url);
            return response.ok;
        } catch {
            return false;
        }
    }

    // 이미지 파일 찾기
    let imageNumber = 1;
    outerLoop: while (true) {
        let imageFound = false;

        // 각 이미지 번호에 대해 모든 포맷 확인
        for (const format of imageFormats) {
            const imageUrl = `${baseUrl}image${imageNumber}${format}`;
            if (await checkImageExists(imageUrl)) {
                images.push({
                    number: imageNumber,
                    url: imageUrl
                });
                imageFound = true;
                imageNumber++;
                continue outerLoop;
            }
        }

        // 모든 포맷에서 이미지를 찾지 못했다면 종료
        if (!imageFound) {
            break;
        }
    }

    // 이미지 번호 기준 내림차순 정렬
    images.sort((a, b) => b.number - a.number);

    // 상위 5개 이미지만 표시
    const imagesToShow = images.slice(0, 5);

    // 이미지 요소 생성 및 추가
    imagesToShow.forEach(image => {
        const imgElement = document.createElement('img');
        imgElement.src = image.url;
        imgElement.alt = `Faker 이미지 ${image.number}`;
        imgElement.className = 'gallery-image';
        imageGrid.appendChild(imgElement);
    });
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