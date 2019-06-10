#!/bin/bash
# version 3.0 2014.12.0

echo Starting installation procedure..

lsb_release -a
OS=$(lsb_release -si)
VER=$(lsb_release -sr)
if [ $OS != "Ubuntu" ] || [ $VER != "18.04" ]
then
    echo "Invalid OS Version, cannot install."
    exit 1
fi

echo -e "\e[1m--- Ubuntu updates & configuration ---\e[0m"
echo - Update Repositories -
sudo apt-get update > /dev/null

echo --- Install NTP ---
sudo apt-get install -y ntp ntpdate
sudo service ntp stop
sudo ntpdate 3.ubuntu.pool.ntp.org
sudo service ntp start

echo -e "\e[1m--- Install CertBot (Let's Encrypt) ---\e[0m"
sudo apt-get install software-properties-common
sudo add-apt-repository ppa:certbot/certbot -y
sudo apt-get update
sudo apt-get install -y certbot

echo -e "\e[1m--- Install Apache2 ---\e[0m"
sudo apt-get install -y apache2
sudo a2enmod rewrite

echo - Enable Apache2 SSL -
sudo a2enmod ssl
sudo service apache2 restart

echo -e "\e[1m--- Install PHP7.2 ---\e[0m"
sudo apt-get install -y php7.2
sudo apt-get install -y libapache2-mod-php7.2

echo -e "\e[1m- Install PHP7.2 MySQL Drivers -\e[0m"
sudo apt-get install -y php7.2-mysql

echo -e "\e[1m- Install PHP7.2 cURL -\e[0m"
sudo apt-get install -y php7.2-curl

echo -e "\e[1m- Install PHP7.2 additional extensions -\e[0m"
sudo apt-get install -y php7.2-xml php7.2-zip php7.2-bcmath php7.2-mbstring

echo -e "\e[1m- Restarting Apache2 & loading modules.. -\e[0m"
sudo service apache2 restart
#sudo systemctl reload apache2

echo -e "\e[1m--- Installing Beanstalkd ---\e[0m"
sudo apt-get install -y beanstalkd

echo -e "\e[1m--- Install PHP Composer globally ---\e[0m"
sudo apt-get install -y composer

echo -e "\e[1m--- User permisssions ---\e[0m"
sudo adduser ubuntu www-data
sudo chown -R www-data:www-data /var/www
sudo chmod -R g+rw /var/www
sudo chmod g+s /var/www

## Change the default umask in apache
# umask 002 to create files with 0664 and folders with 0775
# Check the Apache2 Environment Variables and set the usmask ONLY if it has not been set before
# I have no idea how this one liner works..
sudo grep -q -F 'umask 002' /etc/apache2/envvars || echo 'umask 002' | sudo tee -a /etc/apache2/envvars
sudo service apache2 restart

echo -e "\e[1m--- Configure Apache & Virtual Hosts ---\e[0m"
echo - Prepare log files -
sudo mkdir /etc/apache2/logs
sudo chmod 777 -R /etc/apache2/logs

echo - Disable Default Virtual Hosts -
sudo a2dissite 000-default

echo - Install Catch-All Virtual Hosts -
sudo cp config/vhost/zzz-catch-all.conf /etc/apache2/sites-available/zzz-catch-all.conf
sudo a2ensite zzz-catch-all

# Fix permissions before installing composer
sudo chmod -R 777 /var/www

echo - Install Symfony Applications VHosts -
for i in "app"
do
    echo "Installing: ${i}"
    sudo cp config/vhost/${i}.conf /etc/apache2/sites-available/${i}.conf
    sudo a2ensite ${i}
done

echo "Configuring LogRotate"
sudo cp config/logrotate.conf /etc/logrotate.d/sf_ghp_suite
sudo chmod 644 /etc/logrotate.d/sf_ghp_suite

sudo service apache2 restart
#sudo systemctl reload apache2

echo "[All Done]"