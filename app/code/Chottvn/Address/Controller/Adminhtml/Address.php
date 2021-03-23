<?php

namespace Chottvn\Address\Controller\Adminhtml;

abstract class Address extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Chottvn\Address\Model\ResourceModel\Address
     */
    protected $addressResource;

    /**
     * @var \Chottvn\Address\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @var \Chottvn\Address\Model\ResourceModel\Region
     */
    protected $regionResource;

    /**
     * @var \Chottvn\Address\Model\CityFactory
     */
    protected $cityFactory;

    /**
     * @var \Chottvn\Address\Model\ResourceModel\City
     */
    protected $cityResource;

    /**
     * @var \Chottvn\Address\Model\TownshipFactory
     */
    protected $townshipFactory;

    /**
     * @var \Chottvn\Address\Model\ResourceModel\Township
     */
    protected $townshipResource;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Chottvn\Address\Model\ResourceModel\Address $addressResource
     * @param \Chottvn\Address\Model\RegionFactory $regionFactory
     * @param \Chottvn\Address\Model\ResourceModel\Region $regionResource
     * @param \Chottvn\Address\Model\CityFactory $cityFactory
     * @param \Chottvn\Address\Model\ResourceModel\City $cityResource
     * @param \Chottvn\Address\Model\TownshipFactory $townshipFactory
     * @param \Chottvn\Address\Model\ResourceModel\Township $townshipResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Psr\Log\LoggerInterface $logger,
        \Chottvn\Address\Model\ResourceModel\Address $addressResource,
        \Chottvn\Address\Model\RegionFactory $regionFactory,
        \Chottvn\Address\Model\ResourceModel\Region $regionResource,
        \Chottvn\Address\Model\CityFactory $cityFactory,
        \Chottvn\Address\Model\ResourceModel\City $cityResource,
        \Chottvn\Address\Model\TownshipFactory $townshipFactory,
        \Chottvn\Address\Model\ResourceModel\Township $townshipResource
    ) {
        $this->filter = $filter;
        $this->coreRegistry = $coreRegistry;
        $this->csvProcessor = $csvProcessor;
        $this->resultRawFactory = $resultRawFactory;
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->logger = $logger;
        $this->addressResource = $addressResource;
        $this->regionFactory = $regionFactory;
        $this->regionResource = $regionResource;
        $this->cityFactory = $cityFactory;
        $this->cityResource = $cityResource;
        $this->townshipFactory = $townshipFactory;
        $this->townshipResource = $townshipResource;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Customer::customer');
        return $resultPage;
    }
}
