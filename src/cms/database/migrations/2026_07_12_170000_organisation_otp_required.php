<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisations', static function (Blueprint $table): void {
            $table->boolean('otp_required')
                ->default(false);
        });

        // Existing organisations were subject to OTP enforcement before this
        // flag existed; only organisations created after this migration start
        // with the default (off).
        DB::table('organisations')->update(['otp_required' => true]);
    }
};
