<?php

namespace App\Traits;

trait AcceptedFileTypes
{
    /**
     * Returns a base map of file types categorized by their purpose.
     *
     * @return array An associative array where keys are categories and values are arrays of MIME types.
     */
    protected function baseFileTypeMap(): array
    {
        return [
            'images' => [
                'image/jpeg',
                'image/png',
            ],
            'documents' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
            'excel' => [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel.sheet.macroEnabled.12',
                'text/csv',
            ],
        ];
    }

    /**
     * Returns an array of accepted file types based on the type provided.
     *
     * @param string|array $types The type of files to return. Default is 'default'.
     *                            Other options include 'images', 'documents', and 'excel'.
     * @return array An array of accepted MIME types.
     */
    public function acceptedFileTypesArray(string|array $types = 'default'): array
    {
        $map = $this->baseFileTypeMap();

        if ($types === 'default') {
            $types = ['images', 'documents', 'excel'];
        }

        // Converte il parametro in array nel caso in cui sia una singola stringa
        $types = (array) $types;

        // Estrae e unisce tutti i MIME types delle categorie richieste,
        // rimuove eventuali duplicati e restituisce un array pulito
        return collect($types)
            ->flatMap(fn($type) => $map[$type] ?? [])
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Returns a comma-separated string of accepted file types based on the type provided.
     *
     * @param string $type The type of files to return. Default is 'default'.
     *                     Other options include 'images', 'documents', and 'excel'.
     * @return string A comma-separated string of accepted MIME types.
     */
    public function acceptedFileTypes(string|array $type = 'default'): string
    {
        return implode(',', $this->acceptedFileTypesArray($type));
    }
}
