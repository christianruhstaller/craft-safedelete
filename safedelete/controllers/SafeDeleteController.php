<?php
namespace Craft;

class SafeDeleteController extends BaseController
{
    public function actionTryDelete()
    {
        $ids = craft()->request->getPost('ids');
        $type = craft()->request->getPost('type');

        $relations = craft()->safeDelete->getUsagesFor($ids, $type);
        
        if($relations === null || count($relations) === 0) { // safe to delete

            return $this->doAction($ids, $type);
        } else {
            $html = craft()->templates->render('safeDelete/deleteOverlay', ['relations' => $relations]);

            return $this->returnJson([
                'html' => $html,
                'success'=> true,
            ]);
        }
    }

    protected function doAction($ids, $type)
    {
        $message = '';

        switch($type) {
            case 'asset':
                craft()->assets->deleteFiles($ids);
                $message = Craft::t('Assets deleted.');
                break;
            case 'element':
                craft()->elements->deleteElementById($ids);
                $message = Craft::t('Elements deleted.');
                break;
        }

        return $this->returnJson([
            'success'=> true,
            'message' => $message,
        ]);
    }

    public function actionForceDelete()
    {
        $ids = craft()->request->getPost('ids');
        $type = craft()->request->getPost('type');

        return $this->doAction($ids, $type);
    }
}
