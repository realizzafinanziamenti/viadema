<?php

namespace App\Enums;

enum UserDepartment: string
{
    case DIRECT_PRODUCTION = 'direct-production';
    case INDIRECT_PRODUCTION = 'indirect-production';
    case CALL_CENTER = 'call-center';
    case CONSULTANT = 'consultant';
    case EXTERNAL = 'external';
    case OBSERVER = 'observer';

    /**
     * Returns the role associated with the department.
     */
    public function getRole(): string
    {
        return match ($this) {
            self::DIRECT_PRODUCTION => 'team_member',
            self::INDIRECT_PRODUCTION => 'team_member',
            self::CALL_CENTER => 'team_member',
            self::CONSULTANT => 'team_member',
            self::EXTERNAL => 'team_member',
            self::OBSERVER => 'observer',
        };
    }

    /**
     * Returns an array of unique roles for all departments.
     */
    public static function getRoles(): array
    {
        return array_unique(array_map(
            fn(self $department) => $department->getRole(),
            self::cases()
        ));
    }

    /**
     * Returns the label text for the department.
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::DIRECT_PRODUCTION => 'Produzione Diretta',
            self::INDIRECT_PRODUCTION => 'Produzione Indiretta',
            self::CALL_CENTER => 'Call Center',
            self::CONSULTANT => 'Sala Consulente',
            self::EXTERNAL => 'Collaboratore Esterno',
            self::OBSERVER => 'Osservatore',
        };
    }

    /**
     * Get the CSS class for the current department.
     */
    public function getLabelColor(): string
    {
        return match ($this) {
            self::DIRECT_PRODUCTION => 'direct-production',
            self::INDIRECT_PRODUCTION => 'indirect-production',
            self::CALL_CENTER => 'call-center',
            self::CONSULTANT => 'consultant',
            self::EXTERNAL => 'external',
            self::OBSERVER => 'observer',
        };
    }
}
