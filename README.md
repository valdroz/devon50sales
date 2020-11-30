# BSA Troop Devon 50 Sales

## Prepare your local play ground

1. If not done already, install "Docker Desktop" on your local computer. Follow instructions at https://www.docker.com/get-started

2. Build Docker image next for OC SandBox, like so:

```bash
make build
```

3. Setup OC SandBox:

```bash
# Prepare directory for OS SandBox Files
mkdir www
# Start Docker container
make run
```

.. once in docker terminal run installation script as:

```bash
# Needed only once
root@{dockerhost}:/ sh oc-install.sh
```

.. next open http://localhost/index.php in your browser and complete setup flow.

.. once completed run following command on Docker host to ensure proper ownership:

```bash
root@{dockerhost}:/ chown -R www-data:www-data /var/www
```


4. Your OC SandBox environment is ready.
