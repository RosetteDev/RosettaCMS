<?php

namespace RosettaCMS\Plugins\Forms;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

/**
 * Form builder / submission engine for the Forms plugin.
 *
 * Field definitions and settings are stored as JSON on the Form model.
 * Submissions are stored as JSON payloads on FormSubmission.
 */
class FormsService
{
    /**
     * Build validation rules for a form's fields.
     *
     * @return array<string, mixed>
     */
    public function validationRules(Form $form): array
    {
        $rules = [];

        foreach ($form->fields ?? [] as $field) {
            $label = $field['label'] ?? $field['name'];
            $required = !empty($field['required']);
            $rule = $required ? 'required' : 'nullable';

            switch ($field['type']) {
                case 'email':
                    $rules[$field['name']] = [$rule, 'string', 'email', 'max:255'];
                    break;
                case 'number':
                    $rules[$field['name']] = [$rule, 'numeric', 'max:999999999'];
                    break;
                case 'date':
                    $rules[$field['name']] = [$rule, 'date', 'max:20'];
                    break;
                case 'textarea':
                    $rules[$field['name']] = [$rule, 'string', 'max:5000'];
                    break;
                case 'select':
                case 'radio':
                    $options = $field['options'] ?? [];
                    if ($options) {
                        $rules[$field['name']] = [$rule, 'string', 'in:' . implode(',', $options)];
                    } else {
                        $rules[$field['name']] = [$rule, 'string', 'max:255'];
                    }
                    break;
                case 'checkbox':
                    $rules[$field['name']] = [$rule, 'in:0,1,on,yes,true'];
                    break;
                default: // text, phone
                    $rules[$field['name']] = [$rule, 'string', 'max:255'];
                    break;
            }
        }

        return $rules;
    }

    /**
     * Build an Akismet-lite analysis payload from a submission.
     *
     * @param  array<string, mixed>  $data
     */
    public function spamPayload(Form $form, array $data): array
    {
        $name = null;
        $email = null;
        $message = null;
        $custom = [];

        foreach ($form->fields ?? [] as $field) {
            $value = $data[$field['name']] ?? null;
            if ($value === null || $value === '' || is_bool($value)) {
                continue;
            }

            $value = is_scalar($value) ? (string) $value : '';

            if ($field['type'] === 'email') {
                $email = $value;
            } elseif ($field['type'] === 'textarea') {
                $message = $value;
            } elseif (in_array($field['type'], ['text', 'phone', 'number'], true) && $name === null) {
                $name = $value;
            } else {
                $custom[$field['name']] = $value;
            }
        }

        if ($message === null) {
            $message = implode(' ', array_filter(array_map(
                fn ($f) => $data[$f['name']] ?? null,
                $form->fields ?? [],
            )));
        }

        return [
            'name' => $name,
            'email' => $email,
            'subject' => null,
            'message' => $message,
            'custom_fields' => $custom,
        ];
    }

    /**
     * Normalize submitted data (drop empty values, coerce checkboxes).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalizeSubmission(Form $form, array $data): array
    {
        $normalized = [];

        foreach ($form->fields ?? [] as $field) {
            $name = $field['name'];
            $value = $data[$name] ?? null;

            if ($field['type'] === 'checkbox') {
                $normalized[$name] = !empty($value) && $value !== '0' && $value !== 'false' ? '1' : null;
                continue;
            }

            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            $normalized[$name] = is_scalar($value) ? trim((string) $value) : null;
        }

        return array_filter($normalized, fn ($v) => $v !== null);
    }
}
