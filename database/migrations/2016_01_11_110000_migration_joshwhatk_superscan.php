<?php

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    0.0.3
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

            $table->engine = 'InnoDB';
        });

        Schema::create('baseline_files', function (Blueprint $table) {
            $table->string('path', 200);
            $table->char('hash', 40);
            $table->char('last_modified', 19)->nullable();
            $table->integer('account_id')->nullable()->unsigned();
            $table->timestamps();

            $table->engine = 'InnoDB';

            $table->primary('path');
            $table->foreign('account_id')->references('id')->on('accounts')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('history_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('status', 10);
            $table->string('path', 200);
            $table->string('baseline_hash', 40)->nullable()->default(null);
            $table->string('latest_hash', 40)->nullable()->default(null);
            $table->char('last_modified', 19)->nullable();
            $table->integer('account_id')->unsigned();
            $table->timestamps();

            $table->engine = 'InnoDB';

            $table->foreign('account_id')->references('id')->on('accounts')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('scans', function (Blueprint $table) {
            $table->integer('changes', 11)->default(0);
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
        Schema::drop('scanned');
        Schema::drop('history');
        Schema::drop('baseline');
        Schema::drop('accounts');
    }


    protected function convertRelation($relation)
    {
        return str_singular($relation);
    }
}
