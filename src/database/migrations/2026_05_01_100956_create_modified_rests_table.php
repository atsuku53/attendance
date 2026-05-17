<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModifiedRestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modified_rests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modified_attendance_id')->constrained()->onDelete('cascade');
            $table->timestamp('rest_start')->nullable();
            $table->timestamp('rest_end')->nullable();
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
        Schema::dropIfExists('modified_rests');
    }
}
