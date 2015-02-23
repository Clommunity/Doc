# Service Addition in Cloudy

## A brief introduction

This guide will walk you through the process of adding a new service to
Cloudy.

In order to be clear and provide examples, we are going to be integrating a
very simple service showing snippets of code along the way.

For the sake of simplicity, we will use
[Pastecat](https://github.com/mvdan/pastecat). It is a good candidate because:

 * It is standalone and doesn't federate nor communicate with other nodes
 * Built with Go, it's easy to distribute and deploy
 * It doesn't need a configuration file nor any kind of setup
 * It is very lightweight on resources

## 1. Getting the binary

The first thing we have to figure out is how to download and install the
binary on Cloudy. Most software out there is already available as a package
on Debian, but Pastecat isn't. If it were, it would be a matter of just
running the command `apt-get install pastecat` from PHP.

But since it isn't, we'll have to get it from someplace else. One option is to
fetch the source and build it ourselves. But this often means that Cloudy
should include a lot of build tools and libraries. In the case of Go, that
would mean having its toolchain installed, which isn't very practical.

The better option if a debian package isn't available is to download binaries
from upstream over HTTPS and preferably with digests or signatures. We can use
Github's releases page for that. Both options leave us with an executable file
that we should be able to run directly on Cloudy.

Note that having the service as a Debian package has many advantages:

* Updates are simple and need no extra work from Cloudy
* The package is compiled and built by Debian in a trusted way
* An init.d file is already provided
* Debian packages often contain small patches and fixes

## 2. Testing it out

Before adding it as a service, we want to configure and start it up ourselves
directly to see how it works and that it actually works. Understand what
configuration options or command line options will we need to use for this
service in particular, and how will we manage the service once it is running.

## 3. Adding the controller

In `web/plug/controllers` we have one PHP file per service, called the
controller. This is the code that will run when we enter the services page on
the Cloudy web interface.

### 3.1. Adding the index function

Whe want our service integrated in the Cloudy web structure. In order to do this,
a few php scripts need to be created and added to our device. Altgether, and by
the time being, we'll need to create a total of 2 scripts: `pastecat.php` and
`pastecat.menu.php`. The first one is the controller itself, this is, the script
that renders the page and has all the information such as buttons or redirections.
The other one is what allows our service to show up in the upper menu of Cloudy.

The menu script will look like this:


    <?php
    //peerstreamer.menu.php
    addMenu('Pastecat','pastecat','Clommunity');

By now, we'll use a very simple php script as the controller:

    <?php
    //pastecat
    $title="Pastecat";
    
    function index(){
        global $paspath,$title;
        global $staticFile;
    
        $page=hlc(t($title));
        $page .= hl(t("Minimalist pastebin engine written in Go"),4);
        $page .= par(t("A simple and self-hosted pastebin service written in Go").' '.t("Can use a variety of storage backends").' '.t(" Designed to optionally remove pastes after a certain period of time.").' '.("If using a persistent storage backend, pastes will be kept between runs.").' '.t("This software runs the").' '."<a href='http://paste.cat'>".t("paste.cat")."</a>". t(" public service."));

        return(array('type' => 'render','page' => $page));
    }

In our Cloudy system, these files must be placed at `/var/local/cDistro/plug/` the
first one at `menus` directory and the second at `controllers` directory. Once we've
done this, we can go to our Cloudy system and access our new Pastecat.

### 3.2. Making the controller install the service

As said before, this step is made much more easier if the service is packaged
in Debian. Since pastecat isn't, we'll have to do it manually. This usually
involves a combination of `wget`, `mv` and `chmod`. It is generally a good
idea to keep the service's files under `/opt/SERVICENAME`.

In our particular case, the first thing we need to do is downloading the
binary from the release. In order to do this we will make use of the mentioned
`wget` command. Given a URL to a file, this command allows us to download this
file in our system, and this is what we will do in our system:

    wget https://github.com/mvdan/pastecat/archive/v0.2.2.zip

Once we have the binary, we just need to move it to a directory where executable
files use to be located. In our case, we will use the directory /opt/pastecat/.
To move these files through our system we will use the command `mv`. However,
first of all we need to create the directory where we will place our binary. To
do this we use the `mkdir` command as is shown below:

   mkdir -p /opt/pastecat

Once we have our directory created, it is time to move the binary there:

    mv current_directory/v0.2.2.zip /opt/pastecat

where current\_directory is the directory where we previously downloaded the
binary.

These steps are the minimum requiered to install a service which is not provided
in the Debian official repositories. However, to an end user, it could look like a
nightmare to run these commands in a console connected through ssh to its device,
so what we are going to do now, is create a bash script which will be called later
from the web interface by clicking a button.

AQUI HEM DE FER LO DEL BOTO EN EL HTML AMB LES FUNCIONS DE MIRAR SI LA CARPETA EXISTEIX I ETC...

## 4 Avahi service publishing

On of the best things in Cloudy is the facility of publishing our service as a
publication in the avahi network, allowing other users to know what we are
offering and joining our service. To make our new service visible in avahi we
need to create some files.
