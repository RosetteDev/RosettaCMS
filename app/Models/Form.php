<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use SoftDeletes;

    protected $table = 'forms';

    protected $fillable = [
        'site_id',
        'user_id',
        'name',
        'slug',
        'description',
        'fields',
        'settings',
        'enabled',
    ];

    protected $casts = [
        'fields' => 'array',
        'settings' => 'array',
        'enabled' => 'boolean',
    ];

    /**
     * Field types supported by the builder.
     */
    public const FIELD_TYPES = [
        'text' => 'Text',
        'email' => 'Email',
        'phone' => 'Phone',
        'number' => 'Number',
        'textarea' => 'Textarea',
        'select' => 'Select',
        'radio' => 'Radio',
        'checkbox' => 'Checkbox',
        'date' => 'Date',
    ];

    /**
     * Field types that carry an options list.
     */
    public const OPTION_FIELD_TYPES = ['select', 'radio'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'form_id');
    }

    /**
     * A boolean-ish setting accessor with a safe default.
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        $settings = $this->settings ?? [];

        return $settings[$key] ?? $default;
    }

    /**
     * Always expose normalized fields, even when rows were created
     * outside the admin workflow (tests, seeds, imports).
     */
    public function getFieldsAttribute(mixed $value): array
    {
        // Custom mutators receive the raw attribute (the JSON string, before
        // the array cast runs), so decode it here.
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        return self::normalizeFields(is_array($value) ? $value : []);
    }

    /**
     * Validate a raw fields array and normalize it.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeFields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $field) {
            $name = strtolower(trim((string) ($field['name'] ?? '')));
            $type = (string) ($field['type'] ?? 'text');

            if ($name === '' || !preg_match('/^[a-z][a-z0-9_]{0,49}$/', $name)) {
                continue;
            }

            if (!array_key_exists($type, self::FIELD_TYPES)) {
                $type = 'text';
            }

            $options = [];
            if (in_array($type, self::OPTION_FIELD_TYPES, true)) {
                $rawOptions = $field['options'] ?? [];
                if (is_string($rawOptions)) {
                    $rawOptions = preg_split('/\r\n|\r|\n/', $rawOptions) ?: [];
                }
                $options = collect($rawOptions)
                    ->map(fn ($o) => str_replace([',', '\\'], [' ', ''], trim((string) $o)))
                    ->filter()
                    ->unique()
                    ->take(100)
                    ->values()
                    ->all();
            }

            $normalized[] = [
                'name' => $name,
                'label' => trim((string) ($field['label'] ?? $name)) ?: $name,
                'type' => $type,
                'placeholder' => trim((string) ($field['placeholder'] ?? '')) ?: null,
                'required' => !empty($field['required']),
                'options' => $options ?: null,
                'help_text' => trim((string) ($field['help_text'] ?? '')) ?: null,
            ];
        }

        return $normalized;
    }

    /**
     * Validate a raw settings array and normalize it.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public static function normalizeSettings(array $settings): array
    {
        return [
            'submit_label' => trim((string) ($settings['submit_label'] ?? 'Send')) ?: 'Send',
            'success_message' => trim((string) ($settings['success_message'] ?? 'Thanks! Your message has been sent.')) ?: 'Thanks! Your message has been sent.',
            'mail_to' => trim((string) ($settings['mail_to'] ?? '')) ?: null,
            'spam_protection' => filter_var($settings['spam_protection'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'confirm_message' => trim((string) ($settings['confirm_message'] ?? '')) ?: null,
        ];
    }
}
