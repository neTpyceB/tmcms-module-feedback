<?php

namespace TMCms\Modules\Feedback\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class FeedbackRepository
 * @package TMCms\Modules\Feedback\Entity
 *
 * @method $this setWhereDone(int $flag)
 */
class FeedbackRepository extends EntityRepository {
    protected $db_table = 'm_feedback';
    protected $table_structure = [
        'fields' => [
            'client_id' => [
                'type' => 'index',
            ],
            'date_created' => [
                'type' => 'ts',
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