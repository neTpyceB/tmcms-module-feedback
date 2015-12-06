<?php

namespace TMCms\Modules\Feedback;

use neTpyceB\TMCms\HTML\Cms\CmsForm;
use neTpyceB\TMCms\HTML\Cms\CmsTable;
use neTpyceB\TMCms\HTML\Cms\Column\ColumnAccept;
use neTpyceB\TMCms\HTML\Cms\Column\ColumnData;
use neTpyceB\TMCms\HTML\Cms\Column\ColumnDelete;
use neTpyceB\TMCms\HTML\Cms\Element\CmsHtml;
use neTpyceB\TMCms\Strings\Converter;
use TMCms\Modules\Feedback\Entity\Feedback;
use TMCms\Modules\Feedback\Entity\FeedbackRepository;

defined('INC') or exit;

class CmsFeedback
{
    /** Main view */
    public function _default()
    {
        $feedback_collection = new FeedbackRepository();
        $feedback_collection->addOrderByField('date_created', true);

        echo CmsTable::getInstance()
            ->addData($feedback_collection)
            ->addColumn(ColumnData::getInstance('date_created')
                ->enableOrderableColumn()
                ->nowrap(true)
                ->align('right')
                ->href('?p=' . P . '&do=view&id={%id%}')
                ->dataType('ts2datetime')
                ->title('Date')
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
                ->href('?p=' . P . '&do=_done&id={%id%}')
                ->enableOrderableColumn()
            )
            ->addColumn(ColumnDelete::getInstance()
                ->href('?p=' . P . '&do=_delete&id={%id%}')
            );
    }


    /** View one */
    public function view()
    {
        if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) return;
        $feedback_id = $_GET['id'];

        $feedback = new Feedback($feedback_id);
        if (!$feedback) return;

        $feedback_data = $feedback->getAsArray();

        $form = CmsForm::getInstance()
            ->outputTagForm(false)
        ;

        $feedback_data['date_created'] = date(CFG_CMS_DATETIME_FORMAT, $feedback_data['date_created']);

        unset($feedback_data['id']);
        unset($feedback_data['client_id']);

        foreach ($feedback_data as $k => $item) {
            if (!is_string($item)) {
                continue;
            }

            $form->addField(Converter::symb2Ttl($k), CmsHtml::getInstance($k)->value(htmlspecialchars($item, ENT_QUOTES)));
        }

        echo $form;
    }

    public function _done()
    {
        if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) return;
        $feedback_id = $_GET['id'];

        $feedback = new Feedback($feedback_id);
        $feedback->flipBoolValue('done');
        $feedback->save();

        back();
    }

    public function _delete()
    {

        if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) return;
        $feedback_id = $_GET['id'];

        $feedback = new Feedback($feedback_id);
        $feedback->deleteObject();

        back();
    }
}