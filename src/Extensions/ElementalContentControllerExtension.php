<?php

namespace DNADesign\Elemental\Extensions;

use DNADesign\Elemental\Models\ElementalArea;
use DNADesign\Elemental\Extensions\ElementalAreasExtension;
use SilverStripe\Core\Extension;

class ElementalContentControllerExtension extends Extension
{
    /**
     * @var array
     */
    private static $allowed_actions = array(
        'handleElement'
    );

    public function handleElement()
    {
        $id = $this->owner->getRequest()->param('ID');

        if (!$id) {
            user_error('No element ID supplied', E_USER_ERROR);
            return false;
        }

        /** @var SiteTree $elementOwner */
        $elementOwner = $this->owner->data();

        $elementalAreaRelations = $this->owner->getElementalRelations();

        if (!$elementalAreaRelations) {
            user_error(get_class($this->owner) . ' has no ElementalArea relationships', E_USER_ERROR);
            return false;
        }

        foreach ($elementalAreaRelations as $elementalAreaRelation) {
            $elements = $elementOwner->$elementalAreaRelation()->Elements();

            $virtualElementClass = "DNADesign\ElementalVirtual\Model\ElementVirtual";
            if (class_exists($virtualElementClass)) {
                $elements = $elements->leftJoin("ElementVirtual", "\"ElementVirtual\".\"ID\" = \"Element\".\"ID\"");
            }

            $element = $elements
                ->filterAny([
                    'ID' => $id,
                    "LinkedElementID" => $id
                ])
                ->First();

            if (class_exists($virtualElementClass) && $element instanceof $virtualElementClass) {
                return $element->LinkedElement()->getController();
            }

            if ($element) {
                return $element->getController();
            }
        }

        user_error('Element $id not found for this page', E_USER_ERROR);
        return false;
    }
}
