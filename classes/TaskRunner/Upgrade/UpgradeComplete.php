<?php

/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade;

use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Configuration;

/**
 * Ends the upgrade process and displays the success message.
 */
class UpgradeComplete extends AbstractTask
{
    public function run()
    {
        $this->logger->info($this->container->getState()->getWarningExists() ?
            $this->translator->trans('Upgrade process done, but some warnings have been found.', array(), 'Modules.Autoupgrade.Admin') :
            $this->translator->trans('Upgrade process done. Congratulations! You can now reactivate your shop.', array(), 'Modules.Autoupgrade.Admin')
        );

        $this->next = '';

        if ($this->container->getUpgradeConfiguration()->get('channel') != 'archive' && file_exists($this->container->getFilePath()) && unlink($this->container->getFilePath())) {
            $this->logger->debug($this->translator->trans('%s removed', array($this->container->getFilePath()), 'Modules.Autoupgrade.Admin'));
        } elseif (is_file($this->container->getFilePath())) {
            $this->logger->debug('<strong>' . $this->translator->trans('Please remove %s by FTP', array($this->container->getFilePath()), 'Modules.Autoupgrade.Admin') . '</strong>');
        }

        if ($this->container->getUpgradeConfiguration()->get('channel') != 'directory' && file_exists($this->container->getProperty(UpgradeContainer::LATEST_PATH)) && FilesystemAdapter::deleteDirectory($this->container->getProperty(UpgradeContainer::LATEST_PATH))) {
            $this->logger->debug($this->translator->trans('%s removed', array($this->container->getProperty(UpgradeContainer::LATEST_PATH)), 'Modules.Autoupgrade.Admin'));
        } elseif (is_dir($this->container->getProperty(UpgradeContainer::LATEST_PATH))) {
            $this->logger->debug('<strong>' . $this->translator->trans('Please remove %s by FTP', array($this->container->getProperty(UpgradeContainer::LATEST_PATH)), 'Modules.Autoupgrade.Admin') . '</strong>');
        }

        // Reinit config
        Configuration::deleteByName('PS_AUTOUP_IGNORE_REQS');
        // removing temporary files
        $this->container->getFileConfigurationStorage()->cleanAll();
    }
}
