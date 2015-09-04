# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

  config.vm.box = "hashicorp/precise32"
  config.vm.provision :shell, path: "bin/bootstrap.sh"

  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.hostname="vagrant-dev.com"
  config.vm.network "private_network", ip: "192.168.56.102"

  # Using NFS because VirtualBox's shared drive feature a super slow
  # For more, see  https://docs.vagrantup.com/v2/synced-folders/basic_usage.html
  # Need to disable the default folder, else it will interfear with /vagrant/tmlpstats
  config.vm.synced_folder ".", "/vagrant", disabled: true
  config.vm.synced_folder ".", "/vagrant/tmlpstats", :nfs => true
end
