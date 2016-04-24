<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSponsorshipApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sponsorship_applications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sponsorship_id');
            $table->integer('team_id');
            $table->string('team_name');
            $table->char('mobile', 11);
            $table->string('contact_user');
            $table->string('application_reason');
            $table->string('memo');
            $table->tinyInteger('status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sponsorship_applications');
    }
}
