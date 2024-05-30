# 🐳 Standalone Docker

The CtrlPanel standalone Docker enables users to run CtrlPanel easily with just a few clicks.

To run CtrlPanel standalone Docker, you need to have Docker installed on your machine. Some server operating systems like Unraid, TrueNAS, etc.. already have Docker installed, making it even easier to run CtrlPanel.
If you're using a different operating system, you can follow the official Docker installation guide [here](https://docs.docker.com/get-docker/).

Once you have Docker installed, you can run CtrlPanel standalone Docker by executing the following command:

Recommended way via Docker Compose:

Get the Compose file [here](https://github.com/Ctrlpanel-gg/panel/blob/docker-github-workflow/docker/standalone/compose.yaml).
This also includes all necessaries like a Database, Redis and optionally phpmyadmin to manage the Database.

Running as commandline command:

```bash
docker run -p 80:80 -p 443:443 -v /path/to/website_files:/var/www/html -v /path/to/nginx_config:/etc/nginx/conf.d/ ghcr.io/ctrlpanel-gg/panel:latest
```

When installing you need to update the `.env` file. Change those two variables to: `MEMCACHED_HOST=redis` and `REDIS_HOST=redis`, to use the Redis server which comes with the docker compose installation.

This command will run the latest CtrlPanel Docker image from Docker Hub and run it.

The control panel will be available at http://localhost/install and will be a completely fresh installation.

Note that while the container contains the full CtrlPanel installation, you will still need to perform the basic setup. You can find instructions for this [here](https://ctrlpanel.gg/docs/Installation/getting-started#basic-setup).

## 🏗️ Migrating from a previous bare metal installation

If you are migrating, from a previous bare metal installation, you can follow the instructions [here]() (Soon on documentation).

## 🧰 Creating your own Docker image

If you want to create your own Docker image, you can follow the instructions [here]() (Soon on documentation).
