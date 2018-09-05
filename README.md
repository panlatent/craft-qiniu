Craft Qiniu
===========
[![Build Status](https://travis-ci.org/panlatent/craft-qiniu.svg)](https://travis-ci.org/panlatent/craft-qiniu)
[![Coverage Status](https://coveralls.io/repos/github/panlatent/craft-qiniu/badge.svg?branch=master)](https://coveralls.io/github/panlatent/craft-qiniu?branch=master)
[![Latest Stable Version](https://poser.pugx.org/panlatent/craft-qiniu/v/stable.svg)](https://packagist.org/packages/panlatent/craft-qiniu)
[![Total Downloads](https://poser.pugx.org/panlatent/craft-qiniu/downloads.svg)](https://packagist.org/packages/panlatent/craft-qiniu) 
[![Latest Unstable Version](https://poser.pugx.org/panlatent/craft-qiniu/v/unstable.svg)](https://packagist.org/packages/panlatent/craft-qiniu)
[![License](https://poser.pugx.org/panlatent/craft-qiniu/license.svg)](https://packagist.org/packages/panlatent/craft-qiniu)
[![Craft CMS](https://img.shields.io/badge/Powered_by-Craft_CMS-orange.svg?style=flat)](https://craftcms.com/)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)

![Screenshot](resources/img/qiniu.png)

Qiniu Cloud Storage plugin for Craft CMS 3. 

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

        composer require panlatent/craft-qiniu

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Qiniu.

Configuration
-------------

1. New a volume and set volume type: `Qiniu Volume`

2. Set `Access Key` and `Secret Key` [See Values on Qiniu](https://portal.qiniu.com/user/key)

3. Set `bucket` and the volume's `public URLs`, the value is URL from `bucket` bound URLs.

