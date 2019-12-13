# VRE_data_api

### Dependencies
- Web server  (e.g. Apache2 Nginx)
- PHP and composer
- VRE MongoDB

### Usage

##### 1. Fetch VRE token
Log-in into VRE and copy you access token from `My profile > API keys`
##### 2. Access VRE data via REST
Store your token into a shell variable to use it in all your REST calls. Notice that after a certain time elapse the access token expires and you'll need to fetch it again (step 1).

```
token=eyJhbGciOiJSUzI1NiIsInR5cCIgOi...
curl -H "Authorization: Bearer $token" http://you.site.es/[ENDPOINTS]
```

The list of available endpoints can be checked at: http://you.site.es/doc

### Installation

##### 1. Clone the repository
##### 2. Install 3rd party modules:
Use `composer` to install the PHP libraries required to run the application
```
cd VRE_data_api
composer update
```
##### 3. Create log file:
Create and empty file that the application is going to use as log file. Make sure that the UNIX user of the web server has write permissions:
```
touch logs/app.log
chmod 777 logs/app.log
```

##### 4. Configure web access:

The web server (e.g. Apache2 or Nginx) is to be set so that the `public/` folder of the application is accessible from the internet. In the following example, the **DocumentRoot** of an Apache2 server is pointing to the installation path :

```
<VirtualHost *:80>

        ServerName my.site.es

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html

        Alias "/vre/" "/installation/path/VRE_data_api/public/"

        <Directory "/installation/path/VRE_data_api/public/">
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

</VirtualHost>
```

```
sudo systemctl restart apache2
```
Now, you can test that application is running calling the root endpoint. Any browser should return the welcome message under the URL configured on your web server name. Example:
```
curl -L localhost
```
```
Hello, this is <strong>vre</strong> resource API. <br/>Local Repository: whatever_vre<br/>
```

##### 4.Configure application:
Configure all the particulars of the installation at `app/settings.php`. Take as template the following file:

```
cp app/settings.php.sample   app/settings.php

```
Configure the following key parameters:
- settings.logger : Configure the log module
- settings.db: Configure the connection with the VRE Mongo database
- globals.dataDir: Path to the VRE data folder (userdata).
- globals.api:  OAuth2 endpoints of the OIDC authentication server


