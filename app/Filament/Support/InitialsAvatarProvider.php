<?php

namespace App\Filament\Support;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Avatar généré localement (initiales sur fond marine, en SVG inline) :
 * remplace ui-avatars.com pour que le back-office n'appelle aucun service externe.
 */
class InitialsAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        $name = (string) (method_exists($record, 'getFilamentName') ? $record->getFilamentName() : ($record->name ?? ''));

        $initials = Str::of($name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" rx="32" fill="#0F2D52"/>'
            .'<text x="32" y="32" dy=".35em" text-anchor="middle" font-family="Inter Variable, Inter, sans-serif" font-size="26" font-weight="600" fill="#FFFFFF">'
            .e($initials ?: '?')
            .'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
