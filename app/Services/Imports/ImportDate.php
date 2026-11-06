<?php

namespace App\Services\Imports;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

final class ImportDate
{
    public static function parse(mixed $value): ?string
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        if ($value === null || $value === '') {
            return null;
        }
        try {
            if (is_numeric($value)) {
                if ($value < 1 || $value >= 2958466 || (int) $value === 60) {
                    throw new \InvalidArgumentException;
                }
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            if (preg_match('~^\d{1,2}[-/]\d{1,2}[-/]\d{4}$~', (string) $value)) {
                $date = Carbon::createFromFormat('!d/m/Y', str_replace('-', '/', $value));
                $errors = Carbon::getLastErrors();
                if ($errors && ($errors['warning_count'] || $errors['error_count'])) {
                    throw new \InvalidArgumentException;
                }

                return $date->format('Y-m-d');
            }

            // Only explicit ISO formats: Carbon::parse also accepts relative dates
            // and silently overflows invalid calendar dates.
            foreach (['!Y-m-d', '!Y-m-d H:i:s', '!Y-m-d\TH:i:sP'] as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $value);
                    $errors = Carbon::getLastErrors();
                    if ($date && (! $errors || (! $errors['warning_count'] && ! $errors['error_count']))) {
                        return $date->format('Y-m-d');
                    }
                } catch (Throwable) {
                    // Try the next explicitly supported ISO format.
                }
            }
            throw new \InvalidArgumentException;
        } catch (Throwable) {
            throw ValidationException::withMessages(['date' => 'Data non valida: '.(string) $value]);
        }
    }
}
