<?php

namespace Craft;

class SafeDeleteService extends BaseApplicationComponent
{
    public function getUsagesFor($ids, $type)
    {
        $relations = [];

        foreach ($ids as $id) {
            switch ($type) {
                case 'asset':
                case 'element':
                    $res = $this->getRelationsForElement($id);

                    if (count($res) > 0) {
                        $relations[] = $res;
                    }
                    break;
            }
        }

        return $relations;
    }

    /**
     * Returns an array only with ids which are
     * not referneced and safe to delete.
     *
     * @param $ids
     * @param $type
     * @return array
     */
    public function filterReferencedIds($ids, $type)
    {
        $arrIds = [];
        $arrRet = [];

        $relations = craft()->safeDelete->getUsagesFor($ids, $type);

        foreach ($relations as $elements) {
            foreach($elements as $element) {
                $arrIds[] = $element['sourceElement']->id;
            }
        }

        foreach($ids as $id) {
            if (!in_array($id, $arrIds)) {
                $arrRet[] = $id;
            }
        }

        return $arrRet;
    }

    protected function getRelationsForElement($id)
    {
        $arrReturn = [];

        $sourceElement = craft()->elements->getElementById($id);

        $results = craft()->db->createCommand()->select('fieldId, sourceId')->from('relations')->where(
            'targetId = :targetId',
            ['targetId' => $id]
        )->queryAll();

        foreach ($results as $relation) {
            $fieldId = $relation['fieldId'];
            $sourceId = $relation['sourceId'];

            $field = craft()->fields->getFieldById($fieldId);
            $element = craft()->elements->getElementById($sourceId);
            if ($element !== null) {
                $elementType = $element->getElementType();
                $parent = null;
                $editUrl = null;

                switch ($elementType) {
                    case 'MatrixBlock':
                        $matrix = craft()->matrix->getBlockById($sourceId);
                        $parent = $this->getTopOwner($matrix);
                        break;
                    case 'Neo_Block':
                        $neo = craft()->neo->getBlockById($sourceId);
                        $parent = $this->getTopOwner($neo);
                        break;
                }

                // if the element is referenced but not used in any entry, continue
                if (($elementType == 'MatrixBlock' || $elementType == 'NeoBlock') && !$parent) {
                    continue;
                }

                $edit = $element;
                if ($parent !== null) {
                    $edit = $parent;
                }

                switch ($edit->getElementType()) {
                    case 'Entry':
                        $editUrl = '/entries/' . $edit->section->handle . '/' . $edit->id;
                        break;
                }

                $arrReturn[] = [
                    'sourceElement' => $sourceElement,
                    'field'         => $field,
                    'element'       => $element,
                    'parent'        => $parent,
                    'editUrl'       => $editUrl,
                ];
            }
        }

        return $arrReturn;
    }

    /**
     * Get the top owner of the given element
     *
     * @param $element
     * @return mixed
     */
    private function getTopOwner($element)
    {
        if (!method_exists($element, 'getOwner')) {
            return null;
        }

        $parent = $element->getOwner();

        while ($parent) {
            if (method_exists($parent, 'getOwner')) {
                $parent = $parent->getOwner();
            } else {
                // getOwner() is not possible anymore
                break;
            }
        }

        return $parent;
    }
}
