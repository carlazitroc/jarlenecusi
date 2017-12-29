# Dynamotor CI-3 Bundle

Version: 3.0

A framework to build a tools to manage content easlier which is based on Codeigniter(CI) 3.

## Server Requirement

General

- Free disk-space: 1 GB or more

- RAM: 512Mb or more 

Linux based system

- Preferred Operation System: CentOS 6.5 / Ubuntu 14.04 

- Software / Packages: 

    - Web Server: 

        Nginx 1.8 

        Apache 2.2 with rewrite module (mod_rewrite)

    - MySQL 5.5 

    - PHP 5.5 (and PHP-FPM) with extensions: 

        gd, bcmath, json, xml, mysqli, pdo, mcrypt, intl, curl 

    

## File Structure

All system contexts are stored under "_webContent" folder.

There are few folders you should reminded and be careful:

- "apps"

    All applications are stored under "apps" folder. There are 2 applications inside this framework by default - "admin" and "portal". 

    Structure of each application:

    * "boot.php" 

        It is the application-level config file for app based setting.

    * "index.php"

        It is the semi-orginial copy of application start file. Part of content moved to "boot.php" for sharing config when loading "logviewer.php" and apps itself. Currently this file will be loaded from application starter to protect your files for security issue.

    * "config"

        Application's config folder. It's the orginial structure from CI framework.

        "admin" app's route.php file are modified for multi-language purpose

    * "controllers"

        Application's controller folder. It's the orginial structure from CI framework.

    * "core"

        Application's core folder. It's the orginial structure from CI framework.

    * "views"

        Application's view folder. It's the orginial structure from CI framework.

        You can create other orginial structures (Such as models, helpers, libraries, language) for your need. But we suggest to create it under a project-based package (config it in _webContent/config.php). 

- "tmp"
   
    Containing "cache" content and php session.
   
- "prvdata"

    Containing original copy of user uploaded files.
   
- "logs"

    Containing app's CI log content and php error log.

- "vendor"

    This is 3rd party library folders. It's managed by ```composer.json```. You should update your local build by execute command ```composer update``` in terminal.


## Installation

Before uploading files to server, please update the correct setting for your web server software:


- Windows / Linux with Apache

```text
AllowOverride All
```

- Linux with nginx + php5.5 

```text

# Prevent script files uploaded from admin panel.
location ~ ^\/pub\/.+\.(php|phtml|aspx|cs|vbs|php4|php5|cfm|pl|cgi)$ {
   deny all;
}

# Prevent file access under _webContent
location /_webContent/ {
   deny all;
}

location / {
   try_files $uri $uri/ /index.php;
}

location /admin/ {
   try_files $uri $uri/ /admin/index.php;

}

location /admin/preview/ {
   try_files $uri $uri/ /admin/preview/index.php;
}

```

To install a new platform, you should upload all contents under "_webContent" and related application's starter (_starter.php). 

> Folder "_webContent" is the system core part. No direct http request should be allowed under this directory for security issue.

> For Apache HTTPd, any http request will be blocked via ```.htaccess``` file. 

> You should create access rules for blocking access if you are using nginx or other server-side software.

If you need backend function, you have to upload "admin" folder too.

After files are uploaded to your server, please prepare few files and configured it correctly:

1. apps/{app-name}/config/database.php or apps/{app-name}/config/{env}/database.php

    Database connection setting file. You should always set it

2. apps/{app-name}/config/portal.php or {package}/config/portal.php

    Changed the encryption key for security and also your pub_url (Public data file) and asset_url (Asset url, it be loaded via CDN).

3. apps/{app-name}/config/ph.php or {package}/config/ph.php

    PostHelper setting file. Define what's content will be available.

4. apps/{app-name}/config/admin.php or {package}/config/admin.php

    Changed the encryption key for security.

5. apps/{app-name}/config/language.php 

    (Optional). Configure your system provided language (in locale code format)
    
    
After all files uploaded to server, open browser and go to ```http://your-domain.com/admin/setup```. This is a checking and setup tool for your project. It will give you basic information about file permission and database connection from your setting.

## 3rd Party Libraries via Composer

Started from version 3.0, all 3rd party libraries should be managed by ```composer```. Please run the command ```composer install``` to re-install the required packages if missing.

## Versioning

You can download this bundle and create new repository for managing your own project. Core library will be deliver by updating vendor context from command line tool ```composer```.