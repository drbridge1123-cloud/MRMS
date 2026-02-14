# MRMS - Medical Records Management System

> **Version:** 1.0.0
> **Stack:** PHP 7.4+ / MySQL (MariaDB) / Alpine.js 3.x / Tailwind CSS
> **Environment:** XAMPP (Windows) / Apache
> **Last Updated:** 2026-02-14

---

## Overview

MRMS는 법률사무소에서 의료 기록 요청을 관리하기 위한 웹 기반 시스템입니다.
케이스별 프로바이더 관리, 기록 요청/수신 추적, 데드라인 관리, 에스컬레이션 알림, 이메일/팩스 발송 기능을 제공합니다.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 7.4+ (Vanilla, no framework) |
| Database | MySQL / MariaDB (InnoDB, utf8mb4) |
| Frontend JS | Alpine.js 3.x (CDN) |
| Frontend CSS | Tailwind CSS (CDN) |
| Email | PHPMailer (Composer) - Gmail SMTP |
| Fax | Phaxio API |
| Server | Apache (XAMPP on Windows) |

---

## Project Structure

```
MRMS/
├── backend/
│   ├── api/                    # REST API endpoints
│   │   ├── index.php           # Central router
│   │   ├── auth/               # login, logout, me
│   │   ├── cases/              # CRUD + import/export
│   │   ├── providers/          # CRUD + search + import/export
│   │   ├── case-providers/     # Link/status/deadline/assign
│   │   ├── requests/           # Record request CRUD + send
│   │   ├── receipts/           # Record receipt + verify
│   │   ├── notes/              # Case notes (Activity Log)
│   │   ├── notifications/      # List + mark read
│   │   ├── dashboard/          # Summary + overdue + followup + escalations
│   │   ├── users/              # Admin user management
│   │   ├── activity-log/       # Audit trail
│   │   └── tracker/            # Records tracker view
│   ├── config/
│   │   ├── app.php             # App constants + escalation thresholds
│   │   ├── auth.php            # Session config
│   │   ├── database.php        # DB connection (PDO singleton)
│   │   └── email.php           # SMTP + Fax + Firm info
│   ├── helpers/
│   │   ├── db.php              # DB query functions + logActivity
│   │   ├── auth.php            # Session/auth/CSRF
│   │   ├── response.php        # JSON response helpers
│   │   ├── validator.php       # Input validation
│   │   ├── date.php            # Date utilities
│   │   ├── email.php           # PHPMailer wrapper
│   │   ├── fax.php             # Phaxio fax sender
│   │   ├── csv.php             # CSV import/export
│   │   ├── letter-template.php # Request letter HTML generator
│   │   └── escalation.php      # Escalation tier + notifications + email
│   └── cron/
│       ├── generate_notifications.php  # Daily notification generation
│       └── update_statistics.php       # Provider avg response days
├── frontend/
│   ├── index.php               # Entry point (redirect)
│   ├── layouts/
│   │   ├── main.php            # Main layout (sidebar + header)
│   │   └── auth.php            # Auth layout (login)
│   ├── components/
│   │   ├── header.php          # Top bar + notification bell
│   │   ├── sidebar.php         # Left navigation
│   │   ├── notification-bell.php # Notification dropdown
│   │   ├── modal.php           # Reusable modal
│   │   ├── search-input.php    # Search field
│   │   ├── pagination.php      # Page controls
│   │   └── status-badge.php    # Status display
│   ├── pages/
│   │   ├── auth/login.php
│   │   ├── dashboard/index.php
│   │   ├── cases/index.php, detail.php, create.php
│   │   ├── providers/index.php, detail.php, create.php
│   │   ├── tracker/index.php
│   │   ├── reports/index.php
│   │   └── admin/users.php, activity-log.php, data-management.php
│   └── assets/
│       ├── css/app.css         # Custom styles
│       └── js/
│           ├── app.js          # API helper + utilities
│           ├── alpine-stores.js # Global Alpine stores
│           └── utils.js        # Labels + formatting
├── database/
│   └── schema.sql              # Full DB schema
└── vendor/                     # Composer (PHPMailer)
```

---

## Database Schema

### Tables (12)

#### 1. `users` - 사용자
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | |
| username | VARCHAR(50) UNIQUE | 로그인 ID |
| password_hash | VARCHAR(255) | bcrypt 해시 |
| full_name | VARCHAR(100) | 이름 |
| email | VARCHAR(255) NULL | 이메일 (에스컬레이션 알림용) |
| role | ENUM(admin, manager, staff) | 역할 |
| is_active | TINYINT(1) | 활성 여부 |
| created_at, updated_at | DATETIME | |

#### 2. `cases` - 케이스
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | |
| case_number | VARCHAR(50) UNIQUE | 케이스 번호 |
| client_name | VARCHAR(100) | 의뢰인 이름 |
| client_dob | DATE NULL | 생년월일 |
| doi | DATE NULL | Date of Injury |
| assigned_to | INT FK → users | 담당자 |
| status | ENUM(active, pending_review, completed, on_hold) | |
| attorney_name | VARCHAR(100) NULL | 변호사 |
| ini_completed | TINYINT(1) | INI 완료 여부 |
| notes | TEXT NULL | |

#### 3. `providers` - 프로바이더 (의료기관)
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | |
| name | VARCHAR(200) | 기관명 |
| type | ENUM(hospital, er, chiro, imaging, physician, surgery_center, pharmacy, other) | |
| address, phone, fax, email | VARCHAR | 연락처 |
| portal_url | VARCHAR(300) NULL | 포털 URL |
| preferred_method | ENUM(email, fax, portal, phone, mail) | 선호 연락 방법 |
| uses_third_party | TINYINT(1) | 제3자 대행 여부 |
| avg_response_days | INT NULL | 평균 응답 일수 |
| difficulty_level | ENUM(easy, medium, hard) | 난이도 |

#### 4. `provider_contacts` - 프로바이더 연락처
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | |
| provider_id | INT FK → providers (CASCADE) | |
| department | VARCHAR(100) NULL | 부서 |
| contact_type | ENUM(email, fax, portal, phone) | |
| contact_value | VARCHAR(200) | |
| is_primary | TINYINT(1) | 주 연락처 여부 |

#### 5. `case_providers` - 케이스-프로바이더 연결
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | |
| case_id | INT FK → cases (CASCADE) | |
| provider_id | INT FK → providers (CASCADE) | |
| treatment_start_date, treatment_end_date | DATE NULL | 치료 기간 |
| record_types_needed | SET(medical_records, billing, chart, imaging, op_report) | 필요 기록 유형 |
| overall_status | ENUM(not_started, requesting, follow_up, received_partial, received_complete, verified) | |
| assigned_to | INT FK → users NULL | 담당자 |
| deadline | DATE NULL | 데드라인 |

#### 6. `record_requests` - 기록 요청
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | |
| case_provider_id | INT FK → case_providers (CASCADE) | |
| request_date | DATE | 요청일 |
| request_method | ENUM(email, fax, portal, phone, mail) | 요청 방법 |
| request_type | ENUM(initial, follow_up, re_request, rfd) | 요청 유형 |
| sent_to | VARCHAR(200) NULL | 수신자 |
| send_status | ENUM(draft, sending, sent, failed) | 발송 상태 |
| sent_at | DATETIME NULL | 발송 시간 |
| letter_html | LONGTEXT | 요청서 HTML |
| next_followup_date | DATE NULL | 다음 팔로업 예정일 |

#### 7. `record_receipts` - 기록 수신
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | |
| case_provider_id | INT FK → case_providers (CASCADE) | |
| received_date | DATE | 수신일 |
| received_method | ENUM | 수신 방법 |
| has_medical_records, has_billing, has_chart, has_imaging, has_op_report | TINYINT(1) | 수신 항목 |
| is_complete | TINYINT(1) | 완전 수신 여부 |
| file_location | VARCHAR(500) NULL | SharePoint 경로 |

#### 8. `case_notes` - 케이스 노트 (Activity Log)
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | |
| case_id | INT FK → cases (CASCADE) | |
| case_provider_id | INT FK → case_providers NULL | 관련 프로바이더 |
| user_id | INT FK → users (CASCADE) | 작성자 |
| note_type | ENUM(general, follow_up, issue, handoff) | |
| contact_method | ENUM(phone, fax, email, portal, mail, in_person, other) NULL | 연락 방법 |
| contact_date | DATETIME NULL | 연락 일시 |
| content | TEXT | 내용 |

#### 9. `notifications` - 알림
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | |
| user_id | INT FK → users (CASCADE) | |
| case_provider_id | INT FK → case_providers NULL | |
| type | ENUM(followup_due, deadline_warning, deadline_overdue, handoff, new_assignment, escalation_action_needed, escalation_manager, escalation_admin) | |
| message | VARCHAR(500) | |
| is_read | TINYINT(1) | |

#### 10. `activity_log` - 감사 로그
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | |
| user_id | INT FK → users | |
| action | VARCHAR(100) | 액션 (created, updated, deleted, etc.) |
| entity_type | VARCHAR(50) | 대상 (case, provider, case_provider, etc.) |
| entity_id | INT NULL | |
| details | JSON NULL | 상세 데이터 |

#### 11. `send_log` - 발송 로그
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | |
| record_request_id | INT FK → record_requests (CASCADE) | |
| send_method | ENUM(email, fax) | |
| recipient | VARCHAR(200) | |
| status | ENUM(success, failed) | |
| external_id | VARCHAR(200) | SMTP Message ID / Fax ID |

#### 12. `deadline_changes` - 데드라인 변경 이력
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | |
| case_provider_id | INT FK → case_providers (CASCADE) | |
| old_deadline, new_deadline | DATE | 이전/새 데드라인 |
| reason | VARCHAR(500) | 변경 사유 (필수) |
| changed_by | INT FK → users (CASCADE) | 변경자 |

---

## API Endpoints

### Auth
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/login` | 로그인 (username, password) |
| POST | `/auth/logout` | 로그아웃 |
| GET | `/auth/me` | 현재 사용자 정보 |

### Cases
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/cases` | 목록 (pagination, filter: status/assigned_to/search, sort) |
| GET | `/cases/{id}` | 상세 조회 |
| POST | `/cases` | 생성 |
| PUT | `/cases/{id}` | 수정 |
| DELETE | `/cases/{id}` | 삭제 |
| GET | `/cases/export` | CSV 내보내기 |
| POST | `/cases/import` | CSV 가져오기 |

### Providers
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/providers` | 목록 (pagination, filter: type/difficulty/search) |
| GET | `/providers/{id}` | 상세 조회 |
| POST | `/providers` | 생성 |
| PUT | `/providers/{id}` | 수정 |
| GET | `/providers/search` | 빠른 검색 (드롭다운용) |
| GET | `/providers/export` | CSV 내보내기 |
| POST | `/providers/import` | CSV 가져오기 |

### Case-Providers
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/case-providers?case_id={id}` | 케이스별 프로바이더 목록 |
| POST | `/case-providers` | 프로바이더 연결 |
| PUT | `/case-providers/{id}/status` | 상태 변경 |
| PUT | `/case-providers/{id}/assign` | 담당자 배정 |
| PUT | `/case-providers/{id}/deadline` | 데드라인 변경 (사유 필수) |
| GET | `/case-providers/{id}/deadline-history` | 데드라인 변경 이력 |
| DELETE | `/case-providers/{id}` | 연결 해제 |

### Requests
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/requests?case_provider_id={id}` | 요청 목록 |
| POST | `/requests` | 새 요청 생성 |
| POST | `/requests/followup` | 팔로업 요청 |
| GET | `/requests/{id}/preview` | 요청서 미리보기 (HTML) |
| POST | `/requests/{id}/send` | 이메일/팩스 발송 |

### Receipts
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/receipts` | 수신 기록 |
| PUT | `/receipts/{id}/verify` | 수신 확인 |

### Notes (Activity Log)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notes?case_id={id}` | 노트 목록 (프로바이더 필터 가능) |
| POST | `/notes` | 노트 추가 (프로바이더/연락방법/일시 선택 가능) |
| DELETE | `/notes/{id}` | 노트 삭제 |

### Notifications
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | 알림 목록 |
| PUT | `/notifications/{id}/read` | 읽음 처리 |
| PUT | `/notifications/read-all` | 전체 읽음 |

### Dashboard
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/dashboard/summary` | 요약 (활성 케이스, 요청중, 팔로업, 오버듀, 에스컬레이션 카운트) |
| GET | `/dashboard/overdue` | 오버듀 항목 |
| GET | `/dashboard/followup-due` | 팔로업 필요 항목 |
| GET | `/dashboard/escalations` | 에스컬레이션 항목 (역할별 필터링, 알림 자동 생성) |

### Users (Admin)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/users` | 사용자 목록 |
| POST | `/users` | 사용자 생성 |
| PUT | `/users/{id}` | 사용자 수정 |
| PUT | `/users/{id}/toggle-active` | 활성/비활성 |
| PUT | `/users/{id}/reset-password` | 비밀번호 초기화 |

### Activity Log
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/activity-log` | 감사 로그 |

### Tracker
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tracker/list` | 전체 요청 추적 |

---

## Key Features

### 1. Case Management
- 케이스 CRUD (번호, 의뢰인, DOB, DOI, 변호사, 담당자)
- 상태 관리: Active → Pending Review → Completed / On Hold
- CSV 가져오기/내보내기

### 2. Provider Management
- 프로바이더 CRUD (병원, ER, 카이로, 영상, 의사, 수술센터, 약국 등)
- 연락처, 선호 방법, 난이도, 평균 응답 일수 추적
- 제3자 대행 정보 관리
- CSV 가져오기/내보내기

### 3. Record Request Workflow
```
프로바이더 추가 → 요청 생성 (Initial)
    ↓
요청서 자동 생성 (Letter Template)
    ↓
이메일/팩스 미리보기 → 발송
    ↓
14일 후 팔로업 알림 → Follow-up 요청
    ↓
기록 수신 → 확인 (Verify)
```

### 4. Letter Template
- 법률사무소 레터헤드
- 환자/케이스 정보 자동 입력
- 요청 기록 유형 목록
- 치료 기간
- HIPAA 고지
- HTML 형식 (이메일/팩스 겸용)

### 5. Deadline & Follow-up System
| 항목 | 기간 | 동작 |
|------|------|------|
| Default Deadline | 30일 | 프로바이더 추가 시 자동 설정 |
| Follow-up Reminder | 14일 | 마지막 요청 후 14일에 알림 |
| Deadline Warning | 7일 전 | 데드라인 7일 전 경고 |
| Deadline Overdue | 경과 | 오버듀 알림 |

### 6. Escalation System
| Tier | 기간 (첫 요청일 기준) | 대상 | 색상 |
|------|----------------------|------|------|
| Normal | 0~29일 | - | Gray |
| Action Needed | 30일+ | 담당 직원 | Yellow (#d97706) |
| Manager Review | 42일+ (6주) | Manager 전원 | Orange (#ea580c) |
| Admin Escalation | 60일+ (2달) | Admin 전원 | Red (#dc2626) |

- 대시보드 접속 시 자동 알림 생성 (당일 중복 방지)
- 앱 내 알림 (알림벨 색상 구분)
- 이메일 알림 (사용자에 email 설정 시)
- Admin 에스컬레이션은 pulse 애니메이션

### 7. Deadline Change Audit
- 데드라인 변경 시 사유 필수 입력 (5자 이상)
- `deadline_changes` 테이블에 이력 저장
- 모달에서 변경 이력 확인 가능

### 8. Activity Log (Notes)
- 케이스별 노트 관리
- 프로바이더 연결 가능
- 연락 방법 + 일시 기록
- 유형: General, Follow-Up, Issue, Handoff
- 프로바이더별 필터링

### 9. Notification System (3-Layer)
1. **Dashboard** - 에스컬레이션 배너, 오버듀/팔로업 섹션
2. **Notification Bell** - 실시간 카운트, 에스컬레이션 컬러 배지
3. **Email** - 에스컬레이션 레벨별 HTML 이메일

### 10. Admin Features
- 사용자 관리 (생성, 수정, 활성/비활성, 비밀번호 초기화)
- 역할 관리 (Admin, Manager, Staff)
- 활동 로그 조회
- 데이터 관리 (CSV import/export)

---

## Frontend Pages

| Page | URL | Description |
|------|-----|-------------|
| Login | `/frontend/pages/auth/login.php` | 로그인 |
| Dashboard | `/frontend/pages/dashboard/index.php` | 대시보드 (요약 + 에스컬레이션 + 오버듀 + 팔로업) |
| Cases List | `/frontend/pages/cases/index.php` | 케이스 목록 |
| Case Detail | `/frontend/pages/cases/detail.php?id={id}` | 케이스 상세 (프로바이더 + 요청 이력 + 노트) |
| Case Create | `/frontend/pages/cases/create.php` | 케이스 생성 |
| Providers List | `/frontend/pages/providers/index.php` | 프로바이더 목록 |
| Provider Detail | `/frontend/pages/providers/detail.php?id={id}` | 프로바이더 상세 |
| Provider Create | `/frontend/pages/providers/create.php` | 프로바이더 생성 |
| Records Tracker | `/frontend/pages/tracker/index.php` | 전체 요청 추적 |
| Reports | `/frontend/pages/reports/index.php` | 리포트 (Coming Soon) |
| Users | `/frontend/pages/admin/users.php` | 사용자 관리 (Admin) |
| Activity Log | `/frontend/pages/admin/activity-log.php` | 활동 로그 (Admin) |
| Data Management | `/frontend/pages/admin/data-management.php` | CSV 가져오기/내보내기 (Admin) |

---

## Helper Functions

### DB (`helpers/db.php`)
- `dbQuery($sql, $params)` - Prepared statement 실행
- `dbFetchAll($sql, $params)` - 전체 행 조회
- `dbFetchOne($sql, $params)` - 단일 행 조회
- `dbInsert($table, $data)` - 삽입 (lastInsertId 반환)
- `dbUpdate($table, $data, $where, $params)` - 업데이트
- `dbDelete($table, $where, $params)` - 삭제
- `dbCount($table, $where, $params)` - 카운트
- `logActivity($userId, $action, $entityType, $entityId, $details)` - 감사 로그

### Auth (`helpers/auth.php`)
- `requireAuth()` - 인증 필수 (미인증 시 401)
- `requireAdmin()` - Admin 역할 필수 (비 Admin 시 403)
- `getCurrentUser()` - 현재 사용자 정보
- `generateCSRFToken()` / `validateCSRFToken()` - CSRF 보호

### Response (`helpers/response.php`)
- `successResponse($data, $message)` - 성공 JSON
- `errorResponse($message, $statusCode)` - 에러 JSON
- `paginatedResponse($data, $total, $page, $perPage)` - 페이지네이션 JSON

### Validator (`helpers/validator.php`)
- `getInput()` - JSON/Form 입력 파싱
- `validateRequired($data, $fields)` - 필수 필드 검증
- `sanitizeString($value)` - 문자열 정제
- `validateDate($date)` - 날짜 형식 검증 (Y-m-d)
- `validateEnum($value, $allowed)` - 열거형 검증
- `getPaginationParams()` - 페이지네이션 파라미터

### Date (`helpers/date.php`)
- `calculateNextFollowup()` - 14일 후 날짜
- `calculateDeadline()` - 30일 후 날짜
- `daysElapsed($fromDate)` - 경과 일수
- `daysUntil($targetDate)` - 남은 일수
- `isOverdue($deadline)` - 오버듀 여부
- `isDeadlineWarning($deadline)` - 7일 이내 경고

### Escalation (`helpers/escalation.php`)
- `getEscalationTier($days)` - 에스컬레이션 단계 (normal/action_needed/manager/admin)
- `getEscalationInfo($days)` - 단계 정보 (tier/label/css)
- `generateEscalationNotifications()` - 알림 + 이메일 자동 생성
- `sendEscalationEmail(...)` - 에스컬레이션 HTML 이메일
- `getEscalatedItems($role, $userId)` - 역할별 에스컬레이션 목록

### Email/Fax
- `sendEmail($to, $subject, $html, $options)` - PHPMailer SMTP 발송
- `sendFax($toNumber, $html)` - Phaxio 팩스 발송
- `renderRequestLetter($data)` - 요청서 HTML 생성
- `getRequestLetterData($requestId)` - 요청서 데이터 조회

---

## Configuration Constants

### App (`config/app.php`)
```
DEFAULT_FOLLOWUP_DAYS = 14       # 팔로업 주기
DEADLINE_WARNING_DAYS = 7        # 데드라인 경고
DEFAULT_DEADLINE_DAYS = 30       # 기본 데드라인
ESCALATION_ACTION_NEEDED_DAYS = 30   # Action Needed
ESCALATION_MANAGER_DAYS = 42        # Manager 에스컬레이션
ESCALATION_ADMIN_DAYS = 60          # Admin 에스컬레이션
ITEMS_PER_PAGE = 20              # 페이지당 항목수
Timezone = America/New_York
```

### Email/Fax (`config/email.php`)
```
SMTP: Gmail (smtp.gmail.com:587, TLS)
Fax: Phaxio API
Firm: Bridge Law & Associates
```

---

## Security

- **Authentication:** Session 기반 (8시간 lifetime)
- **Password:** bcrypt 해시 (`password_verify`)
- **SQL Injection:** PDO Prepared Statements
- **CSRF:** Token 생성/검증
- **Role-based Access:** Admin / Manager / Staff
- **API Auth:** 모든 API 엔드포인트에 `requireAuth()` 적용

---

## Cron Jobs

```bash
# 매일 실행 - 알림 생성 (팔로업, 데드라인 경고, 오버듀)
php /path/to/MRMS/backend/cron/generate_notifications.php

# 매일 실행 - 프로바이더 통계 업데이트
php /path/to/MRMS/backend/cron/update_statistics.php
```

> Note: 에스컬레이션 알림은 대시보드 접속 시 on-demand 생성 (cron 불필요)

---

## User Roles

| Role | Permissions |
|------|------------|
| **Admin** | 모든 기능 + 사용자 관리 + 활동 로그 + 데이터 관리 + 60일+ 에스컬레이션 수신 |
| **Manager** | 케이스/프로바이더 관리 + 42일+ 에스컬레이션 수신 |
| **Staff** | 케이스/프로바이더 관리 + 본인 배정 항목 + 30일+ 에스컬레이션 수신 |
