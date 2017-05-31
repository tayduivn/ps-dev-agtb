# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

FROM registry.sugarcrm.net/engineering/node:latest
MAINTAINER Engineering Automation "engineering-automation@sugarcrm.com"

#========================
# Environment Variables for Configuration
#========================
USER root
ENV GEOMETRY 1920x1080x24
ENV DISPLAY :0
ENV DBUS_SESSION_BUS_ADDRESS /dev/null
ARG DEBIAN_FRONTEND=noninteractive

#========================
# Install Graphical Support and Browsers
#========================
ENV FIREFOX_VERSION 51.0
COPY scripts/install-web-browsers.sh /opt/bin/install-web-browsers.sh
RUN chmod +x /opt/bin/install-web-browsers.sh && \
    /opt/bin/install-web-browsers.sh

#========================
# Clean up Apt
#========================
RUN \
  apt-get clean && \
  rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /usr/local/

#========================
# Expose Port 5900
#========================
EXPOSE 5900

#========================
# Add Karma Cleanup Script
#========================
COPY scripts/Karma.Cleanup.sh /opt/bin/cleanup.sh
RUN chmod +x /opt/bin/cleanup.sh
 
#========================
# Setup Entry Point
#========================
COPY scripts/Karma.Entrypoint.sh /opt/bin/entry_point.sh
RUN chmod +x /opt/bin/entry_point.sh
ENTRYPOINT ["/opt/bin/entry_point.sh"]
