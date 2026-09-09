<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Links a local user row to the identity the proxy asserts.
     *
     * The proxy's subject ("usr_…") is the stable identifier; email is explicitly
     * mutable there (users can change their own), so matching on email would let
     * a change of address silently split one person into two accounts — or,
     * worse, attach one person's session to another's row. Nullable because rows
     * created under the builtin driver have no proxy identity, and unique because
     * two local users must never claim the same one.
     */
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->string('pratique_subject')->nullable()->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('pratique_subject');
        });
    }
};
