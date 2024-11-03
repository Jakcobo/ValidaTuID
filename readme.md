# Sistema Valida tu ID

## Descripción del Sistema

El sistema sirve para asd askdjasl djkalsdjlaskdlasd

##### Roles de los Usuarios
Campo | Descripción
------------ | ------------
Administrador | ????
Validador | ???????
Cliente |  ????

## Arquitectura

### Capas del sistema
En un diagrama de capas, nuestro sistema se ve de la siguiente forma.

![Alt text](/Doc/DiagramaCapas.png "Diagrama de Capas")

### Diagrama de Despliegue

![Alt text](/Doc/DiagramaDespliegue.png "Diagrama de Despliegue")

#### Lista de Servicios Desplegados

Servicio | Url Directa | Balanceador | Url Balanceador |  Descripción
------------ | ------------ | ------------- | ------------- | -------------
Portal | http://192.168.100.2:3000 | Balanceadorw | http://192.168.100.2:5080/ | Portal de Valida tu ID.
microclientes | http://192.168.100.2:3001 | Balanceadors1 | http://192.168.100.2:5081/ | Servicio Microclientes
micropeticiones | http://192.168.100.2:3003 | Balanceadors1 | http://192.168.100.2:5083/ | Servicio Micropeticiones
microusuarios | http://192.168.100.2:3002 | Balanceadors2 | http://192.168.100.2:5082/ | Servicio Microusuarios
Peticionesdb | N/A | N/A | N/A | Base de datos

Este sistema esta desplegado utilizando dos servidores Ubuntu. 

Servidor | Memoria | CPU | IP
------------ | ------------ | ------------- | -------------
servidorUbuntu | 2048 | 2 | 192.168.100.2
clienteUbuntu | 1024 | 1 | 192.168.100.3

Los dos servidores conforman un cluster de Docker. 

### Comunicacion entre Servicios
A continuación se gráfica la comunicación entre los servicios configurados en el docker compose.

![Alt text](/Doc/DiagramaComunicacion.png "Diagrama de Comunicación")

El gráfico muestra que cada servicio es accedido mediante un balanceador de carga. 

### Diagrama Entidad Relación
A continuación se muestra las tablas que componen el sistema.

![Alt text](/Doc/DiagramaBaseDatos.png "Diagrama de Base de Datos")

##### Configuración base de datos

Base de Datos | Puerto del Contenedor | Puerto Host
------------ | ------------ | ------------
Peticionesdb | 3306 | 32000

##### Tabla Usuarios
Campo | Descripción
------------ | ------------
usuario | Nick Name o nombre del usuario
nombre | Nombre completo de a quien pertenece el usuario
rol | Rol que tiene este usuario en el sistema. Puede ser un Administrador o validador
password | Contraseña del usuario

##### Tabla Clientes
Campo | Descripción
------------ | ------------
cc | 
password | 

##### Tabla Peticiones
Campo | Descripción
------------ | ------------
id | ????
cccliente | ????
ccarchivo | ????
tiposervicio | ????
bancocliente | ????
fechasolicitud | ????
horasolicitud | ????
usuariovalidador | ????
nombrevalidador | ????
estado | ????
fechacreacion | ????
horacreacion | ????
fecharevision | ????
horarevision | ????

### Servicio de carga de información

El servicio insertadditionaldata, se utiliza para cargar la información inicial que se encuentra en el archivo .cvs. Se extrae los clientes y los usuarios y los crea en la base de datos de Peticiones en MySQL mediante codigo Python.

> *Una vez ejecutado este servicio, se detendrá el contenedor.*

## Instalación del Sistema

### Creación de las Maquinas Virtuales

Las maquinas virtuales se crearon utilizando el vagrant. A continuación se comparte la estructura del archivo Vagrant

```vagrant
# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
 if Vagrant.has_plugin? "vagrant-vbguest"
   config.vbguest.no_install = true
   config.vbguest.auto_update = false
   config.vbguest.no_remote = true
 end

 config.vm.define :servidorUbuntu do |servidorUbuntu|
   servidorUbuntu.vm.box = "bento/ubuntu-22.04"
   servidorUbuntu.vm.network :private_network, ip: "192.168.100.2"   
   servidorUbuntu.vm.hostname = "servidorUbuntu"
   servidorUbuntu.vm.box_download_insecure=true
   servidorUbuntu.vm.provider "virtualbox" do |vb|
      vb.memory = "2048"
      vb.cpus = 2
   end
 end
 
 config.vm.define :clienteUbuntu do |clienteUbuntu|
   clienteUbuntu.vm.box = "bento/ubuntu-22.04"
   clienteUbuntu.vm.network :private_network, ip: "192.168.100.3"   
   clienteUbuntu.vm.hostname = "clienteUbuntu"
   clienteUbuntu.vm.box_download_insecure=true
   clienteUbuntu.vm.provider "virtualbox" do |vb|
      vb.memory = "1024"
      vb.cpus = 1
   end
 end
end
```

### Instalación de Docker

Instale Docker tanto en el servidorUbuntu como en el clienteUbuntu

### Descargar el repositorio y construya las imágenes Docker

Ingrese al servidorUbuntu y descargue el repositorio git

```sh
git clone https://github.com/jacoboDM/ValidaTuID

# Ingrese a la carpeta
cd ValidaTuID

# Construya las imágenes Docker
docker compose build

# Verifique que las imágenes fueron construidas. Recuerde que inician por proyectofinal-
docker images -a
```

### Cofiguración de roles de despliegue

El archivo de Docker compose tiene la configuración para desplegar los contenedores en determinados roles.

Capa | Servicio | Role | Maquina
------------ | ------------ | ------------- | -------------
Presentación | Portal | serverportal | clienteUbuntu
Servicios | micropeticiones | serverservices | servidorUbuntu
Servicios | microclientes | serverservices | servidorUbuntu
Servicios | microusuarios | serverservices | servidorUbuntu
Base de Datos | Peticionesdb | serverstorage | servidorUbuntu
Balanceador | Balanceadorw | loadbalancer | servidorUbuntu
Balanceador | Balanceadors1 | loadbalancer | servidorUbuntu
Balanceador | Balanceadors2 | loadbalancer | servidorUbuntu

Para la configuración anterior, ejecute los siguientes comandos.

Ejecute los siguientes comandos en ?????????
```sh
docker node update --label-add role=loadbalancer servidorUbuntu
docker node update --label-add role=serverportal clienteUbuntu
docker node update --label-add role=serverservices servidorUbuntu
docker node update --label-add role=serverstorage servidorUbuntu
```

### Cree el cluster con Docker Swarn

> *Asegurar tener configurado el cluster antes de ejecutar los siguientes comandos.*

Todas las imagenes y sus contenedores inician por: "proyectofinal-"

Ejecute los siguientes comandos en el servidorUbuntu

```sh
# Desplegar el Docker Componse en el Swarm
docker stack deploy -c docker-compose.yml proyectofinal

# Consultar los stack creado
docker stack ls 

# Consultar los servicios en los stacks
docker service ls 

# Consultar los contenedores en el servicio especificado
docker service ps proyectofinal_microusuarios
```

Consulte el id del contenedor de la base de datos ingrese a ella y realice una consulta;

```sh
# Consultar el id del servicio debe iniciar por "proyectofinal-peticionesdb"
docker ps

# Ingrese al contenedor
docker exec -it 168d22990ac1 /bin/bash

# Ingrese a la base de datos y realice una consulta para verificar que se insertaron los registros

mysql -u root -p
use peticiones;
select * from usuarios;
select * from clientes;

```

### Pruebas de desempeño

El archivo de Jmeter **Pruebas de Carga.jmx** tiene un ejemplo configurado con la consulta de todos los usuarios de la base de datos. Dependiendo de las capacidades de su maquina, escale el servicio para lograr que este responda correctamente.

Ejecute los siguientes comandos en el servidorUbuntu

```sh
# Escale un servicio
docker service scale proyectofinal_microusuarios=2 

# Eliminar el stack
docker stack rm proyectofinal 
```

### Estadísticas de los balanceadores
Para ver las estadisticas de las peticiones que ingresan mediante los balanceadores puede utilizar las siguientes URLs.

Servicio | Url |  Descripción
------------ | ------------ | -------------
Balanceadorw | http://192.168.100.2:5080/haproxy?stats | Estadisticas del Balanceador del Portal
Balanceadors1 | http://192.168.100.2:5081/haproxy?stats | Estadísticas del Balanceador de los servicios de peticiones y clientes
Balanceadors2 | http://192.168.100.2:5082/haproxy?stats | Estadísticas del Balanceador del servicio de usuarios