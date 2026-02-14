<?php
// PUT /api/providers/{id} - Update an existing provider

requireAdminOrManager();
$userId = requireAuth();

$providerId = (int)($_GET['id'] ?? 0);
if (!$providerId) {
    errorResponse('Provider ID is required', 400);
}

// Check provider exists
$existing = dbFetchOne("SELECT id, name FROM providers WHERE id = ?", [$providerId]);
if (!$existing) {
    errorResponse('Provider not found', 404);
}

$input = getInput();

// Validate type if provided
if (!empty($input['type'])) {
    $allowedTypes = ['hospital', 'er', 'chiro', 'imaging', 'physician', 'surgery_center', 'pharmacy', 'other'];
    if (!validateEnum($input['type'], $allowedTypes)) {
        errorResponse('Invalid provider type. Allowed: ' . implode(', ', $allowedTypes), 422);
    }
}

// Validate preferred_method if provided
if (!empty($input['preferred_method'])) {
    $allowedMethods = ['email', 'fax', 'portal', 'phone', 'mail'];
    if (!validateEnum($input['preferred_method'], $allowedMethods)) {
        errorResponse('Invalid preferred method. Allowed: ' . implode(', ', $allowedMethods), 422);
    }
}

// Validate difficulty_level if provided
if (!empty($input['difficulty_level'])) {
    $allowedLevels = ['easy', 'medium', 'hard'];
    if (!validateEnum($input['difficulty_level'], $allowedLevels)) {
        errorResponse('Invalid difficulty level. Allowed: ' . implode(', ', $allowedLevels), 422);
    }
}

// Build update data - only include fields that were sent
$updateData = [];

$stringFields = [
    'name', 'address', 'phone', 'fax', 'email', 'portal_url',
    'third_party_name', 'third_party_contact', 'notes'
];

foreach ($stringFields as $field) {
    if (array_key_exists($field, $input)) {
        $updateData[$field] = $input[$field] !== null ? sanitizeString($input[$field]) : null;
    }
}

$enumFields = ['type', 'preferred_method', 'difficulty_level'];
foreach ($enumFields as $field) {
    if (array_key_exists($field, $input)) {
        $updateData[$field] = $input[$field];
    }
}

if (array_key_exists('uses_third_party', $input)) {
    $updateData['uses_third_party'] = $input['uses_third_party'] ? 1 : 0;
}

if (array_key_exists('avg_response_days', $input)) {
    $updateData['avg_response_days'] = $input['avg_response_days'] !== null ? (int)$input['avg_response_days'] : null;
}

$pdo = getDBConnection();
$pdo->beginTransaction();

try {
    // Update provider fields if any were provided
    if (!empty($updateData)) {
        dbUpdate('providers', $updateData, 'id = ?', [$providerId]);
    }

    // Update contacts if provided
    if (array_key_exists('contacts', $input) && is_array($input['contacts'])) {
        $allowedContactTypes = ['email', 'fax', 'portal', 'phone'];

        // Delete existing contacts and re-insert
        dbDelete('provider_contacts', 'provider_id = ?', [$providerId]);

        foreach ($input['contacts'] as $contact) {
            if (empty($contact['contact_type']) || empty($contact['contact_value'])) {
                continue;
            }

            if (!validateEnum($contact['contact_type'], $allowedContactTypes)) {
                continue;
            }

            $contactData = [
                'provider_id' => $providerId,
                'contact_type' => $contact['contact_type'],
                'contact_value' => sanitizeString($contact['contact_value']),
                'is_primary' => !empty($contact['is_primary']) ? 1 : 0
            ];

            if (!empty($contact['department'])) {
                $contactData['department'] = sanitizeString($contact['department']);
            }

            if (!empty($contact['notes'])) {
                $contactData['notes'] = sanitizeString($contact['notes']);
            }

            if (!empty($contact['verified_at']) && validateDate($contact['verified_at'])) {
                $contactData['verified_at'] = $contact['verified_at'];
            }

            dbInsert('provider_contacts', $contactData);
        }
    }

    $pdo->commit();

    // Log activity
    logActivity($userId, 'update', 'provider', $providerId, [
        'name' => $existing['name'],
        'updated_fields' => array_keys($updateData)
    ]);

    // Fetch updated provider with contacts
    $provider = dbFetchOne("SELECT * FROM providers WHERE id = ?", [$providerId]);
    $provider['contacts'] = dbFetchAll(
        "SELECT id, department, contact_type, contact_value, is_primary, verified_at, notes, created_at
         FROM provider_contacts WHERE provider_id = ? ORDER BY is_primary DESC, department ASC",
        [$providerId]
    );

    successResponse($provider, 'Provider updated successfully');
} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Failed to update provider: ' . $e->getMessage(), 500);
}
