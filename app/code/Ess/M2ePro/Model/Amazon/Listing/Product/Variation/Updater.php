<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Listing\Product\Variation;

class Updater extends \Ess\M2ePro\Model\Listing\Product\Variation\Updater
{
    private $parentListingsProductsForProcessing = array();

    //########################################

    public function process(\Ess\M2ePro\Model\Listing\Product $listingProduct)
    {
        if ($this->checkChangeAsVariationProduct($listingProduct)) {
            return;
        }

        if ($this->checkChangeAsNotVariationProduct($listingProduct)) {
            return;
        }

        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if (!$variationManager->isVariationProduct()) {
            return;
        }

        $this->checkVariationStructureChanges($listingProduct);
    }

    public function afterMassProcessEvent()
    {
        foreach ($this->parentListingsProductsForProcessing as $listingProduct) {
            /** @var \Ess\M2ePro\Model\Amazon\Listing\Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();
            $amazonListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();
        }
    }

    //########################################

    private function checkChangeAsVariationProduct(\Ess\M2ePro\Model\Listing\Product $listingProduct)
    {
        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        $magentoProduct = $listingProduct->getMagentoProduct();

        if (!$magentoProduct->isProductWithVariations() || $variationManager->isVariationProduct()) {
            return false;
        }

        if ($magentoProduct->isSimpleTypeWithCustomOptions() || $magentoProduct->isBundleType()) {
            $listingProduct->setData(
                'is_general_id_owner', \Ess\M2ePro\Model\Amazon\Listing\Product::IS_GENERAL_ID_OWNER_NO
            );
            $listingProduct->setData('template_description_id', null);
        }

        $listingProduct->getChildObject()->setData('is_variation_product', 1);
        $variationManager->setRelationParentType();
        $variationManager->getTypeModel()->resetProductAttributes(false);
        $variationManager->getTypeModel()->getProcessor()->process();

        return true;
    }

    private function checkChangeAsNotVariationProduct(\Ess\M2ePro\Model\Listing\Product $listingProduct)
    {
        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        $isVariationMagentoProduct = $listingProduct->getMagentoProduct()->isProductWithVariations();

        if ($isVariationMagentoProduct || !$variationManager->isVariationProduct()) {
            return false;
        }

        $variationManager->getTypeModel()->clearTypeData();

        if ($variationManager->isRelationParentType()) {
            $listingProduct->setData('general_id', NULL);
            $listingProduct->setData(
                'is_general_id_owner', \Ess\M2ePro\Model\Amazon\Listing\Product::IS_GENERAL_ID_OWNER_NO
            );
            $listingProduct->setData('status', \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED);

            $listingProduct->delete();
        } else {
            $variationManager->setSimpleType();
        }

        return true;
    }

    // ---------------------------------------

    private function checkVariationStructureChanges(\Ess\M2ePro\Model\Listing\Product $listingProduct)
    {
        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if ($variationManager->isRelationParentType()) {
            $this->parentListingsProductsForProcessing[$listingProduct->getId()] = $listingProduct;
            return;
        }

        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager\PhysicalUnit $typeModel */
        $typeModel = $variationManager->getTypeModel();

        if (!$listingProduct->getMagentoProduct()->isSimpleType()) {
            $typeModel->inspectAndFixProductOptionsIds();
        }

        if (!$typeModel->isActualProductAttributes()) {

            if ($variationManager->isRelationChildType()) {
                $this->parentListingsProductsForProcessing[$typeModel->getParentListingProduct()->getId()]
                    = $typeModel->getParentListingProduct();
                return;
            }

            /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager\Type\Individual $typeModel */

            $typeModel->resetProductVariation();

            return;
        }

        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager\PhysicalUnit $typeModel */

        if ($typeModel->isVariationProductMatched() && !$typeModel->isActualProductVariation()) {

            if ($variationManager->isRelationChildType()) {
                /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager\Type\Relation\ChildRelation $typeModel */
                $this->parentListingsProductsForProcessing[$typeModel->getParentListingProduct()->getId()]
                    = $typeModel->getParentListingProduct();
                return;
            }

            $typeModel->unsetProductVariation();
        }

        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager\Type\Relation\ChildRelation $typeModel */

        if ($variationManager->isRelationChildType() &&
            $typeModel->getParentTypeModel()->getVirtualChannelAttributes()
        ) {
            if (!$typeModel->getParentTypeModel()->isActualVirtualChannelAttributes()) {
                $this->parentListingsProductsForProcessing[$typeModel->getParentListingProduct()->getId()]
                    = $typeModel->getParentListingProduct();
            }
        }
    }

    //########################################
}