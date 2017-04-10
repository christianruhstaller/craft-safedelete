<?php

namespace Craft;

class SafeDeletePlugin extends BasePlugin
{
    public function init()
    {
        Craft::import('plugins.safedelete.elementactions.SafeDelete_BaseDeleteElementAction');

        $this->initEventHandlers();

        if (craft()->request->isCpRequest()) {

        }
    }

    public function initEventHandlers()
    {

    }

    public function getName()
    {
        return Craft::t('SafeDelete');
    }

    public function getVersion()
    {
        return '0.0.1';
    }

    public function getDeveloper()
    {
        return 'Christian Ruhstaller';
    }

    public function getDeveloperUrl()
    {
        return 'http://goldinteractive.ch';
    }

    protected function defineSettings()
    {
        return [
            'hideDefaultDeleteAction' => [
                AttributeType::Bool,
                'label' => 'Hide default delete action?',
                'default' => true,
            ],
            'allowForceDelete'        => [AttributeType::Bool, 'label' => 'Allow force delete', 'default' => true],
        ];
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render(
            'safeDelete/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }

    public function addAssetActions($source)
    {
        $actions = [];

        if (preg_match('/^folder:(\d+)$/', $source, $matches)) {
            $folderId = $matches[1];

            if (craft()->assets->canUserPerformAction($folderId, 'removeFromAssetSource')) {
                $action = craft()->elements->getAction('SafeDelete_DeleteAssets');

                $action->setParams(
                    [
                        'label' => Craft::t('Safe Delete…'),
                    ]
                );
                $actions[] = $action;
            }
        }

        return $actions;
    }

    public function addEntryActions($source)
    {
        $actions = [];
        $section = null;

        if (preg_match('/^section:(\d+)$/', $source, $matches)) {
            $section = craft()->sections->getSectionById($matches[1]);
        }

        $userSessionService = craft()->userSession;

        if ($section !== null &&
            $userSessionService->checkPermission('deleteEntries:'.$section->id) &&
            $userSessionService->checkPermission('deletePeerEntries:'.$section->id)
        ) {
            $action = craft()->elements->getAction('SafeDelete_Delete');

            $action->setParams(
                [
                    'label' => Craft::t('Safe Delete…'),
                ]
            );
            $actions[] = $action;
        }

        return $actions;
    }

    public function addCategoryActions($source)
    {
        $actions = [];

        // Get the group we need to check permissions on
        if (preg_match('/^group:(\d+)$/', $source, $matches)) {
            $group = craft()->categories->getGroupById($matches[1]);
        }

        if (!empty($group)) {
            // Delete
            $action = craft()->elements->getAction('SafeDelete_Delete');
            $action->setParams(
                [
                    'label' => Craft::t('Safe Delete…'),
                ]
            );
            $actions[] = $action;
        }

        return $actions;
    }
}