# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

  config.vm.box = "ubuntu/trusty64"
  config.vm.provision :shell, :path => "ansible/provision.sh"

  config.vm.hostname="vagrant.tmlpstats.com"

  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "private_network", ip: "192.168.56.102"

  config.vm.synced_folder ".", "/vagrant", :nfs => true
end
