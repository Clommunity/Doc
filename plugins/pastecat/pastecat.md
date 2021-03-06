# Service Addition in Cloudy

## A brief introduction

This guide will walk you through the process of adding a new service to
Cloudy.

In order to be clear and provide examples, we are going to be integrating a
very simple service showing snippets of code along the way.

For the sake of simplicity, we will use
[Pastecat](https://github.com/mvdan/pastecat). It is a good candidate because:

 * It's a standalone program
 * It only needs to be installed in one node (no federation/syncrhonization)
 * It's Built with Go, it's easy to distribute and deploy
 * It doesn't need a configuration file nor any kind of setup
 * It's very lightweight on resources

## 1. Getting the binary

The first thing we have to figure out is how to download and install the
binary on Cloudy. Most software out there is already available as a package
on Debian, but Pastecat isn't. If it were, it would be a matter of just
running the command `apt-get install pastecat` from PHP. Therefore we'll
have to get it from someplace else.

One option is to fetch the source and build it ourselves. This often means,
however, that Cloudy should include a lot of build tools and libraries. In the
case of Go, that would mean having its toolchain installed, which isn't very
practical.

A better option when the Debian package isn't available is to download the
binary from upstream trusted sources via HTTPS and, preferably, checking digests
or using signatures. We can use Github's releases page for that. Both options
leave us with an executable file that we should be able to run directly on
Cloudy.

In this particular case we are going to download the binaries from the git
repository with the following command line:

    wget https://github.com/mvdan/pastecat/releases/download/v0.3.0/pastecat_linux_386

Note that in this case we are downloading a specific version for a Linux with a 386
architecture. 

Note that having the service as a Debian package has many advantages:

* Updates are simple and need no extra work from Cloudy
* The package is compiled and built by Debian in a trusted way
* An init.d file is already provided
* Debian packages often contain small patches and fixes

## 2. Testing it out

Before adding Pastecat as a Cloudy service, we can configure and start it
ourselves directly (i.e. manually), to see if it works and how. This way we
can better understand what configuration options or command line parameters
are we will need to run it as a service, and also to manage it once it is
running.

## 3. Adding the controller

In `web/plug/controllers` we have one PHP file per service, which we call "the
controller". This is the code that will run when we enter the services page on
the Cloudy web interface.

### 3.1. Adding the index function

We also want our service to be integrated in the Cloudy web structure. To do this,
a few PHP scripts need to be created and added to our device. Altogether, and by
the time being, we'll need to create a total of 2 scripts: `pastecat.PHP` and
`pastecat.menu.PHP`. The first one is the controller itself, this is, the script
that renders the page and has all the information such as buttons or redirections.
The other one is what allows our service to show up in the upper menu bar of
Cloudy's web interface.

The menu code will look like this:


    <?PHP
    //peerstreamer.menu.PHP
    addMenu('Pastecat','pastecat','Clommunity');

By now, we'll use a very simple PHP script in the controller:

    <?PHP
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
in Debian. Since Pastecat isn't, we'll have to do it manually. This usually
involves a combination of `wget`, `mv` and `chmod`. It is generally a good
idea to keep the service's files under `/opt/SERVICENAME`.

In our particular case, the first thing we need to do is downloading the
binary from the release. In order to do this we will make use of the mentioned
`wget` command. Given a URL to a file, this command allows us to download this
file in our system, and this is what we will do in our system (as mentioned before):

    wget https://github.com/mvdan/pastecat/releases/download/v0.3.0/pastecat_linux_386

Once we have the binary, we just need to move it to a directory where executable
files use to be located. In our case, we will use the directory /opt/pastecat/.
To move these files through our system we will use the command `mv`. However,
first of all we need to create the directory where we will place our binary. To
do this we use the `mkdir` command as is shown below:

   mkdir -p /opt/pastecat/

Once we have our directory created, it is time to move the binary there:

    mv current_directory/pastecat_linux_386 /opt/pastecat/

where current\_directory is the directory where we previously downloaded the
binary. Since the binary name depends on the architecture, in order to simplfy
the controller's code, we will change its name to something more simple:

    mv /opt/pastecat/pastecat_linux_386 /opt/pastecat/pastecat

Now our binary is called `pastecat` insted of `pastecat_linux_386`

These steps are the minimum requiered to install a service which is not provided
in the Debian official repositories. However, to an end user, it would look like a
nightmare to run these commands in a console connected through ssh to its device,
so what we are going to do now, is create a bash script which will be called later
from the web interface by clicking a button.

This script is the first version of the pastecat controller. For the time being, We
will just include a function to install pastecat in a device. Later we will include
some other functions to add more facilities to our service.


    #!/bin/bash
    PCPATH="/opt/pastecat/"
    
    doInstall() {
        if isInstall
        then
            echo "Pastecat is already installed."
            return
        fi
    
        # Creating directory and switching
        mkdir -p $pcpath && cd $pcpath
    
        # Getting file
        wget https://github.com/mvdan/pastecat/releases/download/v0.3.0/pastecat_linux_386
    
        # Changing name so controller can invoke it generically
        mv pastecat_linux_386 pastecat
        chmod +x pastecat
        
        cd -
    }

    isInstalled() {
        [ -d $pcpath ] && return 0
        return 1
    }
    
    
    case $1 in
        "install")
            shift
            doInstall $@
            ;;
    esac

We can see how the lasts steps are done within the same function, allowing us to
install the software in the device.

### 3.3 Making the controller use Pastecat

## 3.3.1 Start the service

The next thing we want is our software to be used through the web interface. In 
order to do this, we will include a new option to the main page of pastecat, and
also integrate a new function to the controller script to manage the binary. We
will add the button like this:

    $page .= addButton(array('label'=>t('Create a Pastecat server'),'href'=>$staticFile.'/pastecat/publish'));

after the `Pastecat is installed` message. The next thing will be implementing the
function `publish` in the same PHP. This function is the responsible of calling the
appropiate function in the controller and to announce our server usign the avahi
technology. The difference with this function is that it requieres a form to
introduce data, so in the end we will have a total of 2 functions: a get and a post:

    function publish_get() {
        global $pcpath,$title;
        global $staticFile;
    
        $page = hlc(t($title));
        $page .= hlc(t('Publish a pastecat server'),2);
        $page .= par(t("Write the port to publish your Pastecat service"));
        $page .= createForm(array('class'=>'form-horizontal'));
        $page .= addInput('description',t('Describe this server'));
        $page .= addSubmit(array('label'=>t('Publish'),'class'=>'btn btn-primary'));
        $page .= addButton(array('label'=>t('Cancel'),'href'=>$staticFile.'/peerstreamer'));
    
        return(array('type' => 'render','page' => $page));
    }
    
    function publish_post() {
        $port = $_POST['port'];
        $description = $_POST['description'];
        $ip = "";
    
        $page = "<pre>";
        $page .= _pcsource($description);
        $page .= "</pre>";
    
        return(array('type' => 'render','page' => $page));
    }

As we can see, in the `post` function we are invoking another function. The reason
to do this is to write a more simple and modular code. In this function, we are 
finally calling the script:

    function _pcsource($port,$description) {
        global $pcpath,$pcprogram,$title,$pcutils,$avahi_type,$port;
    
        $page = "";
        $device = getCommunityDev()['output'][0];
        $ipserver = getCommunityIP()['output'][0];
    
        if ($description == "") $description = $type;
    
        $cmd = $pcutils." publish '$port' '$description';
        execute_program_detached($cmd);
    
        $page .= t($ipserver);
        $page .= par(t('Published this server.'));
    
        $page .= addButton(array('label'=>t('Back'),'href'=>$staticFile.'/pastecat'));
    
        return($page)
    }

The next thing to do will be create the function `publish` in the controller, so we will
add a new function to the basic controller we had back at section __3.2__. We will add
a new flag called publish, so the first executed part of the script will look like this:

    if [ $# -lt 1 ]
    then
        doHelp
    fi
    
    case $1 in
        "install")
            shift
            doInstall $@
            ;;
        "publish")
            shift
            doServer $@
            ;;
    esac

As we can see, when the script's first argument is `publish`, we shift the rest of arguments
and call the function `doServer`. In this function, we must start the service with the requiered
arguments, so the first thing we'll do is put the arguments into local variables. Once we do that
the common thing would be to launche the Pastecat server, but since it might be called with root
permissions (and this is bad) we must run it as a `nobody` user. The issue is that the `nobody`
user has merely no permissions... and pastecat need some permissions to create folders and text
files. In order to allow the `nobody` user to do that, first of all we will create a folder
and grant permissions to almost everyone to it. We will use `chmod` again. Now, the user can
create files and directories within this directory, so we can now run pastecat. Finally, we keep
the pid in a variable in case we want to use it in later updates:

    doServer() {
        # Turning machine into a server
    
        local port=${1:-""}
        local description=${2:-""}
        local ip=${3:-"0.0.0.0"}
    
        # Creating directory with nobody permissions
        mkdir -p "/var/local/pastecat"
        chmod 777 "/var/local/pastecat" && cd "/var/local/pastecat"
    
        # Running pastecat 
        cmd='su '$PCUSER' -c "{ '$PCPATH$PCPROG' -l :'$port' > '$LOGFILE' 2>&1 & }; echo \$!"'
        pidpc=$(eval $cmd)          # keeping PID for future needs...
    
        cd -
    
        # Using the PID, we could carry on process control so if the pastecat process die, we can also
        # stop the avahi process to avoid "false connections"
    
        return
    }

Note that we are using some global variables that were not defined before such as `PCUSER` and `LOGFILE`. By
default, we set these variables like this:

    PCPATH="/opt/pastecat/"
    PCPROG="pastecat"
    LOGFILE="/dev/null"
    PCUSER="nobody
    
## 3.3.2 Stop the service

Sometimes, we may also want to stop out service, so we will provide a button to do so.
The first thing would be creating the button, but if we think a little, we will figure
out that before doing this, we need a way to know if our service is running. In addition,
we also need a way to stop our service. Since we are running on Linux, we can easily
achieve that by using the `kill` command. The thing is that to use this command, we need
the Process ID (PID). Luckily for us, we already kept this number when we created the
pastecat server with `pidpc=$(eval $cmd)`.

Now that we have everything we need to kill our process, let's provide a way so the 
PHP can detect whether Pastecat is running or not. An easy and resulting way to do this
is storing some useful data in a file and delete this file when pastecat is stopped.
This way, we make sure that this file will only exists when Pastecat is running. This
file will be created from the controller adding the following lines right below the
sentence we mentioned in the previous paragraph:

    # Writting server info to file
    info="$pidpc http://$ip:$port"          # Separator is space character
    echo $info > $PCFILE
    
where `$PCFILE` is `/var/run/pc.info`. Note that the content of this file will be the PID
and the complete direction of our Pastecat server.

Now we have a way to know if our server is up or down, so we can add the "stop" button in
the web interfae. We will modify a little bit the PHP script that we had before, just by
addind anther advertisement indicating whether Pastecat is up or down, and 2 more buttons
if it is running. So, in our index function, within the condition that checks if Pastecat
is installed we will have the following code:

    $page .= "<div class='alert alert-success text-center'>".t("Pastecat is installed")."</div>\n";
    if ( isRunning() ) {
        $page .= "<div class='alert alert-success text-center'>".t("Pastecat is running")."</div>\n";
        $page .= addButton(array('label'=>t('Go to server'),'href'=>'http://'. getCommunityIP()['output'][0] .':'. $port));
        $page .= addButton(array('label'=>t('Stop server'),'href'=>$staticFile.'/pastecat/stop'));
    } else  {
        $page .= "<div class='alert alert-error text-center'>".t("Pastecat is not running")."</div>\n";
    }
    $page .= addButton(array('label'=>t('Create a Pastecat server'),'href'=>$staticFile.'/pastecat/publish'));

In this piece of code, we can appreciate 2 new features in our code. The first one is a
check function called `isRunning()`. This function looks very similar to the function we 
used to check if Pastecat is installed:

    function isRunning() {
        // Returns whether pastecat is running or not
        global $pcfile;
    
        return(file_exists($pcfile));   
    }

It is a simple as it seems, it just checks if the file we created when starting the server
still exists. The second thing we can notice in the new PHP code is the existance of a new
function called `stop`. This function will invoke another function in the controller which
will stop the pastecat:

    function stop() {
        // Stops Pastecat server
        global $pcpath,$pcprogram,$title,$pcutils,$avahi_type,$port;
    
        $page = "";
        $cmd = $pcutils." stop ";
        execute_program_detached($cmd);
    
        return(array('type'=>'redirect','url'=>$staticFile.'/pastecat'));
    }

In order to make the controller understand this order, we will modify the case and add the
new function. In the `case` statement, we will add the following under the `install` option:

    "stop")
        shift
        doStop $@
        ;;

This calls the function `doStop` within the controller. This function will look like this:

    doStop() {
        # Stopping pastecat server
        pcpid=$(cat $PCFILE | cut -d' ' -f1)
        kill $pcpid
    
        # Removing info file
        rm $PCFILE
    }

This function just gets the pastecat's PID from the file we created before, kills the process
and finally removes the file so the PHP scripts can know that pastecat is now down.

Now we can create a pastecat instance server and stop it. However, there is still something
missing: make the other users see our service. And this is why we are using avahi. 

## 4 Avahi service publishing

On of the best things in Cloudy is the facility of publishing our service as a
publication in the avahi network, allowing other users to know what we are
offering and joining our service. To do this, we first need to add a few lines
to the PHP controller, just after we've called the controller to start the 
pastecat instance. We will add the following lines:

    $description = str_replace(' ', '', $description);
    $temp = avahi_publish($avahi_type, $description, $port, "");
    $page .= ptxt($temp);

So in the end our function will look like this:

    function _pcsource($port,$description) {
        global $pcpath,$pcprogram,$title,$pcutils,$avahi_type;
    
        $page = "";
        $device = getCommunityDev()['output'][0];
        $ipserver = getCommunityIP()['output'][0];
    
        if ($description == "") $description = $type;
    
        $cmd = $pcutils." publish '$port' '$description'";
        execute_program_detached($cmd);
    
        $page .= t($ipserver);
        $page .= par(t('Published this server.'));
        $description = str_replace(' ', '', $description);
        $temp = avahi_publish($avahi_type, $description, $port, "");
        $page .= ptxt($temp);
    
        $page .= addButton(array('label'=>t('Back'),'href'=>$staticFile.'/pastecat'));
    
        return($page)
    }
    
With this simple step, we announced our service in the avahi network. However
the work does not end here, there is still one more thing to do: create a button
and program it so when clicked, it directly goes to our pastecat server.

To do this there is a folder called `avahi` within the `plug` directory. The
scripts that define the function carried on when the button is clicked are
defined in different files within this directory, therefor we will create a
new file called `pastecat.avahi.PHP` which will contain this:

    <?PHP
    // plug/avahi/pastecat.avahi.PHP
    
    addAvahi('pastecat','fpcserver');
    
    function fpcserver($dates){
        global $staticFile;
    
        return ("<a class='btn' href='http://" .$dates['ip'] .":". $dates['port']."'>Go to server</a>  ");
    }

This will create a button besides the avahi announcement line that will point to
our server.

Now that we have our service announced, we want it to dissappear when we stop the pastecat
service. This last step is very simple yet important. It consist of a few lines in the PHP
function called stop. Until now, this function just called the controller and stopped the 
pastecat, but now it will also stop the avahi publication and show a flash comment so the
user knwo it worked:

    $temp = avahi_unpublish($avahi_type, $port);
    $flash = ptxt($temp);
    setFlash($flash);

These lines will be added just after the `execute_program_detached($cmd)` sentence in the
stop function.

