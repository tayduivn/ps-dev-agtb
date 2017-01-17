# Building Mango Docker Images

These instructions assume you are in the sugarcrm/tests/docker directory.

## Engineering Node Image

This image installs yarn, gulp, and a few other utilities used in CI. Our *latest* tag should always point to the latest *LTS release* of the [official node docker image](https://hub.docker.com/_/node/).
```
docker build -f Node.Dockerfile -t registry.sugarcrm.net/engineering/node:latest .
```

## Engineering Node Selenium Image

This image installs everything required to run Selenium on top of our Node image.
```
docker build -f Node.Selenium.Dockerfile -t registry.sugarcrm.net/engineering/node-selenium:latest .
```

## Seedbed Image

This image installs everything required for Seedbed to run on top of our Node Selenium image.
```
docker build -f Seedbed.Dockerfile -t registry.sugarcrm.net/seedbed:latest .
```

# Pushing Images

Just push them to our internal repository:
```
docker push registry.sugarcrm.net/image_name:tag
```
