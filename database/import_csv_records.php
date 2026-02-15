<?php
/**
 * Import CSV records into MRMS database.
 * Usage: php database/import_csv_records.php [--dry-run]
 * Or browser: http://localhost/MRMS/database/import_csv_records.php?dry_run=1
 */

$isCli = php_sapi_name() === 'cli';
$dryRun = $isCli ? in_array('--dry-run', $argv ?? []) : isset($_GET['dry_run']);

if (!$isCli) echo '<pre>';

require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/helpers/db.php';

$pdo = getDBConnection();
$csvFile = __DIR__ . '/../Medical Records Request from Providers 1.csv';

if (!file_exists($csvFile)) {
    die("CSV file not found: $csvFile\nPlace the CSV in the MRMS root directory.\n");
}

// ─── Provider Alias Map ───
$providerAliases = [
    'lynnwood chiro' => 'Lynnwood Chiropractic',
    'lynnwood chiropractic' => 'Lynnwood Chiropractic',
    'lynnwood chiro - hold' => 'Lynnwood Chiropractic',
    'lynnwood chiro - hold - return treatment' => 'Lynnwood Chiropractic',
    'kairos chiro' => 'Kairos Chiropractic',
    'bothell chiro' => 'Bothell Chiropractic',
    'seattle chiro' => 'Seattle Chiropractic',
    'seattle chiropractic' => 'Seattle Chiropractic',
    'dynamic chiro' => 'Dynamic Chiropractic',
    'kore chiro' => 'Kore Chiropractic',
    'good dr. chiro' => 'Good Dr. Chiropractic',
    'hesed chiropractic - renton' => 'Hesed Chiropractic',
    'lin\'s therapeutic massage' => 'Lin\'s Therapeutic Massage',
    'lin\'s therapeutic massage - hold' => 'Lin\'s Therapeutic Massage',
    'lins\'s massage' => 'Lin\'s Therapeutic Massage',
    'lin\'s massage' => 'Lin\'s Therapeutic Massage',
    'er radiology (tra)' => 'TRA',
    'tra' => 'TRA',
    'wapass' => 'WaPass Pain and Spine',
    'wapass pain and spine' => 'WaPass Pain and Spine',
    'irg pt' => 'IRG Physical Therapy',
    'ipg' => 'IPG',
    'core pt' => 'Core Physical Therapy',
    'therapeutic associated pt' => 'Therapeutic Associates PT',
    'bmi' => 'BMI',
    'nw er phy' => 'NW ER Physicians',
    'auto injury' => 'Auto Injury Pain Clinic',
    'auto injury pain clinic' => 'Auto Injury Pain Clinic',
    'skagit valley' => 'Skagit Valley Hospital',
    'request balance verification - skiagit reginal health' => 'Skagit Regional Health',
    'skagit regional health - missing bill for 08/13/25, 6/19/25, 6/10/25' => 'Skagit Regional Health',
    'pacific mental health' => 'Pacific Mental Health',
    'swedish medical center' => 'Swedish Medical Center',
    'swedish medical center billing' => 'Swedish Medical Center',
    'swedish er - request pip file' => 'Swedish ER',
    'swedish edmonds specialty clinic' => 'Swedish Edmonds Specialty Clinic',
    'cep america' => 'CEP America',
    'evergreen health' => 'Evergreen Health',
    'evergreen health er' => 'Evergreen Health ER',
    'evergreen er phy' => 'Evergreen ER Physicians',
    'evergreen er service' => 'Evergreen ER Service',
    'everett clinic' => 'Everett Clinic',
    'valley medical center - records' => 'Valley Medical Center',
    'uw medicine - pcp' => 'UW Medicine PCP',
    'uw billing' => 'UW Billing',
    'multicare urgent care - kent' => 'MultiCare Urgent Care Kent',
    'multicare - pcp' => 'MultiCare PCP',
    'kinwell primary care - renton' => 'Kinwell Primary Care Renton',
    'overlake medical clinic - urgent care' => 'Overlake Medical Clinic Urgent Care',
    'overlake medical clinic- urgent care' => 'Overlake Medical Clinic Urgent Care',
    'brain injury medicine of seattle' => 'Brain Injury Medicine of Seattle',
    'seattle children\'s north clinic' => 'Seattle Children\'s North Clinic',
    'hanmi medical center' => 'Hanmi Medical Center',
    'active sports & spine - hold' => 'Active Sports & Spine',
    'vantage radiology' => 'Vantage Radiology',
    'electrodiagnosis & rehab associates - tacoma' => 'Electrodiagnosis & Rehab Associates',
    'dr. frandanisa' => 'Dr. Frandanisa',
    'wellness acupuncture' => 'Wellness Acupuncture',
    'lifestyle medicine at renton for pt (faxed to valley m. center)' => 'Lifestyle Medicine Renton',
    'alona wellness center' => 'Alona Wellness Center',
    'lynnwood pain management' => 'Lynnwood Pain Management',
    'marysville fire dept' => 'Marysville Fire Dept',
    'snohomish county fpd 21' => 'Snohomish County FPD 21',
    'visionworks (425)386-8428' => 'Visionworks',
    'wwmg (western wa. medical group)' => 'WWMG',
    'providence prov home oxygen & medical equip' => 'Providence Home Oxygen & Medical Equipment',
    'orthopedic, sports & pine & hand center (11/28/23 - present)' => 'Orthopedic Sports & Hand Center',
    '8+1 healthcare' => '8+1 Healthcare',
    'radia' => 'Radia',
];

// ─── Provider names to skip (not real providers) ───
$providerSkipList = [
    'request record & billing for evergreen radia & radia from allstate',
];

// ─── VIA method map ───
$methodMap = [
    'email' => 'email',
    'e-mail' => 'email',
    'fax' => 'fax',
    'phone' => 'phone',
    'ph' => 'phone',
    'chartswap' => 'chartswap',
    'online' => 'online',
    'portal' => 'portal',
    'mail' => 'mail',
];

// ─── Stats ───
$stats = [
    'cases_created' => 0, 'cases_existing' => 0,
    'providers_created' => 0, 'providers_existing' => 0,
    'case_providers_created' => 0, 'requests_created' => 0,
    'notes_created' => 0, 'rows_parsed' => 0, 'rows_skipped' => 0,
];
$warnings = [];

// ─── Helper: parse date ───
function parseDate($str) {
    $str = trim($str);
    if (!$str || $str === '0/0/0000') return null;
    // Try m/d/Y first, then m/d/y (2-digit year)
    $d = DateTime::createFromFormat('n/j/Y', $str);
    if ($d && $d->format('n/j/Y') === $str) return $d->format('Y-m-d');
    $d = DateTime::createFromFormat('n/j/y', $str);
    if ($d) return $d->format('Y-m-d');
    // Fallback
    $ts = strtotime($str);
    return $ts ? date('Y-m-d', $ts) : null;
}

// ─── Helper: parse treatment status ───
function parseTreatmentStatus($val) {
    $val = trim($val);
    if ($val === '' || $val === '0/0/0000') return [null, null, null];

    $upper = strtoupper($val);
    // INI with optional extra text
    if (preg_match('/^INI\s*/i', $val)) {
        $extra = trim(preg_replace('/^INI\s*\/?\.?\s*/i', '', $val));
        return ['in_treatment', null, $extra ?: null];
    }
    if ($upper === 'NEG') return ['neg', null, null];
    if ($upper === 'RFD') return ['rfd', null, null];

    // Try parsing as date → treatment_done
    $date = parseDate($val);
    if ($date) return ['treatment_done', $date, null];

    return [null, null, null];
}

// ─── Helper: normalize provider name ───
function normalizeProvider($name, $aliases) {
    $name = trim($name);
    if (!$name) return [null, false, null];

    // Detect HOLD in provider name
    $isHold = false;
    if (preg_match('/\s*-\s*HOLD\b/i', $name)) {
        $isHold = true;
    }

    $key = mb_strtolower(trim(preg_replace('/\s*-\s*HOLD\b.*$/i', '', $name)));
    $key = trim($key);

    $canonical = $aliases[$key] ?? null;
    if (!$canonical) {
        // Try without trailing whitespace variations
        $key2 = rtrim($key);
        $canonical = $aliases[$key2] ?? ucwords($key2);
    }

    return [$canonical, $isHold, null];
}

// ─── Helper: calculate deadline ───
function calcDeadline($treatmentStatus, $treatmentEndDate) {
    if ($treatmentStatus === 'treatment_done' && $treatmentEndDate) {
        $d = new DateTime($treatmentEndDate);
        $d->modify('+30 days');
        return $d->format('Y-m-d');
    }
    if ($treatmentStatus === 'rfd') {
        $d = new DateTime();
        $d->modify('+14 days');
        return $d->format('Y-m-d');
    }
    return null;
}

out("=== MRMS CSV Import " . ($dryRun ? '(DRY RUN)' : '') . " ===\n");

// ─── PHASE 0: Ensure users exist ───
out("\n--- Phase 0: Users ---\n");

$userMap = [];
$usersNeeded = [
    'Miki' => 'staff',
    'Ella' => 'admin',
    'Jimi' => 'staff',
];

foreach ($usersNeeded as $name => $role) {
    $user = dbFetchOne("SELECT id, full_name FROM users WHERE LOWER(full_name) = LOWER(?) OR LOWER(username) = LOWER(?)", [$name, $name]);
    if ($user) {
        $userMap[strtolower($name)] = (int)$user['id'];
        out("  User '{$name}' found (id={$user['id']})\n");
    } else {
        // Also check for 'micky' → Miki
        if (strtolower($name) === 'miki') {
            $user = dbFetchOne("SELECT id FROM users WHERE LOWER(username) = 'micky'");
            if ($user) {
                if (!$dryRun) {
                    dbUpdate('users', ['full_name' => 'Miki'], 'id = ?', [$user['id']]);
                }
                $userMap['miki'] = (int)$user['id'];
                out("  User 'micky' renamed to 'Miki' (id={$user['id']})\n");
                continue;
            }
        }
        if (!$dryRun) {
            $id = dbInsert('users', [
                'username' => strtolower($name),
                'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                'full_name' => $name,
                'role' => $role,
            ]);
            $userMap[strtolower($name)] = (int)$id;
            out("  User '{$name}' created (id={$id})\n");
        } else {
            $userMap[strtolower($name)] = 0;
            out("  User '{$name}' would be created\n");
        }
    }
}

// ─── Build provider map from DB ───
$providerMap = [];
$dbProviders = $pdo->query("SELECT id, name FROM providers")->fetchAll(PDO::FETCH_ASSOC);
foreach ($dbProviders as $p) {
    $providerMap[mb_strtolower(trim($p['name']))] = (int)$p['id'];
}

// ─── Build case map from DB ───
$caseMap = [];
$dbCases = $pdo->query("SELECT id, case_number FROM cases")->fetchAll(PDO::FETCH_ASSOC);
foreach ($dbCases as $c) {
    $caseMap[trim($c['case_number'])] = (int)$c['id'];
}

// ─── PHASE 1: Parse CSV into groups ───
out("\n--- Phase 1: Parsing CSV ---\n");

$handle = fopen($csvFile, 'r');
// Skip BOM if present
$bom = fread($handle, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($handle);
}

$header = fgetcsv($handle); // row 1: header
$groups = [];
$currentGroup = null;
$rowNum = 1;

while (($row = fgetcsv($handle)) !== false) {
    $rowNum++;

    // Skip legend rows (rows 2-5) and empty rows
    $name = trim($row[1] ?? '');
    $caseNum = trim($row[2] ?? '');
    $provider = trim($row[3] ?? '');
    $allEmpty = empty($name) && empty($caseNum) && empty($provider) && empty(trim($row[4] ?? ''));

    if ($allEmpty) {
        $stats['rows_skipped']++;
        continue;
    }

    // Skip legend rows: check for "Hong, Gil Dong" or "BR = " or "BV=" patterns
    if (strpos($name, 'Hong, Gil Dong') !== false || strpos($provider, 'BR = ') !== false
        || strpos($provider, 'BV=') !== false || ($rowNum <= 5 && empty($name) && empty($caseNum))) {
        $stats['rows_skipped']++;
        continue;
    }

    $stats['rows_parsed']++;

    $rowData = [
        'row_num' => $rowNum,
        'treatment_raw' => trim($row[0] ?? ''),
        'name' => $name,
        'case_number' => $caseNum,
        'provider' => $provider,
        'via' => strtolower(trim($row[4] ?? '')),
        'req_date' => trim($row[5] ?? ''),
        'by' => trim($row[6] ?? ''),
        'followups' => [
            trim($row[7] ?? ''),
            trim($row[8] ?? ''),
            trim($row[9] ?? ''),
            trim($row[10] ?? ''),
        ],
        'note' => trim($row[11] ?? ''),
    ];

    // Parent row: has NAME and CASE
    if (!empty($name) && !empty($caseNum)) {
        if ($currentGroup) {
            $groups[] = $currentGroup;
        }
        $currentGroup = ['parent' => $rowData, 'children' => []];
    } elseif (!empty($name) && empty($caseNum) && $currentGroup) {
        // Name but no case — sub-client sharing same case (e.g. Quam, Daoura)
        // Treat as new parent inheriting case from previous
        $groups[] = $currentGroup;
        $rowData['case_number'] = $currentGroup['parent']['case_number'];
        $rowData['treatment_raw'] = $rowData['treatment_raw'] ?: $currentGroup['parent']['treatment_raw'];
        $currentGroup = ['parent' => $rowData, 'children' => []];
    } elseif (empty($name) && !empty($provider)) {
        // Child row: additional provider for current group
        if ($currentGroup) {
            $currentGroup['children'][] = $rowData;
        } else {
            $warnings[] = "Row {$rowNum}: orphan child row, no parent group — skipped";
        }
    } else {
        $stats['rows_skipped']++;
    }
}
if ($currentGroup) {
    $groups[] = $currentGroup;
}
fclose($handle);

out("  Parsed {$stats['rows_parsed']} data rows into " . count($groups) . " case groups\n");
out("  Skipped {$stats['rows_skipped']} empty/legend rows\n");

// ─── PHASE 2: Insert into DB ───
out("\n--- Phase 2: Database Insert " . ($dryRun ? '(DRY RUN — no writes)' : '') . " ---\n");

foreach ($groups as $gi => $group) {
    $parent = $group['parent'];
    $allRows = array_merge([$parent], $group['children']);

    // Parse client name — remove HOLD
    $clientName = $parent['name'];
    $clientHold = false;
    if (preg_match('/\s*-\s*HOLD\b/i', $clientName)) {
        $clientHold = true;
        $clientName = trim(preg_replace('/\s*-\s*HOLD\b/i', '', $clientName));
    }

    // Remove extra notes from client name (e.g. "Poindexter, Alan (Need Attorney Review...)")
    $clientNotes = null;
    if (preg_match('/^([^(]+)\((.+)\)\s*$/', $clientName, $m)) {
        $clientName = trim($m[1]);
        $clientNotes = trim($m[2]);
    }

    // Parse treatment status
    [$treatmentStatus, $treatmentEndDate, $treatmentExtra] = parseTreatmentStatus($parent['treatment_raw']);

    // Parse case numbers (may be "202040 & 202043")
    $caseNumbers = array_map('trim', explode('&', $parent['case_number']));

    // Determine deadline
    $deadline = calcDeadline($treatmentStatus, $treatmentEndDate);

    // Determine assigned user
    $byUser = strtolower($parent['by']);
    $assignedUserId = $userMap[$byUser] ?? $userMap['ella'] ?? null;

    out("\n  [{$gi}] Client: {$clientName} | Cases: " . implode(', ', $caseNumbers)
        . " | Treatment: " . ($treatmentStatus ?? 'null')
        . ($clientHold ? ' | CLIENT HOLD' : '') . "\n");

    foreach ($caseNumbers as $caseNum) {
        $caseNum = trim($caseNum);
        if (!$caseNum) continue;

        // Create or get case
        $caseId = $caseMap[$caseNum] ?? null;
        if (!$caseId) {
            if (!$dryRun) {
                $caseData = [
                    'case_number' => $caseNum,
                    'client_name' => $clientName,
                    'status' => 'active',
                    'treatment_status' => $treatmentStatus,
                    'treatment_end_date' => $treatmentEndDate,
                    'assigned_to' => $assignedUserId,
                ];
                $caseId = dbInsert('cases', $caseData);
                $caseMap[$caseNum] = (int)$caseId;
            }
            $stats['cases_created']++;
            out("    Case {$caseNum} created" . ($dryRun ? ' (dry)' : " (id={$caseId})") . "\n");
        } else {
            $stats['cases_existing']++;
            // Update treatment status on existing case if we have new info
            if (!$dryRun && $treatmentStatus) {
                dbUpdate('cases', [
                    'treatment_status' => $treatmentStatus,
                    'treatment_end_date' => $treatmentEndDate,
                ], 'id = ?', [$caseId]);
            }
            out("    Case {$caseNum} exists (id={$caseId})\n");
        }

        // Add client notes if any
        if ($clientNotes && !$dryRun && $caseId) {
            dbInsert('case_notes', [
                'case_id' => $caseId,
                'user_id' => $assignedUserId ?: 1,
                'note_type' => 'general',
                'content' => $clientNotes,
            ]);
            $stats['notes_created']++;
        }

        // Add treatment extra notes
        if ($treatmentExtra && !$dryRun && $caseId) {
            dbInsert('case_notes', [
                'case_id' => $caseId,
                'user_id' => $assignedUserId ?: 1,
                'note_type' => 'general',
                'content' => "Treatment note: {$treatmentExtra}",
            ]);
            $stats['notes_created']++;
        }

        // Process all provider rows
        foreach ($allRows as $provRow) {
            $rawProvider = $provRow['provider'];
            if (empty($rawProvider)) continue;
            if (in_array(mb_strtolower(trim($rawProvider)), $providerSkipList)) {
                out("      SKIP: '{$rawProvider}' (not a provider)\n");
                continue;
            }

            [$providerName, $providerHold, $_] = normalizeProvider($rawProvider, $providerAliases);
            if (!$providerName) continue;

            // Find or create provider
            $provKey = mb_strtolower($providerName);
            $providerId = $providerMap[$provKey] ?? null;
            if (!$providerId) {
                if (!$dryRun) {
                    $providerId = dbInsert('providers', [
                        'name' => $providerName,
                        'type' => 'other',
                        'preferred_method' => 'fax',
                    ]);
                    $providerMap[$provKey] = (int)$providerId;
                }
                $stats['providers_created']++;
                $warnings[] = "New provider created: '{$providerName}' (from '{$rawProvider}')";
                out("      Provider '{$providerName}' created" . ($dryRun ? ' (dry)' : '') . "\n");
            } else {
                $stats['providers_existing']++;
            }

            // Check for duplicate case_provider
            if (!$dryRun && $caseId && $providerId) {
                $existing = dbFetchOne(
                    "SELECT id FROM case_providers WHERE case_id = ? AND provider_id = ?",
                    [$caseId, $providerId]
                );
                if ($existing) {
                    out("      Case-provider {$caseNum}↔{$providerName} already exists, skipping\n");
                    continue;
                }
            }

            // Determine method
            $via = $provRow['via'];
            $method = $methodMap[$via] ?? null;

            // Determine overall_status
            $reqDate = parseDate($provRow['req_date']);
            $hasFollowups = false;
            foreach ($provRow['followups'] as $fu) {
                if (!empty(trim($fu))) { $hasFollowups = true; break; }
            }

            if (!$method && !$reqDate) {
                $overallStatus = 'not_started';
            } elseif ($hasFollowups) {
                $overallStatus = 'follow_up';
            } else {
                $overallStatus = 'requesting';
            }

            // Determine assigned_to for this provider row
            $provBy = strtolower($provRow['by'] ?: $parent['by']);
            $provAssigned = $userMap[$provBy] ?? $assignedUserId;

            $isHold = $clientHold || $providerHold;
            $holdReason = null;
            if ($clientHold) $holdReason = 'Client on hold';
            if ($providerHold) $holdReason = 'Provider on hold';

            // Create case_provider
            if (!$dryRun && $caseId && $providerId) {
                $cpId = dbInsert('case_providers', [
                    'case_id' => $caseId,
                    'provider_id' => $providerId,
                    'overall_status' => $overallStatus,
                    'assigned_to' => $provAssigned,
                    'deadline' => $deadline,
                    'is_on_hold' => $isHold ? 1 : 0,
                    'hold_reason' => $holdReason,
                ]);
            } else {
                $cpId = null;
            }
            $stats['case_providers_created']++;

            out("      CP: {$providerName} via " . ($method ?? 'none') . " status={$overallStatus}"
                . ($isHold ? ' [HOLD]' : '') . "\n");

            // Create initial request
            if ($reqDate && $method && !$dryRun && $cpId) {
                // Determine next followup from first non-empty followup field
                $nextFollowup = null;
                foreach ($provRow['followups'] as $fu) {
                    $fu = trim($fu);
                    if (!$fu) continue;
                    // May be comma-separated: "2/11/2026, 2/13/26"
                    $parts = array_map('trim', explode(',', $fu));
                    $nextFollowup = parseDate($parts[0]);
                    break;
                }

                dbInsert('record_requests', [
                    'case_provider_id' => $cpId,
                    'request_date' => $reqDate,
                    'request_method' => $method,
                    'request_type' => 'initial',
                    'requested_by' => $provAssigned,
                    'next_followup_date' => $nextFollowup,
                    'send_status' => 'sent',
                    'sent_at' => $reqDate . ' 09:00:00',
                ]);
                $stats['requests_created']++;
            } elseif ($reqDate && $method && $dryRun) {
                $stats['requests_created']++;
            }

            // Create follow-up requests
            foreach ($provRow['followups'] as $fuIdx => $fuRaw) {
                $fuRaw = trim($fuRaw);
                if (!$fuRaw) continue;

                // Split comma-separated dates
                $fuDates = array_map('trim', explode(',', $fuRaw));
                foreach ($fuDates as $fdIdx => $fuDateStr) {
                    $fuDate = parseDate($fuDateStr);
                    if (!$fuDate) continue;

                    // Next followup = next date in sequence
                    $nextFu = null;
                    // Check remaining dates in this field
                    for ($ni = $fdIdx + 1; $ni < count($fuDates); $ni++) {
                        $nextFu = parseDate($fuDates[$ni]);
                        if ($nextFu) break;
                    }
                    // If none, check next followup fields
                    if (!$nextFu) {
                        for ($ni = $fuIdx + 1; $ni < 4; $ni++) {
                            $nfRaw = trim($provRow['followups'][$ni] ?? '');
                            if ($nfRaw) {
                                $nfParts = array_map('trim', explode(',', $nfRaw));
                                $nextFu = parseDate($nfParts[0]);
                                if ($nextFu) break;
                            }
                        }
                    }

                    if (!$dryRun && $cpId && $method) {
                        dbInsert('record_requests', [
                            'case_provider_id' => $cpId,
                            'request_date' => $fuDate,
                            'request_method' => $method,
                            'request_type' => 'follow_up',
                            'requested_by' => $provAssigned,
                            'next_followup_date' => $nextFu,
                            'send_status' => 'sent',
                            'sent_at' => $fuDate . ' 09:00:00',
                        ]);
                    }
                    $stats['requests_created']++;
                }
            }

            // Create note if present
            $note = $provRow['note'];
            if (!empty($note) && !$dryRun && $cpId && $caseId) {
                dbInsert('case_notes', [
                    'case_id' => $caseId,
                    'case_provider_id' => $cpId,
                    'user_id' => $provAssigned ?: 1,
                    'note_type' => 'general',
                    'content' => $note,
                ]);
                $stats['notes_created']++;
            } elseif (!empty($note)) {
                $stats['notes_created']++;
            }
        }
    }
}

// ─── PHASE 3: Report ───
out("\n\n=== Import Complete " . ($dryRun ? '(DRY RUN — nothing written)' : '') . " ===\n");
out("Cases:          {$stats['cases_created']} created, {$stats['cases_existing']} existing\n");
out("Providers:      {$stats['providers_created']} created, {$stats['providers_existing']} existing\n");
out("Case-Providers: {$stats['case_providers_created']} created\n");
out("Requests:       {$stats['requests_created']} created\n");
out("Notes:          {$stats['notes_created']} created\n");
out("Rows parsed:    {$stats['rows_parsed']}, skipped: {$stats['rows_skipped']}\n");

if (!empty($warnings)) {
    out("\n--- Warnings ({" . count($warnings) . "}) ---\n");
    foreach ($warnings as $w) {
        out("  ! {$w}\n");
    }
}

if (!$isCli) echo '</pre>';

// ─── Output helper ───
function out($msg) {
    echo $msg;
}
