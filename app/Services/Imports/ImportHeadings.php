<?php

namespace App\Services\Imports;

/** Aliases of unambiguous headings, after Laravel Excel's slug formatter. */
final class ImportHeadings
{
    public static function normalize(array $row, bool $practices = false): array
    {
        $mapping = [
            'email' => ['e_mail'],
            'canale_acquisizione' => ['canale_di_acquisizione'],
            'data_ricontatto' => ['data_di_ricontatto'],
            'data_prima_rata' => ['data_di_inizio'],
            'data_ultima_rata' => ['data_di_fine'],
            'finanziato' => ['importo_finanziato'],
            'note' => ['note_lead'],
        ];
        if ($practices) {
            $mapping += [
                'cf_cl' => ['codice_fiscale'], 'recapito_cell' => ['telefono'],
                'data_nascita_cliente' => ['data_nascita', 'data_di_nascita'],
                'applicazione' => ['prodotto'], 'stato_pratica' => ['stato'],
                'nome_agenzia' => ['operatore'], 'numero_rate' => ['rate'],
                'montante' => ['totale_dovuto'], 'importo_rata' => ['rata_mensile'],
                'tipo_cliente' => ['tipologia_cliente'], 'trasformazione_gg' => ['giorni_trasformazione'],
            ];
        }
        foreach ($mapping as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                if (blank($row[$canonical] ?? null) && filled($row[$alias] ?? null)) {
                    $row[$canonical] = $row[$alias];
                }
            }
        }

        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $row[$key] = trim($value) === '' ? null : trim($value);
            }
        }

        return $row;
    }
}
