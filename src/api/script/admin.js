let visitorChart = null;
        let currentPage = 1;
        const PAGE_SIZE = 10;

        // 초기 세션 체크
        async function checkSession() {
            console.log('세션 체크 시작...');
            
            try {
                console.log('세션 체크 API 호출 중...');
                const response = await fetch('../api/auth/check_session.php', {
                    method: 'GET',
                    credentials: 'include'
                });
                
                console.log('서버 응답 상태:', response.status);
                console.log('응답 헤더:', Object.fromEntries(response.headers.entries()));
                
                if (!response.ok) {
                    console.error('세션 체크 실패 - HTTP 상태:', response.status);
                    throw new Error('Session check failed');
                }

                const responseText = await response.text();
                console.log('서버 응답 원본:', responseText);

                let data;
                try {
                    data = JSON.parse(responseText);
                    console.log('파싱된 세션 데이터:', data);
                } catch (parseError) {
                    console.error('JSON 파싱 에러:', parseError);
                    throw new Error('서버 응답을 처리할 수 없습니다: ' + responseText);
                }

                if (!data.success || !data.isAdmin) {
                    console.log('관리자 권한 없음:', data);
                    await new Promise(resolve => setTimeout(resolve, 2000)); // 2초 대기
                    window.location.replace('login.html');
                } else {
                    console.log('세션 체크 성공: 관리자 확인됨');
                }
            } catch (error) {
                console.error('세션 체크 에러 상세:', {
                    name: error.name,
                    message: error.message,
                    stack: error.stack
                });
                await new Promise(resolve => setTimeout(resolve, 2000)); // 2초 대기
                window.location.replace('login.html');
            }
        }

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

        // 대시보드 초기화
        async function initializeDashboard() {
            await checkSession();
            await loadDashboardData();
            await loadActivityLogs();
        }

        // 페이지 로드 시 초기화
        window.addEventListener('load', initializeDashboard);

        // 대시보드 데이터 로드 함수
        async function loadDashboardData() {
            try {
                const response = await fetch('../api/admin/dashboard_data.php', {
                    credentials: 'include'
                });
                const data = await response.json();

                if (data.success) {
                    // 통계 업데이트
                    document.getElementById('totalVisitors').textContent = data.totalVisitors;
                    document.getElementById('todayVisitors').textContent = data.todayVisitors;
                    document.getElementById('activeUsers').textContent = data.activeUsers;
                    
                    // 차트 업데이트
                    if (data.visitorData) {
                        updateVisitorChart(data.visitorData);
                    }
                }
            } catch (error) {
                console.error('대시보드 데이터 로드 실패:', error);
            }
        }

        // 활동 로그 로드
        async function loadActivityLogs() {
            const activityFilter = document.getElementById('activity-filter').value;
            const statusFilter = document.getElementById('status-filter').value;
            
            try {
                const response = await fetch(`../api/admin/activity_logs.php?page=${currentPage}&activity_type=${activityFilter}&status=${statusFilter}`);
                const data = await response.json();
                
                const tbody = document.getElementById('activity-logs');
                tbody.innerHTML = '';
                
                data.logs.forEach(log => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${new Date(log.timestamp).toLocaleString()}</td>
                            <td>${log.activity_type}</td>
                            <td>${log.ip_address}</td>
                            <td>${log.status}</td>
                            <td>${log.details}</td>
                        </tr>
                    `;
                });
                
                // 페이지네이션 업데이트
                document.getElementById('prev-page').disabled = currentPage === 1;
                document.getElementById('next-page').disabled = !data.has_more;
                document.getElementById('page-info').textContent = `페이지 ${currentPage}`;
            } catch (error) {
                console.error('Failed to load activity logs:', error);
            }
        }

        // 방문자 차트 업데이트 함수
        function updateVisitorChart(data) {
            const ctx = document.getElementById('visitorChart').getContext('2d');
            
            // 기존 차트가 있다면 제거
            if (visitorChart) {
                visitorChart.destroy();
            }

            // 새로운 차트 생성
            visitorChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: '시간별 방문자',
                        data: data.values,
                        borderColor: '#e31937',
                        backgroundColor: 'rgba(227, 25, 55, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: '#ffffff'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#ffffff'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#ffffff'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    }
                }
            });
        }

        // 이벤트 리스너
        document.getElementById('activity-filter').addEventListener('change', loadActivityLogs);
        document.getElementById('status-filter').addEventListener('change', loadActivityLogs);
        document.getElementById('prev-page').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                loadActivityLogs();
            }
        });
        document.getElementById('next-page').addEventListener('click', () => {
            currentPage++;
            loadActivityLogs();
        });