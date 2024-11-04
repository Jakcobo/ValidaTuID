FROM haproxy:2.3
RUN apt-get update -y && apt-get install -y telnet
RUN apt-get update -y && apt-get install -y iputils-ping
RUN mkdir -p /run/haproxy/
COPY haproxy.cfg /usr/local/etc/haproxy/haproxy.cfg
