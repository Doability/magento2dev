<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Helper\Module\Maintenance;

use Ess\M2ePro\Helper\Factory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;

class Setup extends \Ess\M2ePro\Helper\AbstractHelper
{
    const CONFIG_PATH = 'm2epro/setup_maintenance/mode';

    private $resourceConnection;

    //########################################

    public function __construct(
        Factory $helperFactory,
        Context $context,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($helperFactory, $context);
        $this->resourceConnection = $resourceConnection;
    }

    //########################################

    public function isEnabled()
    {
        $select = $this->resourceConnection->getConnection()
            ->select()
            ->from($this->resourceConnection->getTableName('core_config_data'), 'value')
            ->where('scope = ?', 'default')
            ->where('scope_id = ?', 0)
            ->where('path = ?', self::CONFIG_PATH);

        return (bool)$this->resourceConnection->getConnection()->fetchOne($select);
    }

    //########################################

    public function enable()
    {
        $select = $this->resourceConnection->getConnection()
            ->select()
            ->from($this->resourceConnection->getTableName('core_config_data'), 'value')
            ->where('scope = ?', 'default')
            ->where('scope_id = ?', 0)
            ->where('path = ?', self::CONFIG_PATH);

        if ($this->resourceConnection->getConnection()->fetchOne($select) === false) {
            $this->resourceConnection->getConnection()->insert(
                $this->resourceConnection->getTableName('core_config_data'),
                [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => self::CONFIG_PATH,
                    'value' => 1
                ]
            );
            return;
        }

        $this->resourceConnection->getConnection()->update(
            $this->resourceConnection->getTableName('core_config_data'),
            ['value' => 1],
            [
                'scope = ?' => 'default',
                'scope_id = ?' => 0,
                'path = ?' => self::CONFIG_PATH,
            ]
        );
    }

    public function disable()
    {
        $this->resourceConnection->getConnection()->update(
            $this->resourceConnection->getTableName('core_config_data'),
            ['value' => 0],
            [
                'scope = ?' => 'default',
                'scope_id = ?' => 0,
                'path = ?' => self::CONFIG_PATH,
            ]
        );
    }

    //########################################
}