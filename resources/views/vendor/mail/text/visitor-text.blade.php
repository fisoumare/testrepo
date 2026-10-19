@props(['text'])
{{ str_replace(["\r\n", "\r"], "\n", trim((string) $text)) }}
