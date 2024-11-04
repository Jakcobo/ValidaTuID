FROM node
EXPOSE 3001
RUN apt-get update -y && apt-get install -y telnet
RUN apt-get update -y && apt-get install -y iputils-ping
WORKDIR /microCliente
COPY src /microCliente/src
COPY package-lock.json /microCliente/package-lock.json
COPY package.json /microCliente/package.json
RUN npm install
CMD node src/index.js