<?php
namespace Craft;

class SafeDelete_DeleteAssetsElementAction extends SafeDelete_BaseDeleteElementAction
{
    public function getDeletionType()
    {
        return 'asset';
    }

    public function getOriginalAction()
    {
        return 'DeleteAssets';
    }
    public function getDeletionHandle()
    {
        return 'SafeDelete_DeleteAssets';
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
        return Craft::t('Are you sure you want to delete the selected assets?');
    }

	public function performAction(ElementCriteriaModel $criteria)
	{
		return false;
	}
}
