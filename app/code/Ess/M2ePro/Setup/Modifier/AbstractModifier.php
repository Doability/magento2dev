<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Setup\Modifier;

use Ess\M2ePro\Helper\Factory;
use Ess\M2ePro\Setup\Tables;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Module\Setup;

class AbstractModifier
{
    /** @var Setup */
    protected $installer;

    /** @var AdapterInterface */
    protected $connection;

    /** @var Tables */
    protected $tablesObject;

    protected $tableName = NULL;
    protected $queriesLog = array();

    //########################################

    public function __construct(
        Setup $installer,
        Factory $helperFactory,
        Tables $tablesObject,
        $tableName
    ) {
        $this->installer  = $installer;
        $this->connection = $installer->getConnection();

        $this->helperFactory = $helperFactory;
        $this->tablesObject  = $tablesObject;

        if (!$this->tablesObject->isExists($tableName)) {
            throw new \Ess\M2ePro\Model\Exception\Setup("Table Name does not exist.");
        }

        $this->tableName = $this->tablesObject->getFullName($tableName);
    }

    //########################################

    public function runQuery($query)
    {
        $this->addQueryToLog($query);

        $this->connection->query($query);
        $this->connection->resetDdlCache();

        return $this;
    }

    public function addQueryToLog($query)
    {
        $this->queriesLog[] = $query;
        return $this;
    }

    // ---------------------------------------

    public function setQueriesLog(array $queriesLog = array())
    {
        $this->queriesLog = $queriesLog;
        return $this;
    }

    public function getQueriesLog()
    {
        return $this->queriesLog;
    }

    //########################################
}