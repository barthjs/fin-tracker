<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class LocalAvatarProvider implements AvatarProvider
{
    /**
     * Return the avatar as base64 encoded svg
     */
    public function get(Model|Authenticatable $record): string
    {
        $initials = $this->extractInitials($record);

        $html = $this->render($initials);

        return 'data:image/svg+xml;base64,'.base64_encode($html);
    }

    private function extractInitials(Model|Authenticatable $record): string
    {
        $initials = '';

        $first = mb_trim((string) ($record->first_name ?? ''));
        $last = mb_trim((string) ($record->last_name ?? ''));

        if ($first !== '') {
            $initials .= Str::upper(Str::substr($first, 0, 1));
        }

        if ($last !== '') {
            $initials .= Str::upper(Str::substr($last, 0, 1));
        }

        if ($initials === '') {
            $name = mb_trim((string) $record->name);
            $initials = Str::upper(Str::substr($name, 0, 1));
        }

        return $initials;
    }

    private function render(string $initials): string
    {
        return view('components.avatar-svg', [
            'initials' => $initials,
        ])->toHtml();
    }
}
