#!/bin/bash

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 로고 출력
echo -e "${GREEN}"
echo "███████╗ █████╗ ██╗  ██╗███████╗██████╗ ██╗    ██╗███████╗██████╗ "
echo "██╔════╝██╔══██╗██║ ██╔╝██╔════╝██╔══██╗██║    ██║██╔════╝██╔══██╗"
echo "█████╗  ███████║█████╔╝ █████╗  ██████╔╝██║ █╗ ██║█████╗  ██████╔╝"
echo "██╔══╝  ██╔══██║██╔═██╗ ██╔══╝  ██╔══██╗██║███╗██║██╔══╝  ██╔══██╗"
echo "██║     ██║  ██║██║  ██╗███████╗██║  ██║╚███╔███╔╝███████╗██████╔╝"
echo "╚═╝     ╚═╝  ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝ ╚══╝╚══╝ ╚══════╝╚═════╝ "
echo -e "${NC}"

# 함수: 서비스 상태 확인
check_service_status() {
    local container_name=$1
    if docker ps --format '{{.Names}}' | grep -q "^${container_name}$"; then
        echo -e "${GREEN}✓ ${container_name} is running${NC}"
        return 0
    else
        echo -e "${RED}✗ ${container_name} is not running${NC}"
        return 1
    fi
}

# 함수: 모든 서비스 상태 확인
check_all_services() {
    echo -e "\n${YELLOW}Checking services status...${NC}"
    local all_running=true

    services=("config-fakerweb-1" "config-fakerdb-1" "config-waf-nginx-1")
    for service in "${services[@]}"; do
        if ! check_service_status "$service"; then
            all_running=false
        fi
    done

    if $all_running; then
        echo -e "\n${GREEN}All services are running!${NC}"
        echo -e "Access the application at:"
        echo -e "${GREEN}➜ Web Interface: http://localhost:8080${NC}"
        echo -e "${GREEN}➜ WAF Interface: http://localhost:8081${NC}"
    else
        echo -e "\n${RED}Some services are not running.${NC}"
    fi
}

# 함수: 서비스 시작
start_services() {
    echo -e "\n${YELLOW}Starting services...${NC}"
    cd config
    docker-compose down --volumes 2>/dev/null
    docker-compose up -d --build
    cd ..
    sleep 5
    check_all_services
}

# 함수: 서비스 중지
stop_services() {
    echo -e "\n${YELLOW}Stopping services...${NC}"
    cd config
    docker-compose down --volumes
    cd ..
    echo -e "${GREEN}All services stopped.${NC}"
}

# 함수: 로그 보기
view_logs() {
    echo -e "\n${YELLOW}Viewing logs...${NC}"
    cd config
    docker-compose logs -f
}

# 메인 메뉴
show_menu() {
    echo -e "\n${YELLOW}FakerWeb Management Menu${NC}"
    echo "1) Start Services"
    echo "2) Stop Services"
    echo "3) Check Status"
    echo "4) View Logs"
    echo "5) Exit"
    echo
    read -p "Select an option: " choice

    case $choice in
        1) start_services ;;
        2) stop_services ;;
        3) check_all_services ;;
        4) view_logs ;;
        5) echo -e "${GREEN}Goodbye!${NC}"; exit 0 ;;
        *) echo -e "${RED}Invalid option${NC}" ;;
    esac

    show_menu
}

# 스크립트 시작
echo -e "${YELLOW}Welcome to FakerWeb Management Script${NC}"
show_menu
