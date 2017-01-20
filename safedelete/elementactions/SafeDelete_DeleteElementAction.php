<?php
namespace Craft;

class SafeDelete_DeleteElementAction extends SafeDelete_BaseDeleteElementAction
{
    public function getDeletionType()
    {
        return 'element';
    }

    public function getOriginalAction()
    {
        return 'Delete';
    }
    public function getDeletionHandle()
    {
        return 'SafeDelete_Delete';
    }

	public function getName()
	{
		return Craft::t('Safe Delete…');
	}

    public function isDestructive()
    {
        return true;
    }

    public function getConfirmationMessage()
    {
        return Craft::t('Are you sure you want to delete the selected elements?');
    }

	public function performAction(ElementCriteriaModel $criteria)
	{
		return false;
	}
}
