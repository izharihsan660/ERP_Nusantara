<?php

namespace App\Http\Requests\Concerns;

trait NormalizesMoneyInput
{
    /**
     * @param  array<int, string>  $fields
     */
    protected function normalizeMoneyInput(array $fields): void
    {
        $data = $this->input();

        foreach ($fields as $field) {
            $this->normalizeMoneyField($data, explode('.', $field));
        }

        $this->replace($data);
    }

    /**
     * @param  array<int, string>  $segments
     */
    private function normalizeMoneyField(mixed &$data, array $segments): void
    {
        if ($segments === []) {
            $data = $this->parseMoneyValue($data);

            return;
        }

        $segment = array_shift($segments);

        if ($segment === '*') {
            if (! is_array($data)) {
                return;
            }

            foreach ($data as &$item) {
                $this->normalizeMoneyField($item, $segments);
            }

            return;
        }

        if (! is_array($data) || ! array_key_exists($segment, $data)) {
            return;
        }

        $this->normalizeMoneyField($data[$segment], $segments);
    }

    private function parseMoneyValue(mixed $value): mixed
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        $normalized = preg_replace('/[^0-9,.-]/', '', trim($value));

        if ($normalized === null || $normalized === '') {
            return $value;
        }

        $hasIndonesianDecimal = str_contains($normalized, ',');
        $hasDatabaseDecimal = preg_match('/^-?\d+\.\d{1,2}$/', $normalized) === 1;

        $normalized = $hasIndonesianDecimal
            ? str_replace(',', '.', str_replace('.', '', $normalized))
            : ($hasDatabaseDecimal ? $normalized : str_replace('.', '', $normalized));

        return is_numeric($normalized) ? (float) $normalized : $value;
    }
}
