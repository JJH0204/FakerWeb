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
