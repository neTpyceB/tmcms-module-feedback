<?php

namespace TMCms\Modules\Feedback;

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

	public static function addNewFeedback(array $data, $need_to_save_in_db = true, $need_to_send_email = false) {
		// Check email
		if ($need_to_send_email && $data['email'] && !Verify::email($data['email'])) {
			return false;
		}

		// Autocreate db
		$feedbacks = new FeedbackRepository();

		// Save to Db
		if ($need_to_save_in_db) {
			$feedback = new Feedback();
			$feedback->loadDataFromArray($data);
			$feedback->save();
		}

		// Send email to manager
		if ($need_to_send_email) {

			$msg = '<table><tr><th>Field</th><th>Value</th></tr>';
			foreach ($data as $k => $v) {
				if ($v) {
					$msg .= '<tr><td>'. $k .'</td><td>'. htmlspecialchars($v) .'</td></tr>';
				}
			}
			$msg .= '</table>';

			Mailer::getInstance()
				->setSubject('New feedback from '. CFG_DOMAIN)
				->setSender(Settings::getCommonEmail())
				->setRecipient(Settings::getCommonEmail())
				->setMessage($msg)
				->send()
			;

		}
		return true;
	}
}
