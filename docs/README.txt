
README
======




DB
==

Steps to create the database:
 mysql
 create user 'rprj@' identified by 'rprj_pwd'
 create database rprj;
 grant all on rprj.* to 'rprj@' with grant option;
 flush privileges;
 \q

Test the connection




