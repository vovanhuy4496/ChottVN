<?php
/**
 * Chottvn
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 */
namespace Chottvn\OfflineShipping\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Chottvn\OfflineShipping\Api\Data;
use Chottvn\OfflineShipping\Api\TablerateRepositoryInterface;
use Chottvn\OfflineShipping\Model\ResourceModel;


class TablerateRepository implements TablerateRepositoryInterface
{

    private $resourceTablerate;

    private $tablerateFactory;

  
    function __construct(
        ResourceModel\Tablerate $resourceTablerate,
        Data\TablerateInterfaceFactory $tablerateFactory
    ) {
        $this->resourceTablerate = $resourceTablerate;
        $this->tablerateFactory = $tablerateFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\TablerateInterface $tablerate)
    {
        try {
            $this->resourceTablerate->save($tablerate);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $tablerate;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($tablerateId)
    {
        $tablerate = $this->tablerateFactory->create();
        $this->resourceTablerate->load($tablerate, $tablerateId);
        if (!$tablerate->getId()) {
            throw new NoSuchEntityException(__('Tablerate with id "%1" does not exist', $tablerateId));
        }
        return $tablerate;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\TablerateInterface $tablerate)
    {
        try {
            $this->resourceTablerate->delete($tablerate);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $tablerate;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($tablerateId)
    {
        return $this->delete($this->getById($tablerateId));
    }
}