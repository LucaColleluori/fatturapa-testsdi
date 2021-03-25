# FatturaPa Vagrant #

Welcome to FatturaPa!! (I'm sorry!)

### Prerequisites ###

* SSH Key setup on Github Account.
* Vagrant
* VirtualBox / Parallels

### Checking out the repos. ###

* Checkout (fatturapa-testsdi).

```shell
cp ~/.ssh/id_rsa* {path}/{fatturaPa}/vagrant/provision/ssh/
```

NOTE: The latest code will be in the 'develop' branches.

### Hosts ###

192.168.10.24 fatturapa.local

* If the IP address conflicts with your LAN network, feel free to use another one. You'll need to change the IP address on the `Vagrantfile` under `config.vm.network "private_network", ip: "192.168.10.24"`.

### Starting Vagrant ###

* cd into `FatturaPa/vagrant` folder. Type `vagrant up`.
* All the urls should be accessible to https, you may need to create exceptions to handle self-signed certificates. The certs are located in `provision/ssl` folder on this repo. Do it, don't be an ass.

### Congratulate yourself! ###
