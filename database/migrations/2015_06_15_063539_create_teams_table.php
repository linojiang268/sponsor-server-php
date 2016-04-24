<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('creator_id');
            $table->string('name', 32);
            $table->string('email', 64)->nullalbe();
            $table->string('address', 128)->nullable();
            $table->string('contact_phone', 32)->nullable();    // phone number of contacts
            $table->string('contact', 32)->nullable();  // name of contacts
            $table->string('introduction', 255)->nullable();
            $table->tinyInteger('status')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('teams');
    }
}
