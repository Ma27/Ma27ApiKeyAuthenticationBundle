# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "precise64"
  config.vm.synced_folder ".", "/vagrant/auth-bundle"

  config.vm.hostname = "api-key-auth-vm"

  config.vm.provider "virtualbox" do |vb|
    vb.gui = true
    vb.memory = "1024"
    vb.cpus  = 1
    vb.name = "ApiKeyAuthentication VM"
  end

  config.vm.network :private_network, :ip => '193.68.45.123'

  config.vm.provision "shell", path: "provisioner.sh"
end
