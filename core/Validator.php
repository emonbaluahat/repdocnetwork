<?php

namespace App\Core;

class Validator
{
    private array $data;
    private array $rules;
    private array $messages;
    private array $errors = [];
    private array $validated = [];

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
        $this->validate();
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $rulesStr) {
            if (is_string($rulesStr)) {
                $rulesList = explode('|', $rulesStr);
            } else {
                $rulesList = $rulesStr;
            }

            foreach ($rulesList as $rule) {
                $this->applyRule($field, $rule);
            }

            if (!isset($this->errors[$field])) {
                $this->validated[$field] = $this->data[$field] ?? null;
            }
        }
    }

    private function applyRule(string $field, string $rule): void
    {
        $params = [];

        if (str_contains($rule, ':')) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }

        $value = $this->data[$field] ?? null;

        $method = 'rule' . ucfirst($rule);
        if (method_exists($this, $method)) {
            $this->$method($field, $value, $params);
        }
    }

    private function addError(string $field, string $rule, string $defaultMessage): void
    {
        $key = "{$field}.{$rule}";
        $message = $this->messages[$key] ?? $this->messages[$rule] ?? $defaultMessage;
        $this->errors[$field][] = $message;
    }

    private function ruleRequired(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, 'required', "{$field} is required.");
        }
    }

    private function ruleEmail(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email', "{$field} must be a valid email.");
        }
    }

    private function ruleMin(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        $min = (int) ($params[0] ?? 0);
        if (is_string($value) && mb_strlen($value) < $min) {
            $this->addError($field, 'min', "{$field} must be at least {$min} characters.");
        }
        if (is_numeric($value) && $value < $min) {
            $this->addError($field, 'min', "{$field} must be at least {$min}.");
        }
    }

    private function ruleMax(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        $max = (int) ($params[0] ?? 0);
        if (is_string($value) && mb_strlen($value) > $max) {
            $this->addError($field, 'max', "{$field} must not exceed {$max} characters.");
        }
        if (is_numeric($value) && $value > $max) {
            $this->addError($field, 'max', "{$field} must not exceed {$max}.");
        }
    }

    private function ruleNumeric(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        if (!is_numeric($value)) {
            $this->addError($field, 'numeric', "{$field} must be a number.");
        }
    }

    private function ruleAlpha(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        if (!preg_match('/^[\p{L}\s]+$/u', $value)) {
            $this->addError($field, 'alpha', "{$field} may only contain letters.");
        }
    }

    private function ruleAlphanumeric(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        if (!preg_match('/^[\p{L}\p{N}\s]+$/u', $value)) {
            $this->addError($field, 'alphanumeric', "{$field} may only contain letters and numbers.");
        }
    }

    private function rulePhone(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $value);
        if (!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $cleaned)) {
            $this->addError($field, 'phone', "{$field} must be a valid Bangladesh phone number.");
        }
    }

    private function ruleNid(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        if (!preg_match('/^\d{10}$/', $cleaned) && !preg_match('/^\d{17}$/', $cleaned)) {
            $this->addError($field, 'nid', "{$field} must be a valid NID number.");
        }
    }

    private function ruleUrl(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'url', "{$field} must be a valid URL.");
        }
    }

    private function ruleIn(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        if (!in_array($value, $params)) {
            $this->addError($field, 'in', "{$field} must be one of: " . implode(', ', $params) . ".");
        }
    }

    private function ruleConfirmed(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        $confirmationField = $field . '_confirmation';
        $confirmationValue = $this->data[$confirmationField] ?? null;
        if ($value !== $confirmationValue) {
            $this->addError($field, 'confirmed', "{$field} confirmation does not match.");
        }
    }

    private function ruleUnique(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        $table = $params[0] ?? null;
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;

        if (!$table) return;

        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = :value";
        $bindings = ['value' => $value];

        if ($exceptId) {
            $sql .= " AND id != :except_id";
            $bindings['except_id'] = $exceptId;
        }

        $result = $db->fetch($sql, $bindings);
        if ((int) $result['count'] > 0) {
            $this->addError($field, 'unique', "{$field} already exists.");
        }
    }

    private function ruleDate(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        if (!strtotime($value)) {
            $this->addError($field, 'date', "{$field} must be a valid date.");
        }
    }

    private function ruleBoolean(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '') return;
        if (!in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true)) {
            $this->addError($field, 'boolean', "{$field} must be true or false.");
        }
    }

    private function ruleArray(string $field, mixed $value, array $params): void
    {
        if ($value === null) return;
        if (!is_array($value)) {
            $this->addError($field, 'array', "{$field} must be an array.");
        }
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function validated(): array
    {
        return $this->validated;
    }
}
