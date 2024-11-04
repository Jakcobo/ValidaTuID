FROM node
EXPOSE 3002
RUN apt-get update -y && apt-get install -y telnet
RUN apt-get update -y && apt-get install -y iputils-ping
WORKDIR /microUsuarios
COPY src /microUsuarios/src
COPY package-lock.json /microUsuarios/package-lock.json
COPY package.json /microUsuarios/package.json
RUN npm install
CMD node src/index.js