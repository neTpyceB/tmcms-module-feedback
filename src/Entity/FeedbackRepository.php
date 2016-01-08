<?php

namespace TMCms\Modules\Feedback\Entity;

use TMCms\Orm\EntityRepository;

class FeedbackRepository extends EntityRepository {
    protected $db_table = 'm_feedback';
    protected $table_structure = [
        'fields' => [
            'client_id' => [
                'type' => 'index',
            ],
            'date_created' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'done' => [
                'type' => 'bool',
            ],
            'name' => [
                'type' => 'varchar',
            ],
            'email' => [
                'type' => 'varchar',
            ],
            'phone' => [
                'type' => 'varchar',
            ],
            'message' => [
                'type' => 'text',
            ],
        ],
    ];
}