<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFileRowsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'UUID',
            ],
            'file_id' => [
                'type' => 'UUID',
            ],
            'row_data' => [
                'type' => 'TEXT',
            ],
            'row_index' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('file_id');
        $this->forge->addForeignKey('file_id', 'files', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('file_rows');
    }

    public function down()
    {
        $this->forge->dropTable('file_rows');
    }
}
