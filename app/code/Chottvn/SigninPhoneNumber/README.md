# Sign in With Phone Number for Magento 2

This extension allow your customers to login to your Magento store using their phone number. Mobile login extention for Magento 2.
- Login with mobile phone number or email.
- Possibility to create account using mobile phone number.
- Change phone number under customer dashboard.


## Installation

### Install via composer

### Using GIT clone

## Activation

Run the following command in Magento 2 root folder:
```sh
php bin/magento module:enable Chottvn_SigninPhoneNumber
```
```sh
php bin/magento setup:upgrade
```

Clear the caches:
```sh
php bin/magento cache:clean
```

## Configuration

1. Go to **STORES** > **Configuration** > **ChottVN** > **Sign in With Phone Number**.
2. Select **Enabled** option to enable the module.
3. Under **Settings** tab, change the **Sign in Mode** to fit to your login process.

## Uninstall

```sh
php bin/magento module:uninstall -r Chottvn_SigninPhoneNumber
```

## Contribution

Want to contribute to this extension? The quickest way is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).


## Support

If you encounter any problems or bugs, please open an issue on [GitHub](https://github.com/chottvn/magento2-sign-in-with-phone-number/issues).

Need help setting up or want to customize this extension to meet your business needs? Please open an issue and I'll add this feature if it's a good one.
