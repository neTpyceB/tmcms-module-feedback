<?php
declare(strict_types=1);

namespace TMCms\Modules\Feedback;

use TMCms\Cache\Cacher;
use TMCms\Config\Settings;
use TMCms\Files\FileSystem;
use TMCms\Modules\Feedback\Entity\FeedbackEntity;
use TMCms\Modules\IModule;
use TMCms\Network\Mailer;
use TMCms\Traits\singletonInstanceTrait;

\defined('INC') or exit;

/**
 * Class ModuleFeedback
 * @package TMCms\Modules\Feedback
 */
class ModuleFeedback implements IModule {
    use singletonInstanceTrait;

    public static $tables = [
        'feedback' => 'm_feedback'
    ];
    private static $sending_period_seconds = 5;

    /**
     * @param array $data
     * @param bool $need_to_save_in_db
     * @param array $send_to_emails
     * @param array $files
     * @return FeedbackEntity
     */
    public static function addNewFeedback(array $data, $need_to_save_in_db = true, array $send_to_emails = [], array $files = []): FeedbackEntity {
        $feedback = new FeedbackEntity();

        $cacher = Cacher::getInstance()->getDefaultCacher();
        $cache_key = 'module_feedback_add_new_feedback_last_send_ts' . VISITOR_HASH;

        // Check message is not sent too quick
        $last_sent_ts = $cacher->get($cache_key);
        if (NOW - $last_sent_ts < self::$sending_period_seconds) {
            return $feedback;
        }
        // Save to Db
        if ($need_to_save_in_db) {
            $feedback->loadDataFromArray($data);
            $feedback->save();
        }

        // Save files if have any
        $folder_for_feedback = $feedback->getUploadFolder(true);
        FileSystem::mkDir($folder_for_feedback);
        $i = 1;
        foreach ($files as $file_path => $file_name) {
            // Upload to Feedback directory
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            \copy($file_path, $folder_for_feedback . $i . '.' . $ext);

            $i++;
        }

        // Send email to manager
        if ($send_to_emails) {

            $msg = '';
            foreach ($data as $k => $v) {
                if ($v && is_scalar($v)) {
                    $msg .= $k . ' - '. htmlspecialchars($v) .'<br>' . "\n";
                }
            }
            $msg .= '</table>';

            $mailer = Mailer::getInstance()
                ->setSubject('New feedback from '. CFG_DOMAIN)
                ->setSender(Settings::getCommonEmail())
                ->setMessage($msg)
            ;

            foreach ($send_to_emails as $email) {
                $mailer->setRecipient($email);
            }

            foreach ($files as $file_path => $file_name) {
                $mailer->addAttachment($file_path);
            }

            $mailer->send();

        }

        // Save last send ts
        $cacher->set($cache_key, NOW);

        return $feedback;
    }
}
