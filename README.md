Qiniu
=====
[![Build Status](https://travis-ci.org/gocraftcms/qiniu.svg)](https://travis-ci.org/gocraftcms/qiniu)
[![Coverage Status](https://coveralls.io/repos/github/gocraftcms/qiniu/badge.svg?branch=master)](https://coveralls.io/github/gocraftcms/qiniu?branch=master)
[![Latest Stable Version](https://poser.pugx.org/gocraft/qiniu/v/stable.svg)](https://packagist.org/packages/gocraft/qiniu)
[![Total Downloads](https://poser.pugx.org/gocraft/qiniu/downloads.svg)](https://packagist.org/packages/gocraft/qiniu) 
[![Latest Unstable Version](https://poser.pugx.org/gocraft/qiniu/v/unstable.svg)](https://packagist.org/packages/gocraft/qiniu)
[![License](https://poser.pugx.org/gocraft/qiniu/license.svg)](https://packagist.org/packages/gocraft/qiniu)
[![Craft CMS](https://img.shields.io/badge/Powered_by-Craft_CMS-orange.svg?style=flat)](https://craftcms.com/)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)

![Screenshot](resources/img/qiniu.png)

Qiniu Cloud Storage plugin for Craft 3. 

The plugin provide a `Qiniu Volume` can save files in the [Qiniu Cloud](https://www.qiniu.com/).

Requirements
------------

This plugin requires Craft CMS 3.0 or later.

Installation
------------

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require gocraft/qiniu

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Qiniu.

Configuration
-------------

1. New a volume and set volume type: `Qiniu Volume`

2. Set `Access Key` and `Secret Key` [See Values on Qiniu](https://portal.qiniu.com/user/key)

3. Set `bucket` and the volume's `public URLs`, the value is URL from `bucket` bound URLs.

