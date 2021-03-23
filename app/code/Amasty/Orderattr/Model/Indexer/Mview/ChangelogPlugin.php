<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

//        .-'---`-.
//        ,'          `.
//        |             \
//        |              \
//        \           _  \
//        ,\  _    ,'-,/-)\
//        ( * \ \,' ,' ,'-)
//         `._,)     -',-')
//           \/         ''/
//            )        / /
//           /       ,'-'

namespace Amasty\Orderattr\Model\Indexer\Mview;

class ChangelogPlugin
{
    /**
     * @var Changelog
     */
    private $changelog;

    public function __construct(Changelog $changelog)
    {
        $this->changelog = $changelog;
    }

    /**
     * @param \Magento\Framework\Mview\View\Changelog $subject
     * @param \Closure $proceed
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function aroundCreate(\Magento\Framework\Mview\View\Changelog $subject, \Closure $proceed)
    {
        if ($this->isAmastyView($subject)) {
            $this->changelog->setViewId($subject->getViewId());
            return $this->changelog->create();
        } else {
            return $proceed();
        }
    }

    /**
     * @param \Magento\Framework\Mview\View\Changelog $subject
     * @param \Closure $proceed
     *
     * @return int|mixed
     * @throws \Magento\Framework\Mview\View\ChangelogTableNotExistsException
     */
    public function aroundGetVersion(\Magento\Framework\Mview\View\Changelog $subject, \Closure $proceed)
    {
        if ($this->isAmastyView($subject)) {
            $this->changelog->setViewId($subject->getViewId());
            return $this->changelog->getVersion();
        } else {
            return $proceed();
        }
    }

    /**
     * @param \Magento\Framework\Mview\View\Changelog $subject
     * @param \Closure $proceed
     *
     * @return mixed|void
     * @throws \Magento\Framework\Mview\View\ChangelogTableNotExistsException
     */
    public function aroundDrop(\Magento\Framework\Mview\View\Changelog $subject, \Closure $proceed)
    {
        if ($this->isAmastyView($subject)) {
            $this->changelog->setViewId($subject->getViewId());
            return $this->changelog->drop();
        } else {
            return $proceed();
        }
    }

    /**
     * @param \Magento\Framework\Mview\View\Changelog $subject
     * @param \Closure $proceed
     * @param int $versionId
     *
     * @return bool|mixed
     * @throws \Magento\Framework\Mview\View\ChangelogTableNotExistsException
     */
    public function aroundClear(\Magento\Framework\Mview\View\Changelog $subject, \Closure $proceed, $versionId)
    {
        if ($this->isAmastyView($subject)) {
            $this->changelog->setViewId($subject->getViewId());
            return $this->changelog->clear($versionId);
        } else {
            return $proceed($versionId);
        }
    }

    /**
     * @param \Magento\Framework\Mview\View\Changelog $subject
     * @param \Closure $proceed
     * @param int $fromVersionId
     * @param int $toVersionId
     *
     * @return int[]|mixed
     * @throws \Magento\Framework\Mview\View\ChangelogTableNotExistsException
     */
    public function aroundGetList(
        \Magento\Framework\Mview\View\Changelog $subject,
        \Closure $proceed,
        $fromVersionId,
        $toVersionId
    ) {
        if ($this->isAmastyView($subject)) {
            $this->changelog->setViewId($subject->getViewId());
            return $this->changelog->getList($fromVersionId, $toVersionId);
        } else {
            return $proceed($fromVersionId, $toVersionId);
        }
    }

    /**
     * @param \Magento\Framework\Mview\View\Changelog $subject
     *
     * @return bool
     */
    public function isAmastyView(\Magento\Framework\Mview\View\Changelog $subject)
    {
        return $subject->getViewId() && $subject->getViewId() === 'amasty_order_attribute_values'
            //possible strpos more save than full path to Interceptor
            && !strpos(get_class($subject), 'Orderattr');
    }
}
