<?php
namespace Oscprofessionals\Vatexempt\Model;

class VatdeclareModel extends \Magento\Framework\Model\AbstractModel
{
    protected $_registry;
    protected $_backendSession;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,

        array $data = []
    )
    {
        $this->_registry = $registry;
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    /**
     * @return int $vatDeclare
     */
    public function getVatdeclareStatusFromModel($params)
    {
        if (array_key_exists('vatdeclare', $params)) {
            $vatDeclare = $params['vatdeclare'];
        } else {
            $vatDeclare = 'false';
        }

        if ($vatDeclare == 'true') {
            $vatDeclare = 1;
        } else {
            if ($vatDeclare == 'false') {
                $vatDeclare = 0;
            }
        }
        $_SESSION['vatdeclare'] = $vatDeclare;


        return $vatDeclare;
    }

    public function getSessionVatdeclareStatus()
    {
        if(!array_key_exists('vatdeclare',$_SESSION))
        {
            $_SESSION['vatdeclare']=0;
        }
        return $_SESSION['vatdeclare'];

    }
}