<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Watchlog\Model\ResourceModel\Attempts;

/**
 * Custom functions collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_watchlogHelper = null;
    protected $_datetime = null;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Wyomind\Watchlog\Helper\Data $watchlogHelper,
        \Magento\Framework\Stdlib\DateTime\Datetime $datetime,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_watchlogHelper = $watchlogHelper;
        $this->_datetime = $datetime;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Wyomind\Watchlog\Model\Attempts', 'Wyomind\Watchlog\Model\ResourceModel\Attempts');
    }

    public function getHistory($date = null)
    {
        if ($date != null) {
            $this->addFieldToFilter('date', ['gteq' => $date]);
        }
        $this->getSelect()
                ->columns('COUNT(id) as attempts')
                ->columns('MAX(date) as date')
                ->columns('SUM(IF(status=0,1,0)) as failed')
                ->columns('SUM(IF(status=1,1,0)) as succeeded')
                ->order("SUM(IF(status=0,1,0)) DESC")
                ->group("ip");
        return $this;
    }

    public function getSummaryDay()
    {
        $this->getSelect()
                ->columns('COUNT(id) as nb')
                ->where("date >= '" . $this->_datetime->date('Y-m-d H:i:s') . "' - INTERVAL 23 HOUR")
                ->group("concat(hour(date))")
                ->order("date asc")
                ->group("status");

        return $this;
    }

    public function getSummaryMonth()
    {

        $nbDays = $this->_watchlogHelper->getDefaultConfig("watchlog/settings/history");

        $this->getSelect()
                ->columns('COUNT(id) as nb')
                ->columns("CONCAT(year(date),'-',month(date),'-',day(date)) as date")
                ->where("date > '" . $this->_datetime->date('Y-m-d H:i:s') . "' - INTERVAL $nbDays DAY")
                ->order("date asc")
                ->group("concat(year(date),'-',month(date),'-',day(date))")
                ->group("status");
        return $this;
    }

    public function getFailedPercentFromDate($date = null)
    {
        $this->getSelect()
                ->columns('SUM(IF(status=0,1,0)) as failed')
                ->columns('COUNT(id) as total')
                ->where("date >= '" . $date . "'");
        $first = $this->getFirstItem();
        if ($first != null && $first->getTotal() > 0) {
            return $first->getFailed() / $first->getTotal();
        } else {
            return 0;
        }
    }
    
    public function purge($before)
    {
        
        $resource = $this->_resource;
        $watchlog = $resource->getTable('watchlog');
        return $resource->getConnection()->delete($watchlog, "date < '" . $this->_datetime->gmtDate('Y-m-d H:i:s', $before) . "'");
    }
}