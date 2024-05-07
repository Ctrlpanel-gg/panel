## 🐳 Standalone Docker

The CtrlPanel standalone Docker enables users to run CtrlPanel easily with just a few clicks.

To run CtrlPanel standalone Docker, you need to have Docker installed on your machine. Some server operating systems like Unraid, TrueNAS, etc.. already have Docker installed, making it even easier to run CtrlPanel.

If you're using a different operating system, you can follow the official Docker installation guide [here](https://docs.docker.com/get-docker/).

Once you have Docker installed, you can run CtrlPanel standalone Docker by executing the following command:

```bash
docker run ...
```

This command will pull the latest CtrlPanel Docker image from Docker Hub and run it.

The control panel will be available at http://localhost/install and will be a completely fresh installation.

Note that while the container contains the full CtrlPanel installation, you will still need to perform the basic setup. You can find instructions for this [here](https://ctrlpanel.gg/docs/Installation/getting-started#basic-setup).

## 🏗️ Advanced Docker

If you are migrating, or want to create your own Docker image, you can follow the instructions [here]().
