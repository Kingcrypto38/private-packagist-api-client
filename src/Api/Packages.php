<?php

/*
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\ApiClient\Api;

use PrivatePackagist\ApiClient\Api\Packages\Artifacts;
use PrivatePackagist\ApiClient\Exception\InvalidArgumentException;

class Packages extends AbstractApi
{
    /**
     * Packages that are mirrored from a public mirrored third party repository like packagist.org.
     */
    const ORIGIN_PUBLIC_MIRROR = 'public-mirror';

    /**
     * Packages that are mirrored from a private mirrored third party repository requiring authentication like repo.magento.com.
     */
    const ORIGIN_PRIVATE_MIRROR = 'private-mirror';

    /**
     * All other packages from a VCS repository or a custom JSON definition.
     */
    const ORIGIN_PRIVATE = 'private';

    /**
     * @deprecated Use Packages::ORIGIN_PUBLIC_MIRROR instead
     */
    const ORIGIN_PUBLIC_PROXY = self::ORIGIN_PUBLIC_MIRROR;

    /**
     * @deprecated Use Packages::ORIGIN_PRIVATE_MIRROR instead
     */
    const ORIGIN_PRIVATE_PROXY = self::ORIGIN_PRIVATE_MIRROR;

    const AVAILABLE_ORIGINS = [self::ORIGIN_PUBLIC_MIRROR, self::ORIGIN_PRIVATE_MIRROR, self::ORIGIN_PRIVATE, 'public-proxy', 'private-proxy'];

    public function all(array $filters = [])
    {
        if (isset($filters['origin']) && !in_array($filters['origin'], self::AVAILABLE_ORIGINS, true)) {
            throw new InvalidArgumentException('Filter "origin" has to be one of: "' . implode('", "', self::AVAILABLE_ORIGINS) . '".');
        }

        return $this->get('/packages/', $filters);
    }

    public function show($packageName)
    {
        return $this->get(sprintf('/packages/%s/', $packageName));
    }

    public function createVcsPackage($url, $credentialId = null, $type = 'vcs')
    {
        return $this->post('/packages/', ['repoType' => $type, 'repoUrl' => $url, 'credentials' => $credentialId]);
    }

    public function createCustomPackage($customJson, $credentialId = null)
    {
        if (is_array($customJson) || is_object($customJson)) {
            $customJson = json_encode($customJson);
        }

        return $this->post('/packages/', ['repoType' => 'package', 'repoConfig' => $customJson, 'credentials' => $credentialId]);
    }

    public function createArtifactPackage(array $artifactPackageFileIds)
    {
        return $this->post('/packages/', ['repoType' => 'artifact', 'artifactIds' => $artifactPackageFileIds]);
    }

    /**
     * @deprecated Use editVcsPackage instead
     */
    public function updateVcsPackage($packageName, $url, $credentialId = null)
    {
        return $this->editVcsPackage($packageName, $url, $credentialId);
    }

    public function editVcsPackage($packageName, $url, $credentialId = null, $type = 'vcs')
    {
        return $this->put(sprintf('/packages/%s/', $packageName), ['repoType' => $type, 'repoUrl' => $url, 'credentials' => $credentialId]);
    }

    public function editArtifactPackage($packageName, array $artifactPackageFileIds)
    {
        return $this->put(sprintf('/packages/%s/', $packageName), ['repoType' => 'artifact', 'artifactIds' => $artifactPackageFileIds]);
    }

    /**
     * @deprecated Use editCustomPackage instead
     */
    public function updateCustomPackage($packageName, $customJson, $credentialId = null)
    {
        return $this->editVcsPackage($packageName, $customJson, $credentialId);
    }

    public function editCustomPackage($packageName, $customJson, $credentialId = null)
    {
        return $this->put(sprintf('/packages/%s/', $packageName), ['repoType' => 'package', 'repoConfig' => $customJson, 'credentials' => $credentialId]);
    }

    public function remove($packageName)
    {
        return $this->delete(sprintf('/packages/%s/', $packageName));
    }

    public function listCustomers($packageName)
    {
        return $this->get(sprintf('/packages/%s/customers/', $packageName));
    }

    public function listDependents($packageName)
    {
        return $this->get(sprintf('/packages/%s/dependents/', $packageName));
    }

    public function listSecurityIssues($packageName, array $filters = [])
    {
        return $this->get(sprintf('/packages/%s/security-issues/', $packageName), $filters);
    }

    public function showSecurityMonitoringConfig($packageName)
    {
        return $this->get(sprintf('/packages/%s/security-monitoring/', $packageName));
    }

    public function editSecurityMonitoringConfig($packageName, array $config)
    {
        return $this->put(sprintf('/packages/%s/security-monitoring/', $packageName), $config);
    }

    public function artifacts()
    {
        return new Artifacts($this->client, $this->client->getResponseMediator());
    }
}
