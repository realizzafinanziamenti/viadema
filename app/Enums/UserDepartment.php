<?php

namespace App\Enums;

enum UserDepartment: string
{
    case FLOOR_MANAGER = 'floor_manager';
    case WEB = 'web';
    case CONSULTANT = 'consultant';
    case EXTERNAL = 'external';
    case BACK_OFFICE = 'back_office';
    case OBSERVER = 'observer';

    /**
     * Returns the role associated with the department.
     */
    public function getRole(): string
    {
        return $this->value;
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
     * Returns a random department.
     */
    public static function randomRole(): self
    {
        $cases = self::cases();
        return $cases[array_rand($cases)];
    }

    /**
     * Returns the label text for the department.
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::FLOOR_MANAGER => 'Coordinatore di Sala',
            self::WEB => 'Web',
            self::CONSULTANT => 'Sala Consulente',
            self::EXTERNAL => 'Collaboratore Esterno',
            self::BACK_OFFICE => 'Back Office',
            self::OBSERVER => 'Osservatore',
        };
    }

    /**
     * Get the CSS class for the current department.
     */
    public function getLabelColor(): string
    {
        return match ($this) {
            self::FLOOR_MANAGER => 'floor-manager',
            self::WEB => 'web',
            self::CONSULTANT => 'consultant',
            self::EXTERNAL => 'external',
            self::BACK_OFFICE => 'back-office',
            self::OBSERVER => 'observer',
        };
    }
}
