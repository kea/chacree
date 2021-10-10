# Chacree

Chat server (php+swoole) and client (vuejs).
Provide basic registration and authentication via API and chat via websocket.

## Setup

You need docker and docker-compose

```
$ make setup
```

The script create ssl certs for http/websocket server, build the container, run `composer install` and restart the server.

## Start chatting

Open https://localhost:9501, confirm the security of self-signed certificate, and the login screen should appear.
Click on "Signup now" enter username and password (no email and non confirmation needed), and then proceed with the login.
