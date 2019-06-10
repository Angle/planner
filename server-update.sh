#!/bin/sh

cd /var/www

echo --- Pull the most recent configuration files ---
sudo chmod 777 -R .
git stash
git pull
if [ $? -gt 0 ]; then
    chmod +x update.sh
    echo "Update stopped. Could not pull from remote repository."
else
    chmod +x server-update.sh
    chmod +x server-install.sh

    # composer now updates through apt-get
    #echo "-- Composer self-update"
    #composer self-update

    sudo chmod 777 -R .

    cd symfony

    echo "- Updating: Symfony App -"

    echo "-> Install packages with composer"
    composer install

    echo "-> Clear cache"
    php bin/console cache:clear --env=dev
    php bin/console cache:clear --env=prod

    echo "-> Fix Permissions"
    sudo chown -R www-data:www-data var/log
    sudo chown -R www-data:www-data var/cache
    sudo chmod 777 -R var/log
    sudo chmod 777 -R var/cache

    cd /var/www

    echo - Update database -
    php symfony/bin/console doctrine:schema:update --force --env=prod

    echo - Install Crontab -
    crontab config/crontab

fi
