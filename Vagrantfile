# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

  config.vm.box = "ubuntu/trusty64"
  config.vm.provision :shell, :path => "ansible/provision.sh"

  # Change this to something smaller if your machine doesn't have much memory
  config.vm.provider :virtualbox do |vb|
    vb.memory = 2048
  end

  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "private_network", ip: "192.168.56.102"

  config.vm.synced_folder ".", "/vagrant", :nfs => true
end
