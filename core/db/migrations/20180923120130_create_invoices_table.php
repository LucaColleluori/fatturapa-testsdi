<?php

use FatturaPa\Core\Models\MigrationManager;
use Illuminate\Database\Connection as DB;
use Illuminate\Database\Schema\Blueprint;

class CreateInvoicesTable extends MigrationManager
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        if (!$this->schema->hasTable('invoices')) {
            $this->schema->create('invoices', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('remote_id')->nullable();
                $table->string('nomefile');
                $table->string('posizione');
                $table->string('cedente');
                $table->string('anno');
                $table->string('status');
                $table->text('blob');
                $table->dateTimeTz('ctime')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->string('actor')->nullable();
                $table->string('issuer')->nullable();
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->schema->drop('invoices');
    }
}
