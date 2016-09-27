<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-sphinx
 * @version   1.0.34
 * @copyright Copyright (C) 2016 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Controller\Adminhtml\Synonym;

use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\TestFramework\Helper\Bootstrap;

class MassDeleteTest extends AbstractBackendController
{
    public function setUp()
    {
        $this->resource = 'Mirasvit_Search::search_synonym';
        $this->uri = 'backend/search/synonym/massDelete';

        parent::setUp();
    }

    /**
     * @covers Mirasvit\Search\Controller\Adminhtml\Synonym\MassDelete::execute
     * @magentoDataFixture Mirasvit/Search/_files/Controller/Synonym/synonyms.php
     */
    public function testEmptyData()
    {
        $this->dispatch('backend/search/synonym/massDelete');

        $this->assertSessionMessages(
            $this->isEmpty(),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertSessionMessages(
            $this->contains('Please select synonym(s).'),
            MessageInterface::TYPE_ERROR
        );

        $this->assertTrue($this->getResponse()->isRedirect(), 'Redirect not exists.');
    }

    /**
     * @covers Mirasvit\Search\Controller\Adminhtml\Synonym\Delete::execute
     * @magentoDataFixture Mirasvit/Search/_files/Controller/Synonym/synonyms.php
     */
    public function testSuccess()
    {
        $data = [
            'synonym' => [1, 2]
        ];

        $this->getRequest()->setParams($data);

        $this->dispatch('backend/search/synonym/massDelete');

        $this->assertTrue($this->getResponse()->isRedirect(), 'No redirect at delete page.');

        $this->assertSessionMessages(
            $this->isEmpty(),
            MessageInterface::TYPE_ERROR
        );
        $this->assertSessionMessages(
            $this->contains('A total of 2 record(s) have been deleted.'),
            MessageInterface::TYPE_SUCCESS
        );
    }
}
