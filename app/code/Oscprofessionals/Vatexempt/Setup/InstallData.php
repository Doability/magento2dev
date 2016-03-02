<?php
/**
 *
 * @author      Oscprofessionals Team (support@oscprofessionals.com)
 * @copyright   Copyright (c) 2015 Oscprofessionals (http://www.oscprofessionals.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @category    Oscprofessionals
 * @package     Oscprofessionals_Vatexempt
 */
namespace Oscprofessionals\Vatexempt\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * Quote setup factory
     *
     * @var QuoteSetupFactory
     */
    protected $_quoteSetupFactory;

    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $_salesSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_quoteSetupFactory = $quoteSetupFactory;
        $this->_salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $varcharOptions = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => true, 'required' => false];

        $booleanOptions = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN, 'visible' => true, 'required' => false];

        /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
        $quoteSetup = $this->_quoteSetupFactory->create(['setup' => $setup]);
        $quoteSetup->addAttribute('quote', 'vatpername', $varcharOptions);
        $quoteSetup->addAttribute('quote', 'vatcomment', $varcharOptions);
        $quoteSetup->addAttribute('quote', 'vatdeclare', $booleanOptions);
        $quoteSetup->addAttribute('quote_item', 'vat_exempt', $booleanOptions);

        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->_salesSetupFactory->create(['setup' => $setup]);
        $salesSetup->addAttribute('order', 'vatpername', $varcharOptions);
        $salesSetup->addAttribute('order', 'vatcomment', $varcharOptions);
        $salesSetup->addAttribute('order', 'vatdeclare', $booleanOptions);
        $salesSetup->addAttribute('order_item', 'vat_exempt', $booleanOptions);

        /** @var Magento\Eav\Setup\EavSetupFactory $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'vat_exempt',
            [
                'group' => 'General',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Vat Exempt',
                'input' => 'select',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'unique' => false,
                'apply_to' => 'simple,configurable,virtual',
                'is_configurable' => false
            ]
        );
    }
}
