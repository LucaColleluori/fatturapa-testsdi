#!/usr/bin/env bash

cp ../provision/ssl/star.fatturapa.local.crt /usr/share/ca-certificates/star.fatturapa.local.crt
echo "star.fatturapa.local.crt" >> /etc/ca-certificates.conf
sudo update-ca-certificates --fresh