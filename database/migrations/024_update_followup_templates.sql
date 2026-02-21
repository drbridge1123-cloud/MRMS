-- Migration 024: Update Follow-Up and Urgent templates to use previous_request_dates
-- Replaces separate initial_request_date / followup_dates with unified previous_request_dates placeholder

UPDATE letter_templates
SET body_template = REPLACE(
    body_template,
    'a second request and follow-up to our medical records request dated {{initial_request_date|date:F j, Y}}, regarding the above-referenced patient. To date, our office has not received the requested records.',
    'a follow-up to our previous medical records request(s) dated {{previous_request_dates}}, regarding the above-referenced patient. To date, our office has not received the requested records.'
)
WHERE name = 'Follow-Up Medical Records Request';

UPDATE letter_templates
SET body_template = REPLACE(
    body_template,
    'We are writing to urgently follow up on our medical records request originally submitted on {{initial_request_date|date:F j, Y}}. {{#if followup_dates}}Our office also followed up on {{followup_dates}}; however, to date, we have not received the requested records or any response from your office.{{else}}To date, we have not received the requested records or any response from your office.{{/if}}',
    'We are writing to urgently follow up on our medical records request(s). Our office has previously requested records on {{previous_request_dates}}; however, to date, we have not received the requested records or any response from your office.'
)
WHERE name = 'Urgent Medical Records Request';
