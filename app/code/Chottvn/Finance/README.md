# Mage2 Module Chottvn Finance

    ``chottvn/module-finance``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Chottvn Module Finance

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Chottvn`
 - Enable the module by running `php bin/magento module:enable Chottvn_Finance`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require chottvn/module-finance`
 - enable the module by running `php bin/magento module:enable Chottvn_Finance`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration

 - default_stock_max (cataloginventory/item_options/default_stock_max)


## Specifications

 - Model
	- Request

 - Model
	- TransactionType

 - Model
	- Transaction

 - Helper
	- Chottvn\Finance\Helper\Data

 - Helper
	- Chottvn\Finance\Helper\Transaction


## Attributes



