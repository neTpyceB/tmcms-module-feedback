<?php
declare(strict_types=1);

namespace TMCms\Modules\Feedback;

use TMCms\Config\Constants;
use TMCms\Files\FileSystem;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\CmsTable;
use TMCms\HTML\Cms\Column\ColumnAccept;
use TMCms\HTML\Cms\Column\ColumnData;
use TMCms\HTML\Cms\Column\ColumnDelete;
use TMCms\HTML\Cms\Element\CmsHtml;
use TMCms\HTML\Cms\Filter\Text;
use TMCms\HTML\Cms\FilterForm;
use TMCms\Modules\Feedback\Entity\FeedbackEntity;
use TMCms\Modules\Feedback\Entity\FeedbackEntityRepository;
use TMCms\Strings\Converter;

\defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addCrumb(P)
;

/**
 * Class CmsFeedback
 * @package TMCms\Modules\Feedback
 */
class CmsFeedback
{
    /**
     * Main view
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function _default()
    {
        $feedback_collection = new FeedbackEntityRepository();
        $feedback_collection->addOrderByField('date_created', true);

        BreadCrumbs::getInstance()
            ->addAction('Remove unconfirmed', '?p='. P .'&do=_remove_unconfirmed')
            ->addAction('Remove duplicate emails', '?p='. P .'&do=_remove_dupes')
        ;

        echo CmsTable::getInstance()
            ->addData($feedback_collection)
            ->addColumn(ColumnData::getInstance('date_created')
                ->enableOrderableColumn()
                ->disableNewlines()
                ->enableRightAlign()
                ->setHref('?p=' . P . '&do=view&id={%id%}')
                ->dataType('ts2datetime')
                ->setTitle('Date')
            )
            ->addColumn(ColumnData::getInstance('name')
                ->enableOrderableColumn()
            )
            ->addColumn(ColumnData::getInstance('phone')
                ->enableOrderableColumn()
            )
            ->addColumn(ColumnData::getInstance('email')
                ->enableOrderableColumn()
                ->dataType('email')
            )
            ->addColumn(ColumnAccept::getInstance('done')
                ->setHref('?p=' . P . '&do=_done&id={%id%}')
                ->enableOrderableColumn()
            )
            ->addColumn(ColumnDelete::getInstance()
                ->setHref('?p=' . P . '&do=_delete&id={%id%}')
            )
            ->attachFilterForm(
                FilterForm::getInstance()
                    ->addFilter('Email', Text::getInstance('email')
                        ->actAs('like')
                    )
            );
    }


    /**
     * View one
     */
    public function view()
    {
        if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
            return;
        }
        $feedback_id = $_GET['id'];

        $feedback = new FeedbackEntity($feedback_id);
        if (!$feedback) {
            return;
        }

        $feedback_data = $feedback->getAsArray();

        $form = CmsForm::getInstance()
            ->disableFormTagOutput()
        ;

        $feedback_data['date_created'] = date(Constants::FORMAT_CMS_DATETIME_FORMAT, (int)$feedback_data['date_created']);

        unset($feedback_data['id'], $feedback_data['client_id']);

        foreach ($feedback_data as $k => $item) {
            if (!\is_string($item)) {
                continue;
            }

            $form->addField(Converter::charsToNormalTitle($k), CmsHtml::getInstance($k)
                ->setValue(htmlspecialchars($item, ENT_QUOTES))
            );
        }

        // Possible files uploaded
        $i = 0;
        foreach (FileSystem::scanDirs($feedback->getUploadFolder(true)) as $item) {
            $i++;
            $form->addField('File '. $i, CmsHtml::getInstance($k)
                ->setValue('<a href="'. $item['path_url'] .'">'. $item['name'] .'</a>')
            );
        }
        echo $form;
    }

    public function _done()
    {
        if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
            return;
        }
        $feedback_id = $_GET['id'];

        $feedback = new FeedbackEntity($feedback_id);
        $feedback->flipBoolValue('done');
        $feedback->save();

        back();
    }

    public function _delete()
    {

        if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
            return;
        }
        $feedback_id = $_GET['id'];

        $feedback = new FeedbackEntity($feedback_id);
        $feedback->deleteObject();

        back();
    }

    public function _remove_dupes() {
        $all = new FeedbackEntityRepository();

        $used_email = [];
        foreach ($all->getAsArrayOfObjects() as $feedback) { /** @var $feedback FeedbackEntity */
            if (!\in_array($feedback->getEmail(), $used_email, true)) {
                $used_email[] = $feedback->getEmail();
                continue;
            }

            $feedback->deleteObject();
        }

        back();
    }

    public function _remove_unconfirmed() {
        $all = new FeedbackEntityRepository();
        $all->setWhereDone(0);
        $all->addWhereFieldIsLower('date_created', NOW - 86400); // One day ago
        $all->deleteObjectCollection();

        back();
    }
}
