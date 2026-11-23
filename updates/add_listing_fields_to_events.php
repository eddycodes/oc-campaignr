<?php namespace ImpulseTechnologies\Campaignr\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

/**
 * Fields for event listings and for events imported from another site:
 * a short summary, a category, cost, an all-day flag, an external link,
 * and the source record's id and URL (so an import can be re-run safely).
 * The featured image is a System\Models\File attachment (no column).
 */
class AddListingFieldsToEvents extends Migration
{
    public function up()
    {
        Schema::table('impulsetechnologies_campaignr_events', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('description');
            $table->string('category', 100)->nullable()->index()->after('excerpt');
            $table->string('cost', 100)->nullable()->after('category');
            $table->boolean('all_day')->default(false)->after('time_end');
            $table->string('website', 255)->nullable()->after('location_misc');
            $table->string('source_id', 100)->nullable()->unique()->after('website');
            $table->string('source_url', 255)->nullable()->after('source_id');
            $table->index('time_begin');
        });
    }

    public function down()
    {
        Schema::table('impulsetechnologies_campaignr_events', function (Blueprint $table) {
            $table->dropIndex(['time_begin']);
            $table->dropUnique(['source_id']);
            $table->dropIndex(['category']);
            $table->dropColumn(['excerpt', 'category', 'cost', 'all_day', 'website', 'source_id', 'source_url']);
        });
    }
}
