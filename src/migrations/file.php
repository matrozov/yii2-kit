<?php

namespace matrozov\yii2common\migrations;

use yii\db\Migration;

class pgsql extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('file', [
            'id'               => 'UUID NOT NULL',
            'target_class'     => $this->string()->notNull(),
            'target_id'        => $this->string()->notNull(),
            'target_attribute' => $this->string()->notNull(),
            'key'              => $this->string(),
            'name'             => $this->string()->notNull(),
            'mime_type'        => $this->string()->notNull(),
            'size'             => $this->integer()->unsigned()->notNull(),
            'created_at'       => $this->integer()->unsigned()->notNull(),
            'updated_at'       => $this->integer()->unsigned()->notNull(),
        ]);

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
