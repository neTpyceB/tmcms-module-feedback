<?php

namespace TMCms\Modules\Feedback\Entity;

use TMCms\Files\FileSystem;
use TMCms\Orm\Entity;

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
class FeedbackEntity extends Entity {
    protected $db_table = 'm_feedback';

    public function beforeCreate() {
        $this->setField('date_created', NOW);
        $this->setField('ip', IP);

        parent::beforeCreate();
    }

    protected function beforeDelete()
    {
        FileSystem::remdir($this->getUploadFolder(true));

        parent::beforeDelete();
    }

    /**
     * @param bool $absolute
     *
     * @return string
     */
    public function getUploadFolder($absolute = false): string
    {
        return ($absolute ? \DIR_BASE : '') . \DIR_PUBLIC_URL . 'feedbacks/' . $this->getId() . '/';
    }
}
