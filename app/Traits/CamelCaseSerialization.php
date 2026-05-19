<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait CamelCaseSerialization
{
    public function toArray(): array
    {
        return self::keysToCamel(parent::toArray());
    }

    private static function keysToCamel(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            $out[Str::camel($key)] = is_array($value) ? self::keysToCamel($value) : $value;
        }
        return $out;
    }
}