<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The fields the AP notification form asks for that the register could not yet
// answer. Multi-choice answers are stored as text and cast to array, following
// the columns that were already on this table.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_breach_records', static function (Blueprint $table): void {
            // 1.3 other supervisory authorities
            $table->text('other_supervisors')->nullable();
            $table->text('other_supervisors_other')->nullable();

            // 2.1 / 2.2 international
            $table->boolean('cross_border')->default(false);
            $table->text('cross_border_countries')->nullable();
            $table->text('reported_other_dpas')->nullable();

            // 4.3 / 4.5 timeline
            $table->text('how_discovered')->nullable();
            $table->text('late_notification_reason')->nullable();

            // 5.1 nature of the breach
            $table->text('nature_of_breach')->nullable();

            // 6.3 number of data records
            $table->text('record_count')->nullable();
            $table->text('record_count_explanation')->nullable();

            // 7.1 / 7.3 affected people
            $table->text('affected_groups')->nullable();
            $table->text('affected_groups_other')->nullable();
            $table->boolean('affected_count_known')->default(false);
            $table->unsignedInteger('affected_count')->nullable();
            $table->unsignedInteger('affected_count_min')->nullable();
            $table->unsignedInteger('affected_count_max')->nullable();

            // 8.1 measures taken beforehand
            $table->text('protection_beforehand')->nullable();
            $table->text('protection_beforehand_explanation')->nullable();

            // 9.1 / 9.2 / 9.3 consequences and risk
            $table->text('consequences_controller')->nullable();
            $table->text('consequences_controller_other')->nullable();
            $table->text('consequences_data_subjects')->nullable();
            $table->text('consequences_data_subjects_other')->nullable();
            $table->text('risk_severity')->nullable();

            // 10.1.3 how many people were informed
            $table->unsignedInteger('reported_to_involved_count')->nullable();
        });

        Schema::table('organisations', static function (Blueprint $table): void {
            // 3.1.1 / 3.1.2 identify the controller on the notification
            $table->string('coc_number')->nullable();
            $table->string('fg_registration_number')->nullable();
            $table->string('sector')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('data_breach_records', static function (Blueprint $table): void {
            $table->dropColumn([
                'other_supervisors',
                'other_supervisors_other',
                'cross_border',
                'cross_border_countries',
                'reported_other_dpas',
                'how_discovered',
                'late_notification_reason',
                'nature_of_breach',
                'record_count',
                'record_count_explanation',
                'affected_groups',
                'affected_groups_other',
                'affected_count_known',
                'affected_count',
                'affected_count_min',
                'affected_count_max',
                'protection_beforehand',
                'protection_beforehand_explanation',
                'consequences_controller',
                'consequences_controller_other',
                'consequences_data_subjects',
                'consequences_data_subjects_other',
                'risk_severity',
                'reported_to_involved_count',
            ]);
        });

        Schema::table('organisations', static function (Blueprint $table): void {
            $table->dropColumn(['coc_number', 'fg_registration_number', 'sector']);
        });
    }
};
