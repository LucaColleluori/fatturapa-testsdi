<?php

use FatturaPa\Core\Models\MigrationManager;
use Illuminate\Database\Schema\Blueprint;

class CreateChannelsTable extends MigrationManager
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        if (!$this->schema->hasTable('channels')) {
            $this->schema->create('channels', function (Blueprint $table) {
                $table->string('cedente');
                $table->string('issuer')->nullable();
                $table->primary('cedente');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->schema->drop('channels');
    }
}
