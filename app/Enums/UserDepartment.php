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
}
