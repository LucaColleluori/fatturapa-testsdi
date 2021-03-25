<?php

use FatturaPa\Core\Models\MigrationManager;
use Illuminate\Database\Schema\Blueprint;

class CreateActorsTable extends MigrationManager
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        if (!$this->schema->hasTable('actors')) {
            $this->schema->create('actors', function (Blueprint $table) {
                $table->string('id');
                $table->string('code');
                $table->string('key')->nullable();
                $table->string('certificate')->nullable();
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->schema->drop('actors');
    }
}
