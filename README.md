# Example
![](pwpay.gif)

# Local Environment
> Using Docker for our local environment

## Requirements

1. Having [Docker installed](https://www.docker.com/products/docker-desktop) (you will need to create a Hub account)
2. Having [Git installed](https://git-scm.com/downloads)

## Installation

1. Clone this repository into your projects folder using the `git clone` command

## Instructions

1. After cloning the project, open your terminal and access the root folder using the `cd /path/to/the/folder` command.
2. To start the local environment, execute the command `make run` in your terminal. For Windows users, execute the command `docker-compose -d up` instead.
3. Create "uploads" folder inside the "public" folder

**Note:** The first time you run this command it will take some time because it will download all the required images from the Hub.

At this point, if you execute the command `docker ps` you should see a total of 4 containers running:

```
pw_local_env-nginx
pw_local_env-admin
pw_local_env-php
pw_local_env-db
```

The application should be running in the 8030 port of your local machine but, before trying it, lets add one entry to your **hosts** file.

For OSX users, this file should located at `/etc/hosts`. For Windows users, you can check [this guide](https://www.howtogeek.com/howto/27350/beginner-geek-how-to-edit-your-hosts-file/).

Edit the file and add the following entry:

```
127.0.0.1 pw.pay
```
Then, go to the project folder and run `composer update`.

At this point, you should be able to access to the application by visiting the following address in your browser [http://pw.pay:8030/](http://pw.pay:8030/).


### Database

To access database we are going to use Adminer, found at [http://localhost:8080/](Database). To login the credentials are user: root password: admin BBD: test.

After logging in, we need to create the table User, to create it, you have to go to the left part of the screen, SQL order button and execute the SQL script named: "bbdd.sql"


## Start and stop server

1. To start the local environment, execute the command `make run` in your terminal. For Windows users, execute the command `docker-compose -d up` instead.
2. To stop the project, execute the command `make stop` in your terminal. For Windows users, execute the command `docker-compose -d down` instead.


## In case there is any problem, try:

1. docker-compose down
2. docker-compose build app
3. docker-compose up -d
