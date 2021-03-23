/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

/**
 * Define Themes
 *
 * area: area, one of (frontend|adminhtml|doc),
 * name: theme name in format Vendor/theme-name,
 * locale: locale,
 * files: [
 * 'css/styles-m',
 * 'css/styles-l'
 * ],
 * dsl: dynamic stylesheet language (less|sass)
 *
 */
module.exports = {      
    chottvn_default: {
        area: 'frontend',
        name: 'Chottvn/default',
        locale: 'vi_VN',        
        files: [
            'css/styles-m',
            'css/styles-l',
            // 'css/ytextend',
            // 'css/yttheme',            
            'css/main'           
        ],
        dsl: 'less'
    }
};
