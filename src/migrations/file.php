<?php

namespace matrozov\yii2kit\migrations;

use yii\base\Exception;
use yii\db\Migration;

class file extends Migration
{
    public string $idUuidSchema = 'CHAR(36) CHARACTER SET ascii NOT NULL';
    public string $options      = '';

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function safeUp(): void
    {
        $this->createTable('file', [
            'id'               => $this->idUuidSchema,
            'target_class'     => 'varchar(255) CHARACTER SET ascii NOT NULL',
            'target_id'        => $this->string()->notNull(),
            'target_attribute' => 'varchar(255) CHARACTER SET ascii NOT NULL',
            'key'              => 'varchar(255) CHARACTER SET ascii',
            'name'             => $this->string()->notNull(),
            'mime_type'        => 'varchar(255) CHARACTER SET ascii',
            'size'             => $this->integer()->unsigned()->notNull(),
            'data'             => $this->json()->notNull(),
            'created_at'       => $this->integer()->unsigned()->notNull(),
            'updated_at'       => $this->integer()->unsigned()->notNull(),
        ], $this->options);

        $this->addPrimaryKey('file_pk', 'file', ['id']);
        $this->createIndex('file_target', 'file', ['target_class', 'target_id', 'target_attribute']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('file');
    }
}
