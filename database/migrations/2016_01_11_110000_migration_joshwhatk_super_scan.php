<?php

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    0.0.4
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MigrationJoshwhatkSuperScan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            foreach(config('joshwhatk.super_scan.account_information.relations') as $relation)
            {
                $relation = $this->convertRelation($relation);
                $table->integer($relation.'_id')->unsiged()->index();
            }
            $table->timestamps();

            $table->engine = 'InnoDB';
        });

        Schema::create('baseline_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('path', 200);
            $table->char('hash', 40);
            $table->char('last_modified', 19)->nullable();
            $table->integer('account_id')->nullable()->unsigned();
            $table->timestamps();

            $table->engine = 'InnoDB';

            $table->unique(['path', 'account_id']);
            $table->foreign('account_id')->references('id')->on('accounts')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('history_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('status', 10);
            $table->string('path', 200);
            $table->string('baseline_hash', 40)->nullable();
            $table->string('latest_hash', 40)->nullable();
            $table->char('last_modified', 19)->nullable();
            $table->integer('account_id')->unsigned();
            $table->timestamps();

            $table->engine = 'InnoDB';

            $table->foreign('account_id')->references('id')->on('accounts')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('scans', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('changes')->default(0);
            $table->integer('account_id')->unsigned();

            $table->timestamps();

            $table->engine = 'InnoDB';

            $table->foreign('account_id')->references('id')->on('accounts')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('scans');
        Schema::drop('history_records');
        Schema::drop('baseline_files');
        Schema::drop('accounts');
    }


    protected function convertRelation($relation)
    {
        return str_singular($relation);
    }
}
