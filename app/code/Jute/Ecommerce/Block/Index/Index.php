<?php

namespace Jute\Ecommerce\Block\Index;

use Jute\Ecommerce\Block\BaseBlock;

class Index extends BaseBlock {

    /**
     * Returns action url for contact form. Form submit URL
     *
     * @return string
     */
    public function getFormAction() {
        return $this->getUrl('ecommerce/index/post', ['_secure' => true]);
    }

}
