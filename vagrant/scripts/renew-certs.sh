#!/usr/bin/env bash

ssh-keygen -f star.fatturapa.local.key -t rsa -N ''
openssl req \
    -newkey rsa:2048 \
    -x509 \
    -nodes \
    -keyout star.fatturapa.local.key \
    -new \
    -out star.fatturapa.local.crt \
    -config fatturapa-openssl.cnf \
    -sha256 \
    -days 365\
    -extensions v3_req

openssl rsa -in star.fatturapa.local.key -out star.fatturapa.local.key

mv star.fatturapa.local.crt ../provision/nginx/ssl/star.fatturapa.local.crt
mv star.fatturapa.local.key ../provision/nginx/ssl/star.fatturapa.local.key
mv star.fatturapa.local.key.pub ../provision/nginx/ssl/star.fatturapa.local.key.pub
