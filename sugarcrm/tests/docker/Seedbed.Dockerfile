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

ENV GEOMETRY 1920x1080x24
ENV DISPLAY :0
ENV DBUS_SESSION_BUS_ADDRESS /dev/null

# Prevents timezone message in Sugar instance (it was covering page elements)
ENV TZ America/Los_Angeles

# Install Graphical Support and Browsers
ENV FIREFOX_VERSION 53.0
COPY scripts/install-web-browsers.sh /opt/bin/install-web-browsers.sh
RUN chmod +x /opt/bin/install-web-browsers.sh && \
    /opt/bin/install-web-browsers.sh

# Add multimedia repository, required to install ffmpeg
RUN \
    echo "deb http://www.deb-multimedia.org jessie main non-free" >> /etc/apt/sources.list && \
    echo "deb-src http://www.deb-multimedia.org jessie main non-free" >> /etc/apt/sources.list && \
    apt-get update && \
    apt-get install -y --force-yes deb-multimedia-keyring && \
    apt-get update

# Install Seedbed-specific external dependencies
RUN apt-get install -y --force-yes --no-install-recommends \
    graphicsmagick ffmpeg

# Install Java 8, required for Selenium
RUN \
    echo "deb http://ppa.launchpad.net/webupd8team/java/ubuntu trusty main" > /etc/apt/sources.list.d/java-8-debian.list && \
    echo "deb-src http://ppa.launchpad.net/webupd8team/java/ubuntu trusty main" >> /etc/apt/sources.list.d/java-8-debian.list && \
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys EEA14886 && \
    apt-get update && \
    echo oracle-java8-installer shared/accepted-oracle-license-v1-1 select true | /usr/bin/debconf-set-selections && \
    apt-get install -y --no-install-recommends oracle-java8-installer

# Cleanup
RUN apt-get autoremove -y && apt-get clean && \
    rm -Rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ADD scripts/Seedbed.Entrypoint.sh /Seedbed.Entrypoint.sh

# Default command to run when container starts:
ENTRYPOINT ["/Seedbed.Entrypoint.sh"]
