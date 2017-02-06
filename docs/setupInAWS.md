# EC2 instance
Image: amazon linux
Both EC2 instance linked to Elastic IP
# SETUP
## As root
```
# Become root
sudo su -

# Install docker
yum install vim docker.x86_64 git
chkconfig --add docker

# Make sure ec2-user is part of the docker group so we don't have to run as root
usermod -a -G docker ec2-user

# Install docker-compose
curl -L "https://github.com/docker/compose/releases/download/1.10.1/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose
```
        
## As ec2-user (re-logon to be sure this user is part of "docker" group)
```
# Checkout sourcecode
mkdir checkouts
cd checkouts
git clone  https://github.com/Badminton-PBO/lion-freaky.git
cd lion-freaky

# Make storage directories writeable for docker containers
chmod -R 777 laravel/storage

# Update config
cp laravel/.env.production.example laravel/.env
#vim .env  (DB pwd, ports)
#vim laravel/.env   (DB pwd, mail, URLs)

# Build and both docker containers
docker-compose up -d
#docker-compose ps -> wait for php-compose to be finished
```