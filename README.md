davekok/controller
================================================================================

A framework for your controller. The days that we could have a one framework for everything are basically over. Most projects specialized frameworks for the view and for the model. However, for controllers no specialized frameworks have yet emerged. In this work a specialized framework for the controller is investigated. What it would look like. What could and should it do?

THIS IS A PROOF OF CONCEPT, NOT PRODUCTION READY.

Purpose
--------------------------------------------------------------------------------

The purpose of this controller is to replace the webserver as the central hub in a network architecture. Allowing workers to communicate via the hub with each other. At minimal HTTP will be supported, so HTTP requests can be accepted and routed to a worker. Workers will also be able to communicate with each other and use websockets to do so, as HTTP is to limited for this. In browser service workers could potentially also connect. But others protocols like SMTP could be added on.

Being a framework it has to be extensible. Allowing you to add your own stuff. Things like authentication, authorization, validating input, composing the output from multiple workers to the view, to name some common stuff. That is more suited to be in a dedicated controller than scrammed into a view or model.

Views are expected to be based on webcomponents and consume a view-model in which the vocabulary is in the terms of the UI. Model is well what ever it has to be.

Architecture style
--------------------------------------------------------------------------------

The architecture style that is followed is the good old unix style. Which perhaps could be named a worker oriented architecture (WOA) as apposed to the service oriented architecture (SOA). In SOA all services are also stream servers. Adding the complexity of having to solve handling multiple streams in each service. Often require multi-threaded or asynchronous programming with fibers/co-routines. Non of this is easy even if abstracted by a framework. Having to deal with this only in the controller, simplifies things on the whole. A part from this the are other controller things that could be off loaded. Now often requiring to be implemented everywhere. Like input validation. Data synchronization.

By having the workers simply be stream or TCP clients it is also easier to run them anywhere. In the cloud, on premise, on your mobile device. Basically extending the cloud to outside the datacentre. SOA makes it difficult to be flexible.

Off course the controller will still be a stream or TCP server and handles multiple streams. But at least in this way it can be contained to only the controller framework. Unburdening view and model frameworks. Also meaning the view and model developers don't have to deal with this and debug it. If you have ever experienced debugging application logic while also dealing with asynchronous logic, you'll properly wish for this sooner then later. Forcing messages over a single serial stream to workers handling application logic will make it much easier to monitor and debug them and lessen the risk of race conditions. Dealing with application logic is already tough enough.

For this to work properly the vocabulary used in the requests/messages/events should be UI focused on output and data entry focused on input and not in the vocabulary of the domain of the workers. The controller should facilitate composition of the view if multiple workers output to the same view. Links can be used by workers to reference each other.

Examples of this architecture are:
- Bash with it terminal client as the view, bash as the controller and any programs running under bash as workers. Vocabulary is centered around characters.
- X11, with the display server as the view, the desktop manager as the controller and any program as a worker. Vocabulary is centered around drawing primitives and pixel buffers.
- Old school PHP, with the webbrowser as the view, Apache as the controller and PHP running in CGI mode as a worker. Vocabulary is HTML/CSS. One could say Apache is one of the first specialized controller frameworks. Only problem is that it is written in C and not easy to adopt.

In this controller vocabulary shall be centered around webcomponents. Webcomponents implement HTML/CSS. HTML/CSS implement drawing primitives.

### Advantages

- Workers connect as stream or TCP client to the controller. Thus then can run anywhere. In the cloud, on promise or mobile (IoT devices). No need to open up firewalls either. The controller does not need to know where they are.
- They are easy to write. They just require an infinite loop reading from the main stream. Doing a read will block the program until a new message/request/event arrives.
- Using the replication feature of Kubernutes for instance, will get you multiple workers. No need to write watch dogs or manager programs. The controller can easily detect a worker offline if a stream is disconnected.
- They can easily be deployed in a container but also without one.
- Only the controller needs to handle multiple streams and not everything.

### Comparison

|                   | WOA (except controller) | SOA                         |
|-------------------|-------------------------|-----------------------------|
| IO                | Synchronous             | Asynchronous                |
| Streams           | Single                  | Multiple                    |
| Threads/Fibers    | No                      | Yes                         |
| Output vocabulary | UI focused              | Domain focused              |
| Input vocabulary  | Data entry focused      | Invocation/command focused  |
| Standalone        | No requires controller  | Yes, controller is embedded |
| Cinefin tag       | Obvious                 | Complicated                 | 

Operations
--------------------------------------------------------------------------------

| Command         | Description                                        |
|-----------------|----------------------------------------------------|
| ./ops build dev | to build a dev container                           |
| ./ops build TAG | to build a release container with TAG              | 
| ./ops update    | to update package dependencies (through composer)  |
| ./ops require   | to require a package (through composer)            |
| ./ops push TAG  | to push container to container registry            |
| ./ops check     | to do a syntax check                               |
| ./ops test      | to run the tests                                   |
| ./ops run dev   | to run dev container                               |
| ./ops run TAG   | to run release container with TAG                  |
| ./ops run       | to run latest release container                    |

### Run preview

Terminal one:

    podman run -it --rm -p 8080:8080 ghcr.io/davekok/controller:latest

Terminal two:

    wget localhost:8080

### Directory structure

The expected directory structure:

- __$SRCDIR__ your source dir
- __$SRCDIR/$GITACCOUNT__ the account name of the fork (davekok if unforked)
- __$SRCDIR/$GITACCOUNT/controller__ checkout of this repo
- __$SRCDIR/$GITACCOUNT/http__ checkout of http repo
- __$SRCDIR/$GITACCOUNT/lalr1__ checkout of lalr1 repo
- __$SRCDIR/$GITACCOUNT/stream__ checkout of stream repo
- __$SRCDIR/$GITACCOUNT/log__ checkout of log repo
