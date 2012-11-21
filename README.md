xyz-platform
============
XYZ is a free and open source Polls and Survey Questions Platform.
Responses could be trough input, check box or semantic differential scales.

To use it you need a web server with:
- [PHP](http://php.net/)
- [Zend Framework](http://framework.zend.com/)
- [MySQL](http://www.mysql.com/)

Instructions
- Upload all files to your web server;
- Point the domain to `public` directory;
- Configure your MySQL database as shown in the `extras/documentation/xyz_SQL_ER.mwb` file. Use [MySQL Workbench](http://www.mysql.com/products/workbench/) to open it;
- Modify the `application/configs/application.ini` file with your MySQL authentication params;
- Start inserting questions and check box/ semantic differential scales options directly in database tables. [PhpMyAdmin](http://www.phpmyadmin.net/) suggested to do it.

Results can be accessed via http://your-web-site/result
