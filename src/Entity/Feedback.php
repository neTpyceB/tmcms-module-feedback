<?php

namespace TMCms\Modules\Feedback\Entity;

use neTpyceB\TMCms\Orm\Entity;

/**
 * Class Feedback
 * @package TMCms\Modules\Feedback\Entity
 *
 * @method setName(string)
 * @method setEmail(string)
 * @method setMessage(string)
 * @method string getMessage()
 * @method string getEmail()
 * @method string getName()
 */
class Feedback extends Entity {
    protected $db_table = 'm_feedback';

    public function beforeCreate() {
        $this->setField('date_created', NOW);
        $this->setField('ip', IP);

        parent::beforeCreate();
    }
}
