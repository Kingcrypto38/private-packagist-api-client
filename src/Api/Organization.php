<?php

/*
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\ApiClient\Api;

class Organization extends AbstractApi
{
    public function sync()
    {
        return $this->post('/organization/sync');
    }
}
