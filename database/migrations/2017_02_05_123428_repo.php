<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Repo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('fullName');
            $table->text('description')->nullable();
            $table->string('stars')->nullable();
            $table->string('forks')->nullable();
            $table->string('repoId');
            $table->string('identifier')->unique();
            $table->string('language')->nullable();
            $table->string('weeklyCommits')->nullable();
            $table->string('totalWeeklyCommits')->nullable();
            $table->unsignedInteger('userId');
            $table->timestamps();
            $table->foreign('userId')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('repos');
    }
}
