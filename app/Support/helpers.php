<?php

use App\Models\Setting;
use App\Support\Money;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('gnf')) {
    function gnf(int|float|null $amount, bool $withCurrency = true): string
    {
        return Money::gnf($amount, $withCurrency);
    }
}

if (! function_exists('whatsapp_url')) {
    function whatsapp_url(?string $text = null): string
    {
        $number = preg_replace('/\D+/', '', (string) setting('whatsapp', setting('phone', '')));
        $url = 'https://wa.me/'.$number;

        return $text ? $url.'?text='.rawurlencode($text) : $url;
    }
}
