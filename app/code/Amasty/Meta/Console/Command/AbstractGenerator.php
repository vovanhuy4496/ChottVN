<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */


declare(strict_types=1);

namespace Amasty\Meta\Console\Command;

use Amasty\Meta\Helper\UrlKeyHandler;
use Symfony\Component\Console\Command\Command;
use Magento\Indexer\Model\Indexer\StateFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractGenerator extends Command
{
    /**
     * @var UrlKeyHandler
     */
    protected $helperUrl;

    /**
     * @var StateFactory
     */
    protected $stateFactory;

    public function __construct(
        UrlKeyHandler $helperUrl,
        StateFactory $stateFactory,
        string $name = null
    ) {
        parent::__construct($name);
        $this->helperUrl = $helperUrl;
        $this->stateFactory = $stateFactory;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->helperUrl->process($this->isNeedRedirect());
    }

    protected function isNeedRedirect()
    {
        return false;
    }
}
