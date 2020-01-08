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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner;

use PrestaShop\Module\AutoUpgrade\AjaxResponse;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\TaskRepository;

/**
 * Execute the whole process in a single request, useful in CLI.
 */
abstract class ChainedTasks extends AbstractTask
{
    protected $step;

    /**
     * Execute all the tasks from a specific initial step, until the end (complete or error).
     *
     * @return int Return code (0 for success, any value otherwise)
     */
    public function run()
    {
        $requireRestart = false;
        while ($this->canContinue() && !$requireRestart) {
            $this->logger->info('=== Step ' . $this->step);
            $controller = TaskRepository::get($this->step, $this->container);
            $controller->init();
            $controller->run();

            $result = $controller->getResponse();
            $requireRestart = $this->checkIfRestartRequested($result);
            $this->error = $result->getError();
            $this->stepDone = $result->getStepDone();
            $this->step = $result->getNext();
        }

        return (int) ($this->error || $this->step === 'error');
    }

    /**
     * Customize the execution context with several options.
     *
     * @param array $options
     */
    abstract public function setOptions(array $options);

    /**
     * Tell the while loop if it can continue.
     *
     * @return bool
     */
    protected function canContinue()
    {
        if (empty($this->step)) {
            return false;
        }

        if ($this->error) {
            return false;
        }

        return $this->step !== 'error';
    }

    /**
     * For some steps, we may require a new request to be made.
     * Return true for stopping the process.
     */
    protected function checkIfRestartRequested(AjaxResponse $response)
    {
        return false;
    }
}
