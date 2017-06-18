<?php

namespace TMCms\Modules\Feedback;

use TMCms\Cache\Cacher;
use TMCms\Config\Settings;
use TMCms\Modules\Feedback\Entity\FeedbackRepository;
use TMCms\Modules\IModule;
use TMCms\Network\Mailer;
use TMCms\Strings\Verify;
use TMCms\Traits\singletonInstanceTrait;
use TMCms\Modules\Feedback\Entity\Feedback;

defined('INC') or exit;

class ModuleFeedback implements IModule {
	use singletonInstanceTrait;

	public static $tables = [
		'feedback' => 'm_feedback'
	];
    private static $sending_period_seconds = 5;

	public static function addNewFeedback(array $data, $need_to_save_in_db = true, $send_to_emails = [], $files = []) {
		$send_to_emails = (array)$send_to_emails;

		$cacher = Cacher::getInstance()->getDefaultCacher();
		$cache_key = 'module_feedback_add_new_feedback_last_send_ts' . VISITOR_HASH;

		// Check message is not sent too quick
		$last_sent_ts = $cacher->get($cache_key);
		if (NOW - $last_sent_ts < self::$sending_period_seconds) {
			return false;
		}

		// Autocreate db
		$feedbacks = new FeedbackRepository();

		$feedback = NULL;
		// Save to Db
		if ($need_to_save_in_db) {
			$feedback = new Feedback();
			$feedback->loadDataFromArray($data);
			$feedback->save();
		}

		// Send email to manager
		if ($send_to_emails) {

			$msg = '<table><tr><th>Field</th><th>Value</th></tr>';
			foreach ($data as $k => $v) {
                if ($v && is_scalar($v)) {
					$msg .= '<tr><td>'. $k .'</td><td>'. htmlspecialchars($v) .'</td></tr>';
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

			foreach ($files as $file) {
				$mailer->addAttachment($file);
			}

			$mailer->send();

		}

		// Save last send ts
		$cacher->set($cache_key, NOW);

		return $feedback;
	}
}