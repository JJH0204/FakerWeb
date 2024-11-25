# G.O.A.T Web
 이미지를 전시하고 공유하는 faker 선수의 팬 페이지 컨셉의 웹 서비스 CTF


## 🐳 Docker 환경 구성 가이드

### 사전 요구사항

- Docker Desktop 설치
- Git 설치

### 설치 및 실행 방법

1. 저장소 클론
```bash
git clone https://github.com/JJH0204/FakerWeb.git
cd FakerWeb
```

2. Docker 컨테이너 실행
```bash
# Docker 컨테이너 빌드 및 실행
docker compose up -d

# 실행 중인 컨테이너 확인
docker ps
```

3. 서비스 접속
- 웹 서비스: http://localhost:8080

### Docker 구성 요소

### 문제 해결

일반적인 문제 해결 방법:

1. 로그 확인
```bash
# 전체 서비스 로그 확인
docker-compose logs

# 특정 서비스 로그 확인
docker-compose logs web
```

2. 컨테이너 재시작
```bash
# 전체 서비스 재시작
docker-compose restart

# 특정 서비스 재시작
docker-compose restart web
```

3. 컨테이너 중지 및 제거
```bash
# 컨테이너 중지
docker-compose down

# 컨테이너 및 볼륨 완전 제거
docker-compose down -v
```

### 개발 환경 설정

로컬 개발 시 다음 명령어를 사용하여 개발 모드로 실행:

```bash
# 개발 모드로 실행
docker-compose -f docker-compose.dev.yml up

# 변경사항 실시간 반영
docker-compose -f docker-compose.dev.yml up --build
