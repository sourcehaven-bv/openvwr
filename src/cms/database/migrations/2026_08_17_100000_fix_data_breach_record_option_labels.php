<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The option labels are themselves the stored values, so correcting a label in
// the translations would leave existing records holding a value that no longer
// occurs in the option list: the form would render the choice as unselected and
// validation would reject it on the next save. Rewrite the stored values along
// with the labels.
return new class extends Migration
{
    private const NATURE_OF_INCIDENT = [
        'Persoonsgegevens toegevoegd aan verkeer dossier' => 'Persoonsgegevens toegevoegd aan het verkeerde dossier',
    ];

    /**
     * Stored as JSON by the array cast, so the replacement happens inside the
     * encoded list rather than on the column as a whole.
     */
    private const PERSONAL_DATA_CATEGORIES = [
        'Andere financiële gegevens, namelijk:[open veld]' => 'Andere financiële gegevens',
    ];

    public function up(): void
    {
        $this->rename(self::NATURE_OF_INCIDENT, self::PERSONAL_DATA_CATEGORIES);
    }

    public function down(): void
    {
        $this->rename(
            array_flip(self::NATURE_OF_INCIDENT),
            array_flip(self::PERSONAL_DATA_CATEGORIES),
        );
    }

    /**
     * @param array<string, string> $natureOfIncident
     * @param array<string, string> $personalDataCategories
     */
    private function rename(array $natureOfIncident, array $personalDataCategories): void
    {
        foreach ($natureOfIncident as $from => $to) {
            DB::table('data_breach_records')
                ->where('nature_of_incident', $from)
                ->update(['nature_of_incident' => $to]);
        }

        foreach ($personalDataCategories as $from => $to) {
            // The array cast encodes with the default flags, so "financiële" is
            // stored as "financiële". Rows may also predate that or have been
            // written by hand, so replace both the escaped and the literal form.
            foreach ([JSON_THROW_ON_ERROR, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE] as $flags) {
                DB::table('data_breach_records')
                    ->whereNotNull('personal_data_categories')
                    ->update([
                        'personal_data_categories' => DB::raw(
                            sprintf(
                                'replace(personal_data_categories, %s, %s)',
                                $this->quoteJsonString($from, $flags),
                                $this->quoteJsonString($to, $flags),
                            ),
                        ),
                    ]);
            }
        }
    }

    /**
     * The values sit inside a JSON document, so they are matched in their encoded
     * form. json_encode wraps the result in quotes; those are stripped because the
     * replacement targets the value inside the document, not a whole document.
     */
    private function quoteJsonString(string $value, int $flags): string
    {
        $encoded = json_encode($value, $flags);

        return DB::getPdo()->quote(trim((string) $encoded, '"'));
    }
};
