<?php

namespace Chottvn\SigninPhoneNumber\Model\Handler;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Config\Share as ConfigShare;
use Chottvn\SigninPhoneNumber\Api\SigninInterface;
use Chottvn\SigninPhoneNumber\Helper\Data as HelperData;

/**
 * Class Signin
 * Handle login using the phone number instead of the email as default.
 */
class Signin implements SigninInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param StoreManagerInterface $storeManager
     * @param HelperData $helperData
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        StoreManagerInterface $storeManager,
        HelperData $helperData
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled($scopeCode = null)
    {
        return (bool) $this->helperData->isActive($scopeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getSigninMode($scopeCode = null)
    {
        return $this->helperData->getSigninMode($scopeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getByPhoneNumber(string $phone)
    {
        $this->writeLog("func:getByPhoneNumber");
        $websiteIdFilter[] = $this->filterWebsiteShare();

        // Add customer attribute filter
        $customerFilter[] = $this->filterBuilder
            ->setField(\Chottvn\SigninPhoneNumber\Setup\InstallData::PHONE_NUMBER)
            ->setConditionType('eq')
            ->setValue($phone)
            ->create();

        // Build search criteria
        $searchCriteriaBuilder = $this->searchCriteriaBuilder->addFilters($customerFilter);
        if (!empty($websiteIdFilter)) {
            $searchCriteriaBuilder->addFilters($websiteIdFilter);
        }
        $searchCriteria = $searchCriteriaBuilder->create();

        // Retrieve customer collection.
        $collection = $this->customerRepository->getList($searchCriteria);
        $this->writeLog("func:getByPhoneNumber - total count in collection customer".$collection->getTotalCount());
        if ($collection->getTotalCount() > 0) {
            // Return last occurrence.
            $accs = [];
            $accounts = $collection->getItems();
            foreach($accounts as $acc) {
                $this->writeLog("func:getByPhoneNumber - ids of each account: ".$acc->getId());
            }
            if($collection->getTotalCount() > 1) {
                $accs[] = array_pop($accounts);
                $this->writeLog("func:getByPhoneNumber - Pop account id: ".$accs[0]->getId());
                return reset($accs);
            }
            return reset($accounts);
        }
        return false;
    }

    /**
     * Add website filter if customer accounts are shared per website.
     *
     * @return FilterBuilder|boolean
     */
    private function filterWebsiteShare()
    {
        if ($this->helperData->getCustomerShareScope() == ConfigShare::SHARE_WEBSITE) {
            return $this->filterBuilder
                ->setField('website_id')
                ->setConditionType('eq')
                ->setValue($this->storeManager->getStore()->getWebsiteId())
                ->create();
        }
        return false;
    }

	/**
	 * @param $info
	 * @param $type  [error, warning, info]
	 * @return 
	 */
	private function writeLog($info, $type = "info")
	{
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/handler_signin.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		switch ($type) {
			case "error":
				$logger->err($info);
				break;
			case "warning":
				$logger->notice($info);
				break;
			case "info":
				$logger->info($info);
				break;
			default:
				$logger->info($info);
		}
	}
}
