<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFilesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'UUID',
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'original_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
            ],
            'row_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('files');
    }

    public function down()
    {
        $this->forge->dropTable('files');
    }
}
