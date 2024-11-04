FROM node
EXPOSE 3003
RUN apt-get update -y && apt-get install -y telnet
RUN apt-get update -y && apt-get install -y iputils-ping
WORKDIR /microPeticiones
COPY src /microPeticiones/src
COPY package-lock.json /microPeticiones/package-lock.json
COPY package.json /microPeticiones/package.json
RUN npm install
CMD node src/index.js