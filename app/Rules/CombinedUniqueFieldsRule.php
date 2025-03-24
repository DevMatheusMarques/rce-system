<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;

class CombinedUniqueFieldsRule implements Rule
{
    protected
    string $currentField;
    protected
    ?string $valueField;
    protected
    string $withField;
    protected
    Model $model;

    /**
     * Create a new rule instance.
     *
     * @return void
     */

    public function __construct(Model $model, string $withField, ?string $valueField = null)
    {
        $this->model = $model;
        $this->withField = $withField;
        $this->valueField = $valueField ?? null;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!$this->valueField) {
            return false;
        }
        $this->currentField = $attribute;

        return !$this->recordExists($attribute, $value);
    }

    /**
     * Check if the record exists in the database.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function recordExists(string $attribute, $value): bool
    {
        return $this->model
            ->where($attribute, $value)
            ->where($this->withField, $this->valueField)
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        if (!$this->valueField) {
            return "The 'product_id' field is required";
        }
        return "The fields '{$this->currentField}' and '{$this->withField}' must be unique in the table '{$this->model->getTable()}'.";
    }
}
