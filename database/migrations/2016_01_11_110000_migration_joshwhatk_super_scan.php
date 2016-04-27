<?php

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    1.0.2
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

use Illuminate\Database\Schema\Blueprint;
use JoshWhatK\SuperScan\Database\Account;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('server_name');
            $table->string('ip_address');
            $table->string('scan_directory');
            $table->string('public_url');
            $table->text('excluded_directories');
            $table->timestamps();

            $table->engine = 'InnoDB';
        });

        //-- create the default account
        foreach(config('joshwhatk.super_scan.account.defaults') as $account)
        {
            Account::create($account);
        }

        Schema::create('baseline_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('path')->index();
            $table->char('hash', 40);
            $table->timestamp('last_modified')->nullable();
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
            $table->string('path')->index();
            $table->char('baseline_hash', 40)->nullable();
            $table->char('latest_hash', 40)->nullable();
            $table->timestamp('last_modified')->nullable();
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
}
