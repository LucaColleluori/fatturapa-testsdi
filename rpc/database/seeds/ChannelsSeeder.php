<?php

use FatturaPa\Core\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * INSERT INTO channels(cedente, issuer) VALUES ('IT-01234567890', '0000001');
         * INSERT INTO channels(cedente, issuer) VALUES ('IT-12345678901', '0000002');
         * INSERT INTO channels(cedente, issuer) VALUES ('IT-23456789012', '0000003');
         */
        Channel::query()->create([
            'cedente' => 'IT-01234567890',
            'issuer'  => '0000001',
        ]);

        Channel::query()->create([
            'cedente' => 'IT-12345678901',
            'issuer'  => '0000002',
        ]);
        Channel::query()->create([
            'cedente' => 'IT-23456789012',
            'issuer'  => '0000003',
        ]);
    }
}
