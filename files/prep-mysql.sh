mysql -u root -e "CREATE DATABASE ocdevon;"
mysql -u root -e "CREATE USER ocdevon@'%' IDENTIFIED BY 'password';"
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO ocdevon@'%' with grant option; flush privileges;"

