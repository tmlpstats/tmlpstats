# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.provision :shell, :path => "ansible/provision.sh"

  if RUBY_PLATFORM =~ /darwin/ or RUBY_PLATFORM =~ /linux/
    ### Unix-specific
    config.vm.box = "ubuntu/trusty64"

    # Change this to something smaller if your machine doesn't have much memory
    config.vm.provider :virtualbox do |vb|
      vb.memory = 2048
    end
    config.vm.synced_folder ".", "/vagrant", :nfs => true
  else
    ### Windows-specific config goes here
    config.vm.box = "ericmann/trusty64"

    config.vm.provider :hyperv do |hyperv|
      hyperv.memory = 2048
    end
    config.vm.synced_folder ".", "/vagrant-src"
    config.vm.network "public_network"
  end

  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "private_network", ip: "192.168.56.102"

end
