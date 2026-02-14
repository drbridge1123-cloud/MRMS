<?php
// POST /api/providers - Create a new provider

$userId = requireAuth();

$input = getInput();

// Validate required fields
$errors = validateRequired($input, ['name', 'type']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors), 422);
}

// Validate type enum
$allowedTypes = ['hospital', 'er', 'chiro', 'imaging', 'physician', 'surgery_center', 'pharmacy', 'other'];
if (!validateEnum($input['type'], $allowedTypes)) {
    errorResponse('Invalid provider type. Allowed: ' . implode(', ', $allowedTypes), 422);
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

// Build provider data
$providerData = [
    'name' => sanitizeString($input['name']),
    'type' => $input['type']
];

// Optional fields
$optionalFields = [
    'address', 'phone', 'fax', 'email', 'portal_url',
    'third_party_name', 'third_party_contact', 'notes'
];

foreach ($optionalFields as $field) {
    if (isset($input[$field])) {
        $providerData[$field] = sanitizeString($input[$field]);
    }
}

// Enum/boolean optional fields with special handling
if (isset($input['preferred_method'])) {
    $providerData['preferred_method'] = $input['preferred_method'];
}

if (isset($input['uses_third_party'])) {
    $providerData['uses_third_party'] = $input['uses_third_party'] ? 1 : 0;
}

if (isset($input['difficulty_level'])) {
    $providerData['difficulty_level'] = $input['difficulty_level'];
}

// Insert provider
$pdo = getDBConnection();
$pdo->beginTransaction();

try {
    $providerId = dbInsert('providers', $providerData);

    // Insert contacts if provided
    if (!empty($input['contacts']) && is_array($input['contacts'])) {
        $allowedContactTypes = ['email', 'fax', 'portal', 'phone'];

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
    logActivity($userId, 'create', 'provider', $providerId, [
        'name' => $providerData['name'],
        'type' => $providerData['type']
    ]);

    // Fetch the created provider with contacts
    $provider = dbFetchOne("SELECT * FROM providers WHERE id = ?", [$providerId]);
    $provider['contacts'] = dbFetchAll(
        "SELECT id, department, contact_type, contact_value, is_primary, verified_at, notes, created_at
         FROM provider_contacts WHERE provider_id = ? ORDER BY is_primary DESC",
        [$providerId]
    );

    successResponse($provider, 'Provider created successfully');
} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Failed to create provider: ' . $e->getMessage(), 500);
}
