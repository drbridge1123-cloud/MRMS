# MRMS Project Notes

## TODO: Faxage.com eFax 연동

### 배경
- 현재 fax는 Phaxio API 사용 (backend/helpers/fax.php) — HTML을 보내면 Phaxio가 PDF 변환
- 실제로는 faxage.com을 통해 eFax를 보내고 있음 → API 연동 필요
- DomPDF로 HTML→PDF 변환 시스템 이미 완성됨 (backend/helpers/pdf-generator.php)

### 구현 계획
1. **backend/config/email.php** — Faxage 계정 정보 추가
   ```php
   define('FAX_SERVICE', 'faxage');
   define('FAXAGE_USERNAME', '');
   define('FAXAGE_COMPANY', '');
   define('FAXAGE_PASSWORD', '');
   ```

2. **backend/helpers/fax.php** — `sendFaxViaFaxage()` 함수 추가
   - Faxage API: `POST https://www.faxage.com/httpsfax.php`
   - 파라미터: username, company, password, operation=sendfax, faxno, faxfilenames[], faxfiledata[] (base64)
   - 상태 확인: operation=status
   - PDF는 이미 DomPDF로 생성됨 → base64로 인코딩해서 전송

3. **backend/api/requests/send.php** — fax 경로에서 PDF 첨부 전달
   - 현재: `sendFax($recipient, $html)` (HTML만 전달)
   - 변경: `sendFax($recipient, $html, ['pdf_path' => $letterPdfPath, 'attachments' => $attachments])`
   - Faxage는 HTML 대신 PDF 파일을 직접 전송

4. **bulk-send.php** — 동일하게 수정

### 관련 파일
- `backend/helpers/fax.php` — fax 전송 로직 (switch문에 'faxage' case 추가)
- `backend/helpers/pdf-generator.php` — HTML→PDF 변환 (완성됨)
- `backend/config/email.php` — SMTP + FAX 설정
- `backend/api/requests/send.php` — 단건 전송
- `backend/api/requests/bulk-send.php` — 대량 전송
