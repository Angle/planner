#!/bin/bash
cd "$(dirname "$0")"

echo - Generate CertBot SSL Certificates -
sudo service apache2 stop
for i in "planner.angle.mx"
do
    sudo certbot certonly --standalone --force-renewal --non-interactive -d ${i} --agree-tos --email letsencrypt@angle.mx
done

sudo chmod 755 -R /etc/letsencrypt

sudo service apache2 start
