<?php

namespace App\Enums;

enum PanelRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Editor = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => '超级管理员',
            self::Admin => '管理员',
            self::Editor => '编辑',
        };
    }

    /**
     * Get the role options for form and table filters.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role): array => [$role->value => $role->label()])
            ->all();
    }
}
