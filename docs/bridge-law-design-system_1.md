# Bridge Law & Associates — MRMS UI 디자인 시스템 프롬프트

## 개요
Bridge Law & Associates의 내부 법무 관리 시스템(MRMS)용 UI를 만들 때 이 디자인 시스템을 따릅니다.
전체 톤은 **차분하고 professional한 법무 환경**에 맞게, 컬러풀하지 않으면서도 구조가 명확하게 보여야 합니다.

---

## 색상 팔레트 (Color Tokens)

```css
:root {
  /* Primary — Navy */
  --navy:         #0F1B2D;   /* 메인 브랜드 색, 헤더/버튼/타이틀  (navy.DEFAULT) */
  --navy-light:   #1A2A40;   /* 카드 hover, 보조 배경              (navy.light) */
  --navy-border:  #243347;   /* navy 영역 안 border                (navy.border) */

  /* Accent — Gold */
  --gold:         #C9A84C;   /* 네비 하단 보더, 배지, 중요 금액 강조 (gold.DEFAULT) */
  --gold-hover:   #B8973F;   /* gold hover state                   (gold.hover) */

  /* Accent — Warm Linen */
  --linen:        #E5E5E0;   /* 섹션 헤더 배경, 테이블 헤더, 하단 바 */
  --linen-dark:   #C8C8C2;   /* linen 요소의 border */
  --linen-text:   #5a5a54;   /* linen 배경 위 텍스트 */

  /* Neutrals */
  --white:        #ffffff;
  --bg:           #f2f2ee;   /* 페이지 배경 — 따뜻한 오프화이트 */
  --border:       #ddddd8;   /* 일반 border */
  --muted:        #8a8a82;   /* 보조 텍스트, 라벨 */
  --text:         #1a2535;   /* 본문 텍스트 */

  /* Status Colors — 최소한으로만 사용 */
  --red:          #b83232;   /* balance 높음, 경고, Attorney Review */
  --green:        #2a6b4a;   /* balance 0, 완료 */
}
```

**색상 사용 원칙:**
- 컬러는 **3가지만** (Navy, Gold, Linen). 나머지는 중립색
- 빨강/초록은 **데이터 상태 표시 전용** (balance 금액, 경고)으로만 사용
- 배경에 강한 색 절대 금지. 색은 border-top, 텍스트 강조에만 포인트로 사용

---

## 타이포그래피

```css
/* 본문 */
font-family: 'IBM Plex Sans', sans-serif;
font-size: 13px;

/* 숫자 (금액, 날짜, ID) */
font-family: 'IBM Plex Mono', monospace;

/* Google Fonts import */
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap');
```

**타이포 규칙:**
- 레이블은 항상 `font-size: 9–10px`, `font-weight: 700`, `letter-spacing: 0.08–0.1em`, `text-transform: uppercase`
- 금액은 항상 IBM Plex Mono, `text-align: right`
- 0인 숫자는 `color: #c5c5be` (흐리게 처리해서 시각적 노이즈 제거)

---

## 레이아웃 구조

**패턴 A — 전체 페이지 (대시보드, 목록 페이지 등)**
```
[Sticky Top Nav]
  └─ Navy background + Gold 2px bottom border
  └─ Gold 배지 (회사명) + 흰색 페이지 타이틀

[Main — max-width: 1280px, padding: 28px 24px]
  ├─ Case Header (제목 + 케이스 정보 + 상태 배지)
  ├─ Summary Cards Row (5-grid)
  └─ Card(s)
       ├─ Card Header (Linen bg + Section Label)
       ├─ Card Body
       └─ Bottom Bar (Linen bg + Notes + Buttons)
```

**패턴 B — 인라인 Report Card (케이스 상세 안에 embed된 형태)**
```
[Report Card]
  border: 1px solid var(--border)
  border-radius: 10px
  box-shadow: 0 1px 4px rgba(0,0,0,0.06)
  overflow: hidden

  ├─ Report Header Bar
  │    background: var(--navy)
  │    border-bottom: 2px solid var(--gold)
  │    padding: 14px 20px
  │    └─ 왼쪽: [collapse ▾] + [제목] + [Draft 배지]
  │    └─ 오른쪽: [Print 버튼]
  │
  ├─ Section Block — Insurance Settings
  │    ├─ Section Label Bar  (Linen bg + linen-text + 아이콘)
  │    └─ Insurance Body     (White bg, padding 16px 20px)
  │         ├─ Fields Row: 5-grid inputs
  │         └─ Checkbox Row
  │
  └─ Section Block — Table
       ├─ Table
       │    ├─ grp-head row  (Linen bg)
       │    ├─ col-head row  (Linen bg + 2px border-bottom)
       │    ├─ sec-row       (Linen bg, 섹션 구분)
       │    ├─ data rows     (White bg, hover: #eaeae5)
       │    └─ tfoot         (Navy bg)
       └─ Bottom Bar         (Linen bg)
            ├─ Report Notes textarea
            └─ Button row: [+ Add RX] [+ Add Line] [Mark Complete ★]
```

---

## 컴포넌트 스펙

### Report Header Bar (패턴 B 전용)
```css
background: var(--navy);
border-bottom: 2px solid var(--gold);
padding: 14px 20px;
display: flex; align-items: center; justify-content: space-between;
```
- **Collapse 버튼**: `color: rgba(255,255,255,0.5)`, hover시 `rgba(255,255,255,0.9)`
- **제목**: `font-size: 15px`, `font-weight: 600`, `color: white`
- **Draft 배지**: `background: rgba(201,168,76,0.2)`, `color: var(--gold)`, `border: 1px solid rgba(201,168,76,0.35)`
- **Print 버튼**: `border: 1px solid var(--navy-border)`, `color: rgba(255,255,255,0.55)`, ghost 스타일

---

### Section Label Bar
```css
background: var(--linen);
border-bottom: 1px solid var(--linen-dark);
padding: 9px 20px;
display: flex; align-items: center; gap: 8px;
```
- 아이콘: `color: var(--linen-text)`, `opacity: 0.7`
- 텍스트: `font-size: 10px`, `font-weight: 700`, `letter-spacing: 0.1em`, `text-transform: uppercase`, `color: var(--linen-text)`

---

### Insurance Settings
```css
/* Fields Row */
display: grid;
grid-template-columns: repeat(5, 1fr);
gap: 12px;
margin-bottom: 14px;

/* Input */
border: 1px solid var(--border);
border-radius: 5px;
padding: 7px 10px;
background: var(--bg);
font-size: 13px;
/* focus: border-color: var(--navy-border); background: white; */
/* placeholder: color: var(--muted); font-style: italic; */
```

---

### Checkbox
```css
/* 기본 */
width: 15px; height: 15px;
border: 1.5px solid var(--border);
border-radius: 3px;
background: var(--white);

/* 체크됨 */
background: var(--navy);
border-color: var(--navy);
/* ::after: content '✓', color white, font-size 9px */

/* 라벨 텍스트 */
font-size: 12px;
color: var(--muted);  /* 미선택 */
color: var(--text); font-weight: 500;  /* 선택됨 */
```

---

### Top Nav (패턴 A 전용)
```css
background: var(--navy);
height: 50px;
border-bottom: 2px solid var(--gold);
position: sticky; top: 0; z-index: 100;
padding: 0 28px;
```
- 회사 배지: `background: var(--gold)`, `color: white`, `border-radius: 3px`, `font-size: 10px`, uppercase
- 페이지 타이틀: `color: rgba(255,255,255,0.5)`, `letter-spacing: 0.1em`, uppercase

---

### Summary Cards (패턴 A 전용)
```css
display: grid;
grid-template-columns: repeat(5, 1fr);
gap: 10px;
```
각 카드:
```css
background: var(--white);
border: 1px solid var(--border);
border-radius: 8px;
padding: 14px 16px;
border-top: 3px solid [카테고리별 색];
```
| 카드 | border-top | 금액 색 |
|---|---|---|
| Total Charges | `--navy` | `--navy` |
| PIP #1 | `--linen-dark` | `--linen-text` |
| Health #1 | `--linen-dark` | `--linen-text` |
| Discount | `--muted` | `--navy` |
| Balance Due | `--gold` | `--gold` |

---

### 상태 배지 (Badge)
```css
font-size: 10px; font-weight: 600;
padding: 2px 9px; border-radius: 3px;
letter-spacing: 0.06em; text-transform: uppercase;
```
| 타입 | background | color | border |
|---|---|---|---|
| Draft | `rgba(201,168,76,0.2)` | `--gold` | `rgba(201,168,76,0.35)` |
| In Review | `#E5E5E0` | `#5a5a54` | `#c8c8c2` |
| Complete | `#e8f5ee` | `#1e6645` | `#a8d5bc` |

---

### 데이터 테이블

**헤더 구조: 2-row (그룹 헤더 + 컬럼 헤더)**

```css
/* 그룹 헤더 row */
background: var(--linen);
font-size: 9px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
color: var(--muted);
padding: 7px 14px 5px;
border-bottom: 1px solid var(--linen-dark);
/* 그룹 span th: border-bottom: 2px solid var(--linen-dark); color: var(--linen-text); */

/* 컬럼 헤더 row */
background: var(--linen);
font-size: 10px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;
color: var(--navy);
padding: 8px 14px;
border-bottom: 2px solid var(--linen-dark);
/* Balance 헤더: color: var(--gold); */

/* 섹션 구분 row */
background: var(--linen);
color: var(--linen-text);
font-size: 9px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
padding: 5px 14px;
border-top: 1px solid var(--linen-dark);
border-bottom: 1px solid var(--linen-dark);

/* 일반 데이터 row */
border-bottom: 1px solid #e8e8e3;
/* hover: background: #eaeae5; */
padding: 10px 14px;

/* tfoot (Total row) */
background: var(--navy);
color: rgba(255,255,255,0.7);
/* 라벨: color: rgba(255,255,255,0.35); text-align: left; uppercase */
/* Balance 금액: color: var(--gold); font-size: 14px; font-weight: 600; */
```

**Balance 컬럼 색상 규칙:**
```
$0          → color: var(--green)    /* 완납 */
$1~$4,999   → color: var(--gold)     /* 잔액 있음 */
$5,000+     → color: var(--red)      /* 높은 잔액, 주의 필요 */
```

**Note 컬럼:**
```
일반   → color: var(--muted)
⚠ 경고 → color: var(--red); font-weight: 500;
```

---

### Bottom Bar
```css
background: var(--linen);
border-top: 1px solid var(--linen-dark);
padding: 16px 20px;
display: flex; gap: 16px; align-items: flex-start;
```

Notes textarea:
```css
border: 1px solid var(--linen-dark);
border-radius: 5px;
padding: 8px 12px;
background: var(--white);
font-size: 13px;
height: 68px;
/* focus: border-color: var(--navy-border); */
/* placeholder: color: var(--muted); font-style: italic; */
```

버튼:
```css
padding: 8px 16px; border-radius: 5px;
font-size: 12px; font-weight: 600;
font-family: inherit;

/* Ghost — 보조 액션 (+ Add RX, + Add Line) */
background: transparent;
border: 1px solid var(--linen-dark);
color: var(--linen-text);
/* hover: background: var(--linen-dark); */

/* Primary — Navy (일반 확인 액션) */
background: var(--navy);
color: var(--white);
/* hover: background: var(--navy-light); */

/* Gold CTA — 가장 중요한 액션 (Mark Complete) */
background: var(--gold);
color: var(--white);
/* hover: background: var(--gold-hover); */
```

---

## 디자인 원칙 요약

1. **색은 구조를 위해** — Linen은 항상 헤더/구분 영역. Gold는 항상 중요 금액/강조/CTA. Navy는 항상 primary container/타이틀
2. **숫자는 Mono 폰트** — 금액, 날짜, ID 등 모든 숫자는 IBM Plex Mono로 오른쪽 정렬
3. **0은 흐리게** — 값이 없는 셀은 `#c5c5be`로 처리해 시각적 노이즈 제거
4. **라벨은 항상 uppercase + tracking** — 모든 섹션 라벨, 컬럼 헤더는 소문자 금지
5. **border-radius 일관성** — 카드: `10px` / 인풋·버튼: `5px` / 배지: `3px`
6. **max-width: 1280px** — 모든 페이지 최대 너비 통일, padding: 28px 24px
7. **컬러풀 금지** — 파란색, 보라색, 주황색 등 화려한 색 사용 금지. 상태 표시는 red/green만
8. **버튼 계층** — Ghost(보조) → Navy(일반) → Gold(핵심 CTA) 순서로 중요도 구분
9. **섹션 구분은 Linen** — 같은 카드 안에서 섹션을 나눌 때는 항상 Linen 배경 바 사용
