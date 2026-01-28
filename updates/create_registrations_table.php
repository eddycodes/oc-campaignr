<?php namespace ImpulseTechnologies\Campaignr\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class CreateRegistrationsTable extends Migration
{
    public function up()
    {
        Schema::create('impulsetechnologies_campaignr_registrations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('first_name');
            $table->string('other_name')->nullable();
            $table->string('last_name');
            $table->string('phone_number');
            $table->string('email');
            $table->string('organisation_name')->nullable();
            $table->text('additional_information')->nullable();
            $table->integer('event_id')->unsigned();
            $table->timestamps();

            // Add index on event_id for performance
            $table->index('event_id');

            // Add foreign key constraint
            $table->foreign('event_id')
                  ->references('id')
                  ->on('impulsetechnologies_campaignr_events')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('impulsetechnologies_campaignr_registrations');
    }
}