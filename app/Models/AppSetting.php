<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'group'];

    public static function value(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        if ($key === 'mail_password' && filled($setting->value)) {
            try {
                return Crypt::decryptString($setting->value);
            } catch (DecryptException) {
                return $setting->value;
            }
        }

        return $setting->value ?? $default;
    }
}
