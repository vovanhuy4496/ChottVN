<?php

namespace Chottvn\DataFeed\Cron;

use \Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class GoogleDataFeed {

  protected $logger;
  protected $productCollectionFactory;
  protected $categoryFactory;
  protected $fileSystem;
  protected $io;
  protected $emulation;


  /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
   */
  protected $scopeConfig;

  const XML_PATH_GOOGLE_DATA_FEED_CONFIG = 'datafeed_configuration/google_datafeed_cron/data_config';
  const XML_PATH_GOOGLE_DATA_FEED_HOST_CONFIG = 'datafeed_configuration/google_datafeed_cron/host_config';
  const XML_PATH_GOOGLE_DATA_FEED_USERNAME_CONFIG = 'datafeed_configuration/google_datafeed_cron/username_config';
  const XML_PATH_GOOGLE_DATA_FEED_PASSWORD_CONFIG = 'datafeed_configuration/google_datafeed_cron/password_config';
  const XML_PATH_GOOGLE_DATA_FEED_DIRECTORY_CONFIG = 'datafeed_configuration/google_datafeed_cron/directory_config';
  const XML_PATH_GOOGLE_DATA_FEED_EXECUTE_CONFIG = 'datafeed_configuration/google_datafeed_cron/execute_config';

  public function __construct(
    LoggerInterface $logger,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Catalog\Model\CategoryFactory $categoryFactory,
    \Magento\Framework\Filesystem $fileSystem,
    \Magento\Framework\Filesystem\Io\File $io,
    \Magento\Store\Model\App\Emulation $emulation
  ) {
    $this->logger = $logger;
    $this->scopeConfig = $scopeConfig;
    $this->categoryFactory = $categoryFactory;
    $this->productCollectionFactory = $productCollectionFactory;
    $this->fileSystem = $fileSystem;
    $this->io = $io;
    $this->emulation = $emulation;
  }

  public function execute() {
    $_store = 1;
    $this->emulation->startEnvironmentEmulation($_store, \Magento\Framework\App\Area::AREA_FRONTEND, true);
    // [{"type":"google","run":"all","path":"datafeeds/test/google","enable":"1","currency":"đ"},{"type":"facebook","run":"2","path":"datafeeds/test/facebook","enable":"1","currency":"VND"}]
    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    $feed_config = $this->scopeConfig->getValue(self::XML_PATH_GOOGLE_DATA_FEED_EXECUTE_CONFIG, $storeScope);
    if($feed_config){
      // decode json
      $feed_config = json_decode($feed_config);
      foreach ($feed_config as $feed) {
        // check enable
        if($feed->enable){
          // get category ids 
          $cateogryIds = $this->getCategoryIds($feed->run);
          // get feed path
          $datafeed_path = $feed->path;
          /// get currency
          $currency = $feed->currency;

          // run script
          $this->runDataFeed($cateogryIds, $datafeed_path, $currency);
        }
      }
    }
    $this->emulation->stopEnvironmentEmulation();
  }

  public function runDataFeed($cateogryIds, $datafeed_path, $currency){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $priceHelper = $objectManager->get('Chottvn\PriceDecimal\Helper\Data');
    $brandHelper = $objectManager->get('Ves\Brand\Helper\ProductBrand');
    // media path
    $mediapath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    $datafeed_dir = $mediapath.$datafeed_path.'/';
    $array_category_file = array();

    // if dont exist datafeeds google => create folder
    if ( ! file_exists($datafeed_dir)) {
      $this->io->mkdir($datafeed_dir, 0775);
    }

    // store file xml
    if($cateogryIds){
      try {
        foreach ($cateogryIds as $categoryId) {
          $feed_data = '';
          $category = $this->categoryFactory->create()->load($categoryId);
          if($category->getName() != ''){
            $feed_filename = $this->toSlug($category->getName()).'.xml';
            // echo $feed_filename.'-'.$category->getName().'<br>';

            // initialize data xml
            $feed_data .= "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
            $feed_data .= "<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\">";
              $feed_data .= "<channel>";
                // foreach item product add to data
                $products = $this->getProductCollection($categoryId);
                foreach ($products as $product) {
                  // echo $product->getProductUrl();
                  $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
                  // echo '<pre>';print_r($product->getSku().'-'.strip_tags($priceHelper->formatPriceSpace($product->getPriceInfo()->getPrice('final_price')->getValue())).'-'.$product->getPriceInfo()->getPrice('regular_price')->getValue());echo '</pre>';
                  // echo $product->getProductUrl();exit;
                  $feed_data .= "<item>";
                  $feed_data .= "<g:id>".$product->getSku()."</g:id>";
                  $feed_data .= "<title><![CDATA[".$product->getNameShort()."]]></title>";
                  $feed_data .= "<description><![CDATA[".strip_tags(trim($product->getShortDescription()))."]]></description>";
                  $feed_data .= "<link>".$this->replaceHttpToHttps($product->getProductUrl())."</link>";
                  $feed_data .= "<g:country>VN</g:country>";
                  $feed_data .= "<g:language>vi</g:language>";
                  // image
                  $images = $product->getMediaGalleryImages();
                  $i = 0;
                  foreach ($images as $image) {
                    if($i == 0){
                      $feed_data .= "<g:image_link>".$this->replaceHttpToHttps($image->getUrl())."</g:image_link>";
                    }else{
                      $feed_data .= "<g:additional_image_link><![CDATA[".$this->replaceHttpToHttps($image->getUrl())."]]></g:additional_image_link>";
                    }
                    $i++;
                  }

                  $price_convert = strip_tags($priceHelper->formatPriceSpace($product->getPriceInfo()->getPrice('final_price')->getValue(),$currency));
                  if($price_convert == '0 '.$currency){
                    $price_convert = '';
                  }
                  $feed_data .= "<g:condition>new</g:condition>";
                  $feed_data .= "<g:price>".$price_convert."</g:price>";
                  $feed_data .= "<g:shipping><g:country>VN</g:country><g:price>0 ".$currency."</g:price></g:shipping>";
                  $feed_data .= "<g:availability><![CDATA[In Stock]]></g:availability>";
                  $feed_data .= "<g:google_product_category></g:google_product_category>";
                  $feed_data .= "<g:identifier_exists>FALSE</g:identifier_exists>";
                  $feed_data .= "<g:product_type><![CDATA[".$category->getName()."]]></g:product_type>";
                  $feed_data .= "<g:sale_price></g:sale_price>";
                  // get brand name
                  $brandProduct = $brandHelper->getFirstBrandByProduct($product);
                  $feed_data .= "<g:brand><![CDATA[".$brandProduct->getName()."]]></g:brand>";
                  $feed_data .= "</item>";
                }
              $feed_data .= "</channel>";
            $feed_data .= "</rss>";

            // save xml to drive
            $data_array = array(
                          'filename' => $feed_filename,
                          'filename_dir' => $datafeed_dir . $feed_filename
                          );
            $array_category_file[] = $data_array;
            $myFeedFile = fopen($datafeed_dir . $feed_filename, "w") or die("Unable to open file!");
            fwrite($myFeedFile, $feed_data);
            fclose($myFeedFile);
          }
        }
      } catch (\Exception $e) {
        $this->writeLog('--- Can\'t store file XML ---');
        $this->writeLog($e->getMessage());
      }

      // upload XML to ftp server
      try {
        foreach ($array_category_file as $category_file) {
          $localfile = $category_file['filename_dir'];
          $remotefile = $category_file['filename'];
          $host = $this->getDataFeedConfiguration(self::XML_PATH_GOOGLE_DATA_FEED_HOST_CONFIG);
          $username = $this->getDataFeedConfiguration(self::XML_PATH_GOOGLE_DATA_FEED_USERNAME_CONFIG);
          $password = $this->getDataFeedConfiguration(self::XML_PATH_GOOGLE_DATA_FEED_PASSWORD_CONFIG);
          $upload = $this->uploadToFtpServer($localfile,$remotefile,$host,$username,$password,$datafeed_path);
          $this->writeLog($upload);
        }
      } catch (\Exception $e) {
        $this->writeLog('--- Update XML to ftp server ---');
        $this->writeLog($e->getMessage());
      }
    }
  }

  public function getProductCollection($categoryId) {
    $category = $this->categoryFactory->create()->load($categoryId);
    $collection = $this->productCollectionFactory->create();
    $collection->addAttributeToSelect('*');
    $collection->addCategoryFilter($category);
    $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
    $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
    return $collection;
  }

  public function getCategoryIds($run) {
    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    switch ($run) {
      case 'all':
        $value = $this->scopeConfig->getValue(self::XML_PATH_GOOGLE_DATA_FEED_CONFIG, $storeScope);
        // decode json
        $value = json_decode($value);
        // get object category_ids
        $value = explode(',',$value->category_ids);
        break;
      
      default:
        $value = explode(',',$run);
        break;
    }
    return $value;
  }

  public function getDataFeedConfiguration($value) {
    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    $value = $this->scopeConfig->getValue($value, $storeScope);

    return $value;
  }

  function toSlug($str) {
    $str = trim(mb_strtolower($str));
    $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
    $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
    $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
    $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
    $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
    $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
    $str = preg_replace('/(đ)/', 'd', $str);
    $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
    $str = preg_replace('/([\s]+)/', '-', $str);
    return $str;
  }

  function uploadToFtpServer($localfile,$remotefile,$host,$username,$password,$datafeed_path){
    $ch = curl_init();
    $fp = fopen($localfile, 'r');
    curl_setopt($ch, CURLOPT_URL, 'ftp://'.$username.':'.$password.'@'.$host.'/'.$datafeed_path.'/'.$remotefile);
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localfile));
    curl_exec ($ch);
    $error_no = curl_errno($ch);
    curl_close ($ch);
    if ($error_no == 0) {
        $mess = 'File uploaded succesfully for file '.$localfile;
    } else {
        $mess = 'File upload error for file '.$localfile;
    }

    return $mess;
  }

  function replaceHttpToHttps($url){
    $url = preg_replace("/^http:/i", "https:", $url);

    return $url;
  }

  /**
   * @param $info
   * @param $type  [error, warning, info]
   * @return 
   * $this->writeLog($e->getMessage());
   */
  private function writeLog($info, $type = "info")
  {
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/crontab_google_datafeed_create.log');
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