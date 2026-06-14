<?php

namespace App\Http\Requests\Concerns;

trait SanitizesRequestInput
{
    /**
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    protected function sanitizedStrings(array $keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            if (! $this->has($key)) {
                continue;
            }

            $value = $this->input($key);

            if (is_string($value)) {
                $value = trim(strip_tags($value));
            }

            $values[$key] = $value === '' ? null : $value;
        }

        return $values;
    }
}
