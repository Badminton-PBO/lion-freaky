#!/usr/bin/bash

echo "Updating lionfreaky"
cd lion-freaky
git stash
git pull --rebase
git stash pop

echo "copying lf_front to public.www"
cp -rf lf_front/* /customers/9/1/f/badminton-pbo.be/httpd.www/competitie/.
cp /customers/9/1/f/badminton-pbo.be/httpd.www/competitie/index2.php /customers/9/1/f/badminton-pbo.be/httpd.www/competitie/index.php 

