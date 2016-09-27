<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\SimpleGoogleShopping\Controller\Adminhtml\Feeds;

/**
 * Generate data feed action
 */
class Debug extends \Wyomind\SimpleGoogleShopping\Controller\Adminhtml\Feeds
{

    /**
     * Execute action
     */
    public function execute()
    {
        $request = $this->getRequest();

        $id = $request->getParam('simplegoogleshopping_id');
        $limit = $request->getParam('limit');

        $model = $this->_objectManager->create('Wyomind\SimpleGoogleShopping\Model\Feeds');
        $model->limit = $limit;
        $model->debugEnabled = true;
        $model->logEnabled = true;

        $model->load($id);
        $resultRaw = $this->resultRawFactory->create();


        try {
            $model->generateXml($request);

            return $resultRaw->setContents($model->debugData);
        } catch (\Exception $e) {
            return $resultRaw->setContents(__('Unable to generate the data feed.') . '<br/><br/>' . $e->getMessage());
        }

        return $resultRaw->setContents("");
    }
}
