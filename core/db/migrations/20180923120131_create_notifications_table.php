<?php


use FatturaPa\Core\Models\MigrationManager;
use Illuminate\Database\Connection as DB;

class CreateNotificationsTable extends MigrationManager
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        if (!$this->schema->hasTable('notifications')) {
            $this->schema->create('notifications', function (Illuminate\Database\Schema\Blueprint $table) {
                $table->increments('id');
                $table->integer('invoice_id');
                $table->foreign('invoice_id')->references('id')->on('invoices')->change();
                $table->string('type');
                $table->string('status');
                $table->text('blob');
                $table->string('actor')->nullable();
                $table->text('nomefile')->nullable();
                $table->dateTimeTz('ctime')->default(DB::raw('CURRENT_TIMESTAMP'));
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->schema->drop('notifications');
    }
}
