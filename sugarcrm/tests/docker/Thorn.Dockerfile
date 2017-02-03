FROM registry.sugarcrm.net/engineering/node:latest
MAINTAINER Engineering Automation "engineering-automation@sugarcrm.com"

ADD Thorn.Entrypoint.sh /Thorn.Entrypoint.sh

# Default command to run when container starts:
ENTRYPOINT ["/Thorn.Entrypoint.sh"]
