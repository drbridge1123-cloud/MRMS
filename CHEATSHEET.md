# MRMS 파일 구조 Cheat Sheet

## frontend/assets/ — 정적 리소스 (CSS, JS)

### assets/css/
| 파일 | 설명 |
|------|------|
| `app.css` | 전역 스타일. 상태 배지 색상, 테이블 스타일, 사이드바, 스크롤바, 토스트, 타임라인, 모달 오버레이 등 모든 페이지에서 공유하는 CSS |

### assets/js/ — 공통 JS
| 파일 | 설명 |
|------|------|
| `app.js` | API 호출 함수 (`api.get`, `api.post` 등), 토스트 알림 (`showToast`), 날짜 포맷 (`formatDate`), 상태 라벨 매핑, 디바운스 헬퍼 |
| `utils.js` | 데이터 라벨/포맷터. 프로바이더 타입 12종, 요청 방법 7종, 전화번호 포맷, URL 쿼리 빌더 (`buildQueryString`), 상대시간 (`timeAgo`) |
| `shared.js` | **NEW.** 7개 리스트 페이지가 공유하는 함수. `listPageBase()` (데이터 로딩+정렬+필터), `initScrollContainer()` (테이블 스크롤), `modalHelpers()` (모달 열기/닫기) |
| `alpine-stores.js` | Alpine.js 글로벌 상태. 로그인 유저 정보 (`auth`), 알림 벨 (`notifications`), 사이드바 접힘/펼침 (`sidebar`) |

### assets/js/pages/ — 페이지별 JS (인라인에서 추출)
| 파일 | 원본 | 설명 |
|------|------|------|
| `dashboard.js` | `dashboard/index.php` | 대시보드 요약 통계 로드, 최근 케이스/팔로업 목록, 차트 데이터 |
| `cases.js` | `cases/index.php` | 케이스 목록 검색/필터/정렬, 상태별 필터링 |
| `case-detail.js` | `cases/detail.php` | 케이스 상세 페이지 로직. 프로바이더 관리, 요청 생성, 문서 업로드, 상태 변경, 활동 로그 |
| `providers.js` | `providers/index.php` | 프로바이더 목록 CRUD, 타입/난이도 필터, 연락처 관리 |
| `mr-tracker.js` | `tracker/index.php` | MR Tracker. 의료기록 요청 추적, 에스컬레이션 티어, 팔로업 관리, 벌크 액션 |
| `health-tracker.js` | `health-ledger/index.php` | Health Tracker. 보험사별 건강기록 요청 추적, 티어/상태 필터 |
| `mbds.js` | `mbds/index.php` | MBDS 목록. 케이스별 의료비 요약 검색/필터 |
| `mbds-edit.js` | `mbds/edit.php` | MBDS 편집. 라인 아이템 추가/삭제, 금액 자동 합산, 자동 저장 |

### assets/js/pages/admin/ — 관리자 페이지 JS
| 파일 | 원본 | 설명 |
|------|------|------|
| `users.js` | `admin/users.php` | 사용자 CRUD, 역할(admin/manager/staff) 관리, 비밀번호 초기화 |
| `templates.js` | `admin/templates.php` | 이메일/팩스 템플릿 관리, 첨부파일 설정, 미리보기 |
| `activity-log.js` | `admin/activity-log.php` | 시스템 활동 로그 조회, 사용자/액션/날짜 필터 |
| `data-management.js` | `admin/data-management.php` | DB 통계, 데이터 정리, CSV 임포트 |

---

## frontend/components/ — 재사용 PHP 컴포넌트

| 파일 | 설명 |
|------|------|
| `sidebar.php` | 왼쪽 네비게이션 바. Dashboard~MBDS 6개 메뉴 + Admin 4개 메뉴. 접힘/펼침 지원 |
| `header.php` | 상단 헤더 바. 현재 페이지 제목 표시 |
| `notification-bell.php` | 헤더 우측 알림 벨 아이콘. 읽지 않은 알림 개수 표시, 클릭하면 드롭다운 |
| `pagination.php` | 페이지네이션 UI. 이전/다음 버튼 + 페이지 번호. 리스트 페이지 하단에 사용 |
| `modal.php` | 모달 다이얼로그 기본 틀. 오버레이 + 애니메이션 + 닫기 버튼 |
| `status-badge.php` | PHP 함수로 상태 배지 HTML 렌더링. `renderStatusBadge()`, `renderDifficultyBadge()` |
| `search-input.php` | 검색 입력 필드 컴포넌트 |

---

## frontend/layouts/ — 페이지 레이아웃

| 파일 | 설명 |
|------|------|
| `main.php` | 메인 레이아웃. `<head>` (Tailwind CDN, 폰트, CSS/JS 로드) + 사이드바 + 헤더 + 컨텐츠 영역. 모든 로그인 후 페이지가 이 레이아웃 사용 |
| `auth.php` | 로그인 페이지 레이아웃. 사이드바/헤더 없이 중앙 정렬 폼만 |

---

## frontend/pages/ — 실제 페이지 (탭별 정리)

### pages/auth/
| 파일 | 설명 |
|------|------|
| `login.php` | 로그인 페이지. 아이디/비번 입력 폼 |

### pages/dashboard/
| 파일 | 설명 |
|------|------|
| `index.php` | 대시보드. 전체 요약 통계 카드 (총 케이스, 진행중, 팔로업 필요 등), 최근 활동 목록, 에스컬레이션 알림 |

### pages/cases/
| 파일 | 설명 |
|------|------|
| `index.php` | 케이스 목록. 검색, 상태 필터, 담당자 필터, 정렬 |
| `detail.php` | 케이스 상세 메인. 아래 5개 partial을 include하는 컨테이너 |
| `_detail-header.php` | 케이스 기본 정보 카드 (클라이언트명, 케이스번호, 상태, 담당자) |
| `_detail-providers.php` | 해당 케이스의 프로바이더 목록 테이블 + 프로바이더 추가/편집 모달 |
| `_detail-documents.php` | 문서 업로드/다운로드 섹션 |
| `_detail-activity.php` | 활동 로그 타임라인 (누가 언제 무엇을 했는지) |
| `_detail-modals.php` | 케이스 편집, 상태 변경 등 모달 다이얼로그들 |

### pages/providers/
| 파일 | 설명 |
|------|------|
| `index.php` | 프로바이더 목록. 병원/의사/약국 등 의료기관 검색, 타입/난이도 필터 |
| `detail.php` | 프로바이더 상세. 연락처, 평균 응답일, 관련 케이스 목록 |

### pages/mr-tracker/ (현재 `tracker/`)
| 파일 | 설명 |
|------|------|
| `index.php` | MR Tracker. 의료기록 요청 상태 추적 대시보드. 에스컬레이션 티어(Action/Manager/Admin), 팔로업 기한, 벌크 요청 |

### pages/health-tracker/ (현재 `health-ledger/`)
| 파일 | 설명 |
|------|------|
| `index.php` | Health Tracker. 보험사에 보내는 건강기록 요청 추적. MR Tracker와 비슷한 구조이지만 대상이 보험사 |

### pages/mbds/
| 파일 | 설명 |
|------|------|
| `index.php` | MBDS 목록. 케이스별 의료비 손해 요약(Medical Bill Damage Summary) 리스트 |
| `edit.php` | MBDS 편집. 라인별 의료비 항목 입력, 금액 자동 합산, 인라인 자동저장 |

### pages/reports/
| 파일 | 설명 |
|------|------|
| `index.php` | 리포트 페이지 (Coming Soon 플레이스홀더) |

### pages/admin/
| 파일 | 설명 |
|------|------|
| `users.php` | 사용자 관리. 직원 계정 생성/편집/비활성화, 역할 설정 (admin/manager/staff) |
| `templates.php` | 템플릿 관리. 의료기록 요청 이메일/팩스 템플릿 편집, 첨부파일 관리, 미리보기 |
| `activity-log.php` | 활동 로그. 전체 시스템 액션 기록 조회 (누가 뭘 했는지) |
| `data-management.php` | 데이터 관리. DB 테이블별 레코드 수 확인, CSV 임포트, 데이터 정리 도구 |
