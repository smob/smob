=========================================================
SMOB detailed installation instructions in Debian/Ubuntu
=========================================================

Getting and installing all the software
========================================

#. Install required software
   Install the following packages (required dependencies will also be installed) :

   $ sudo aptitude install libapache2-mod-php5 libapache2-mod-proxy-html php-mysql php5-curl mysql-server git

#. Download SMOB code from github:

   $ git clone https://github.com/smob/smob.git
   
   If you plan to contribute, you will a github account and clone the project with write permission: 
   
   $ git clone git@github.com:smob/smob.git

#. Download the required PHP libraries
   Download arc2:
   
   $ git clone https://github.com/semsol/arc2.git
   
   Download xmlrpc from http://sourceforge.net/projects/phpxmlrpc/files/phpxmlrpc/3.0.0beta/xmlrpc-3.0.0.beta.tar.gz/download
   
#. Move the libraries to SMOB folder
   Move arc2 into lib/arc:

   $ mv /your/path/to/arc2 /your/path/to/smob/lib/arc

   Extract xmlrpc into lib/xmlrpc:

   $ unzip xmlrpc-3.0.0.beta.tar.gz
   $ mv /your/path/to/xmlrpc-3.0.0.beta /your/path/to/smob/lib/xmlrpc
   
#. Make the config directory writable by your web server:

   $ sudo chown www-data /your/path/to/smob/config

Configuring apache2
===================

#. Make sure that you have the required apache2 modules installed and enabled. If you run:

   $ sudo ls -l /etc/apache2/mods-enabled
   
   You will see at least:

    lrwxrwxrwx 1 root root 30 Feb 22 16:51 rewrite.load -> ../mods-available/rewrite.load
    lrwxrwxrwx 1 root root 27 Nov 25 11:22 php5.conf -> ../mods-available/php5.conf
    lrwxrwxrwx 1 root root 27 Nov 25 11:22 php5.load -> ../mods-available/php5.load
    lrwxrwxrwx 1 root root 33 Feb 22 16:38 proxy_html.conf -> ../mods-available/proxy_html.conf
    lrwxrwxrwx 1 root root 33 Feb 22 16:38 proxy_html.load -> ../mods-available/proxy_html.load
    
   If you don't have those files, you will have to enable or install the modules. To enable the modules:
   
   $ sudo a2enmod rewrite php5
   
   To install the modules:
   
   $ sudo aptitude install libapache2-mod-php5 libapache2-mod-proxy-html
   


Configure SMOB to use HTTP basic authentication
------------------------------------------------

#. You can use the example file in auth/.htpasswd for user admin and password admin or create your custom .htpasswd file using http://www.htaccesstools.com/htpasswd-generator/ and pasting the result in a .htpasswd file.

#. Edit the file /your/path/to/smob/smob/auth/.htaccess and replace the .htpasswd file path with your own . It will look like:

    AuthUserFile /your/path/to/smob/.htpasswd
    AuthGroupFile /dev/null
    AuthName "Restricted area !"
    AuthType Basic
    <Limit GET>
    require valid-user
    </Limit>

Configure the domain
---------------------

Using localhost
~~~~~~~~~~~~~~~~
   
Warning: this configuration is not recommended for production servers.

#. Change your apache default configuration virtualhost in /etc/apache2/sites-enabled/000-default. In the "<Directory /var/www/>" section change "AllowOverride None" to "AllowOverride All".

#. Move smob folder into /var/www/:

  $ mv /your/path/to/smob /var/www/
  
#. Open http://localhost/smob in your browser. If everthing went ok, you should be able to install SMOB from your browser.


Using a custom virtual domain
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

#. If you prefer to serve SMOB from a URL like http://example.com/ instead of http://example.com/smob/, comment the following line in /your/path/to/smob/.htaccess

   #RewriteBase /smob/

#. Create your apache2 virtualhost file or customize the example in apache2/smob copying it to the apache2 path:

   $ cp /your/path/to/smob/apache2/smob /etc/apache2/sites-available/example.com
   
   Customize etc/apache2/sites-available/example.com
   
   Enable the virtual domain and reload apache2:
   
   $ sudo a2ensite example.com
   $ sudo /etc/init.d/apache2 reload

#. Open http://example.com in your browser. If everthing went ok, you should be able to install SMOB from your browser.



Troubleshooting
==================

#. The MySQL database is created but the tables aren't
   Check if you have arc library inside SMOB folder under lib/arc 

#. When you try to access any other page except the index.php (Example: Deleting a post, RSS, Other, RDF), you will get an File not found error in the apache logs
   Check whether you have enabled the rewrite module
   Check the line "RewriteBase /smob/" in the  /your/path/to/smob/.htaccess file

#. Authentication not working. (Internal Server Error: 500)
Check whether the file /your/path/to/smob/auth/.htaccess is edited properly 


