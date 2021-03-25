#!/usr/bin/env bash
export DEBIAN_FRONTEND=noninteractive

# Locales
sudo locale-gen

# UTC
sudo ln -fs /usr/share/zoneinfo/Etc/Universal /etc/localtime
sudo dpkg-reconfigure -f noninteractive tzdata

# Update
sudo add-apt-repository ppa:ondrej/php
sudo add-apt-repository ppa:ondrej/nginx
sudo apt update -y
sudo apt install -y vim git curl wget zip supervisor build-essential libssl-dev software-properties-common ca-certificates apt-transport-https gnupg-agent

# Mysql (5.7 --> https://www.claudiokuenzler.com/blog/991/install-mysql-5.7-on-ubuntu-20.04-focal-avoid-8.0-packages)
echo "### THIS FILE IS AUTOMATICALLY CONFIGURED ###
# You may comment out entries below, but any other modifications may be lost.
# Use command 'dpkg-reconfigure mysql-apt-config' as root for modifications.
deb http://repo.mysql.com/apt/ubuntu/ bionic mysql-apt-config
deb http://repo.mysql.com/apt/ubuntu/ bionic mysql-5.7
deb http://repo.mysql.com/apt/ubuntu/ bionic mysql-tools
#deb http://repo.mysql.com/apt/ubuntu/ bionic mysql-tools-preview
deb-src http://repo.mysql.com/apt/ubuntu/ bionic mysql-5.7" | sudo tee /etc/apt/sources.list.d/mysql.list
echo "Package: *
Pin: origin repo.mysql.com
Pin-Priority: 900

Package: *
Pin: origin ch.archive.ubuntu.com
Pin-Priority: 700

Package: *
Pin: origin security.ubuntu.com
Pin-Priority: 800" | sudo tee /etc/apt/preferences.d/mysql
sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys 8C718D3B5072E1F5
echo "mysql-community-server mysql-community-server/root-pass password secret" | sudo debconf-set-selections
echo "mysql-community-server mysql-community-server/re-root-pass password secret" | sudo debconf-set-selections
sudo apt update -y
sudo apt -y install mysql-server
# sudo mysql_install_db
# mysql_secure_installation
sudo mysql -u root -psecret -e "DELETE FROM mysql.user WHERE User=''"
sudo mysql -u root -psecret -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1')"
sudo mysql -u root -psecret -e "DROP DATABASE IF EXISTS test"
sudo mysql -u root -psecret -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%'"
sudo mysql -u root -psecret -e "CREATE DATABASE IF NOT EXISTS fatturapa DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_unicode_ci"
sudo mysql -u root -psecret -e "FLUSH PRIVILEGES"

# PHP 8
#sudo apt install -y php8.0-cli php8.0-fpm php8.0-mysqlnd php8.0-dom php8.0-mbstring php8.0-curl php-json php8.0-cgi php8.0-gd php8.0-bz2 php8.0-zip php8.0-bcmath php8.0-ctype php8.0-intl php8.0-soap php8.0-xml php8.0-gmp php-imagick php-redis php8.0-xdebug php8.0-imap

# PHP 7.4
sudo apt install -y php7.4-cli php7.4-fpm php7.4-mysqlnd php7.4-dom php7.4-mbstring php7.4-curl php7.4-json php7.4-cgi php7.4-gd php7.4-bz2 php7.4-zip php7.4-bcmath php7.4-ctype php7.4-intl php7.4-soap php7.4-xml php7.4-gmp php7.4-imagick php7.4-redis php7.4-xdebug php7.4-imap
sudo apt install -y libxmlsec1 libffi6 python-pip sendmail memcached libmemcached-tools

echo '[xdebug]'                     	| sudo tee /etc/php/7.4/fpm/conf.d/20-xdebug.ini
echo 'zend_extension=xdebug.so'         | sudo tee -a /etc/php/7.4/fpm/conf.d/20-xdebug.ini
echo 'xdebug.mode=develop,coverage,debug,trace' | sudo tee -a /etc/php/7.4/fpm/conf.d/20-xdebug.ini
echo 'xdebug.client_host=127.0.0.1' 	| sudo tee -a /etc/php/7.4/fpm/conf.d/20-xdebug.ini
echo 'xdebug.client_port=9003'      	| sudo tee -a /etc/php/7.4/fpm/conf.d/20-xdebug.ini
sudo cp /etc/php/7.4/fpm/conf.d/20-xdebug.ini /etc/php/7.4/cli/conf.d/20-xdebug.ini
sudo sed -i "s/;sendmail_path.*/sendmail_path='\/usr\/local\/bin\/mailhog sendmail noreply@fatturapa.local'/" /etc/php/7.4/fpm/php.ini
sudo sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php/7.4/fpm/php.ini
echo 'max_input_time = 5400'      | sudo tee -a /etc/php/7.4/fpm/php.ini # 90 minutes
echo 'post_max_size = 2G'         | sudo tee -a /etc/php/7.4/fpm/php.ini # 2Gb max file size.
echo 'upload_max_filesize = 2G'   | sudo tee -a /etc/php/7.4/fpm/php.ini
echo 'memory_limit=256M'          | sudo tee -a /etc/php/7.4/fpm/php.ini
sudo sed -i "s/^user = www-data/user = vagrant/" /etc/php/7.4/fpm/pool.d/www.conf
sudo sed -i "s/^group = www-data/group = vagrant/" /etc/php/7.4/fpm/pool.d/www.conf
sudo sed -i "s/^listen.owner = www-data/listen.owner = vagrant/" /etc/php/7.4/fpm/pool.d/www.conf
sudo sed -i "s/^listen.group = www-data/listen.group = vagrant/" /etc/php/7.4/fpm/pool.d/www.conf
sudo sed -i "s/^;listen.mode = 0660/listen.mode = 0660/" /etc/php/7.4/fpm/pool.d/www.conf

# Composer
wget -qO- https://getcomposer.org/installer | php
sudo mv composer.phar /usr/bin/composer

# Install crontab.
cat /home/vagrant/provision/crontab.conf |crontab -

# Nginx
sudo apt install -y python redis-server nginx
sudo mkdir /etc/nginx/ssl
sudo cp -r /home/vagrant/provision/nginx/ssl/* /etc/nginx/ssl/
sudo cp -r /home/vagrant/provision/nginx/sites-enabled/* /etc/nginx/sites-enabled/
sudo cp -r /home/vagrant/provision/nginx/conf.d/* /etc/nginx/conf.d/
sudo cp -r /home/vagrant/provision/supervisor/* /etc/supervisor/conf.d/
sudo sed -i "s/^user www-data/ user vagrant/" /etc/nginx/nginx.conf

# Install oh-my-zsh
sudo apt-get -y install zsh
sudo git clone git://github.com/robbyrussell/oh-my-zsh.git /home/vagrant/.oh-my-zsh
sudo cp /home/vagrant/provision/zshrc /home/vagrant/.zshrc
sudo chsh -s $(which zsh) vagrant
sudo git clone https://github.com/zsh-users/zsh-syntax-highlighting.git /home/vagrant/.oh-my-zsh/custom/plugins/zsh-syntax-highlighting

# SSH Keys
cp /home/vagrant/provision/ssh/id_rsa* /home/vagrant/.ssh/
chmod 400 /home/vagrant/.ssh/id_rsa*

# FatturaPA
cd /home/vagrant/fatturapa
sudo cp /home/vagrant/provision/fatturapa/core_config.php /home/vagrant/fatturapa/core/config.php
sudo cp /home/vagrant/provision/fatturapa/soap_config.php /home/vagrant/fatturapa/soap/config.php
sudo cp /home/vagrant/provision/fatturapa/env.fatturapa /home/vagrant/fatturapa/rpc/.env
sudo chown -R vagrant:vagrant /home/vagrant/fatturapa
composer install
composer update
sudo chown vagrant core/storage/time_travel.json
cd rpc
touch storage/logs/laravel.log
sudo chown -R vagrant storage/logs
sudo chmod g+w storage/logs/laravel.log
sudo chown -R vagrant storage/framework
sudo chown -R vagrant bootstrap/cache
php artisan key:generate
php artisan migrate:fresh --seed
cd /home/vagrant/fatturapa/core
php vendor/bin/phinx migrate -c config-phinx.php

# Restart services
sudo service nginx restart
sudo service php7.4-fpm restart
