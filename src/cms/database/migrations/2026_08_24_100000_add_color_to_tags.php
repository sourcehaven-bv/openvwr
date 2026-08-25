<?php

declare(strict_types=1);

use App\Enums\LabelColor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Give labels a colour.
 *
 * Nullable, because null is the honest representation of "no colour yet": a
 * bundle imported from an older version carries no colour, and the rendering
 * falls back to grey for those rather than inventing one at read time.
 *
 * Existing labels are backfilled here so the feature is not empty on arrival.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tags', static function (Blueprint $table): void {
            $table->string('color')->nullable();
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('tags', static function (Blueprint $table): void {
            $table->dropColumn('color');
        });
    }

    /**
     * Spread the palette over the labels each organisation already has.
     *
     * Ordered by name rather than by creation date so the result is stable:
     * re-running this on a copy of the database produces the same colours.
     * Soft-deleted labels are included because they can be restored, and would
     * otherwise come back without a colour.
     */
    private function backfill(): void
    {
        $colors = array_map(
            static fn (LabelColor $labelColor): string => $labelColor->value,
            LabelColor::cases(),
        );

        $organisationIds = DB::table('tags')
            ->distinct()
            ->orderBy('organisation_id')
            ->pluck('organisation_id');

        foreach ($organisationIds as $organisationId) {
            $ids = DB::table('tags')
                ->where('organisation_id', $organisationId)
                ->orderBy('name')
                ->orderBy('id')
                ->pluck('id');

            foreach ($ids as $index => $id) {
                DB::table('tags')
                    ->where('id', $id)
                    ->update(['color' => $colors[$index % count($colors)]]);
            }
        }
    }
};
