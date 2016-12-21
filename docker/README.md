# Sonerezh with Docker

#1. Containers configuration 

**Do NOT remove vhost.conf and Dockerfile**

## With docker-compose (recommended)
Simply change the different variables in `docker-compose.yml`.
Like `MYSQL_DATABASE`, `MYSQL_USER` and `MYSQL_PASSWORD`

*Keep vars `MYSQL_DATABASE`, `MYSQL_USER` and `MYSQL_PASSWORD` in mind, you'll need them later*

Then do `dokcer-compose up`.

## Without docker-compose

 - Create a MySQL container with this :
`docker run -d --name mysqldb -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=sonerezh -e MYSQL_USER=sonerezh -e MYSQL_PASSWORD=password mysql`
For safety reasons change `password` by a better password. *Keep vars MYSQL_DATABASE, MYSQL_USER and MYSQL_PASSWORD in mind, you'll need them later*


- Build the Sonerezh container with this :
`docker build -t mysonerezhcontainer .`

- Start the Sonerezh container :
`docker run -d -p 8000:80 -v /Users/armand/SonerezhDocker/data:/data --link mysqldb:mysql mysonerezhcontainer`

**Make sure that the MySQL container name match with the `--link` when you start the Sonerezh container**

**You should change `docker-compose.yml` permissions to something like 600 to limit the access** 

Then access yourdockerhost:8000 

#2. Sonerezh configuration

## Database configuration
In Host put `mysql` if in `--link mysqldb:mysql` you keep the link `mysqldb` to `mysql` 
In Database put the var `MYSQL_DATABASE`, in login : `MYSQL_USER` and password : `MYSQL_PASSWORD`.

## Information needed:
In Music folder put `/data` note the `/`