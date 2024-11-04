# Sistema *Valida tu ID*

Comprobar en Ejecucion/docker-compose.yml el links ya que al ejecutar el swarm arroja un mensaje que dice que no se soporta, creo que deben cambarlo a depends_on

## Descripción del Sistema

El sistema sirve para asd askdjasl djkalsdjlaskdlasd

##### Roles del sistema
Rol | Descripción
------------ | ------------
Administrador | Rol de usurario quien puede crear usuarios y visualizar todas las peticiones
Validador | Rol de usuarios con la opcion de realizar el cambio de las solicitudes de pendiente a Aprobado o Rechazado
Cliente |  Solicitante del servicio

##### Tipos de Solicitud 

- CDT 
- Cuenta Ahorros 
- Credito
 

##### Estados de la Solicitud
Estado | Descripción
------------ | ------------
Aprobado | Una vez el validador determina que el proceso cumple con los lineamientos para ser aprobado
Rechazado | El proceso no cumple con lo requerimientos
Pendiente |  Estado predeterminado al crear una solicitud


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
cc | numero de documento 
password | Clave de acceso al portal

##### Tabla Peticiones
Campo | Descripción
------------ | ------------
id | Id del registro de la petición
cccliente | identificacion del cliente que registró la petición
ccarchivo | ruta del archivo cargado para validar.
tiposervicio | Tipo de Servicio para que cual se realiza la petición
bancocliente | Banco para el que va la solicitud
fechasolicitud | Fecha del registro de la solicitud
horasolicitud | hora en la que se registro la solicitud
usuariovalidador | Usuario que valido la solicitud
nombrevalidador | Nombre del usuario que valido la solicitud
estado | Estado de la solicitud
fechacreacion | Fecha en que se creo la peticion
horacreacion | Hora en que se creo la peticion
fecharevision | Fecha en que el validador reviso la peticion
horarevision | Hora en que el validador reviso la peticion

### Servicio de carga de información

El servicio **insertadditionaldata**, se utiliza para cargar la información inicial que se encuentra en el archivo .cvs. Se extrae los clientes y los usuarios y los crea en la base de datos de Peticiones en MySQL mediante codigo Python.

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
      vb.memory = "2048"
      vb.cpus = 2
   end
 end
end
```

### Instalación de Docker

Instale Docker tanto en el servidorUbuntu como en el clienteUbuntu

### Descargar el repositorio y construya las imágenes Docker

Ingrese al servidorUbuntu y descargue el repositorio git

```sh
# Descargue el repositorio de git
git clone https://github.com/jacoboDM/ValidaTuID

# Ingrese a la carpeta
cd ValidaTuID/CreacionImagenes

# Construya las imágenes Docker
docker compose build

# Verifique que las imágenes fueron construidas. Recuerde que inician por "proyectofinal-"
docker images -a
```

### Publique las imagenes en docker hub

> *Este ejemplo asume que el usuario en Docker hub es **Usuario1**.*

```sh
# Autentiquese en Docker hub
sudo docker login -u Usuario1

# Suba las imagenes a Docker hug
docker tag proyectofinal-balanceadorw Usuario1/proyectofinal-balanceadorw
sudo docker push Usuario1/proyectofinal-balanceadorw

docker tag proyectofinal-portal Usuario1/proyectofinal-portal
sudo docker push Usuario1/proyectofinal-portal

docker tag proyectofinal-balanceadors1 Usuario1/proyectofinal-balanceadors1
sudo docker push Usuario1/proyectofinal-balanceadors1

docker tag proyectofinal-insertadditionaldata Usuario1/proyectofinal-insertadditionaldata
sudo docker push Usuario1/proyectofinal-insertadditionaldata

docker tag proyectofinal-micropeticiones Usuario1/proyectofinal-micropeticiones
sudo docker push Usuario1/proyectofinal-micropeticiones

docker tag proyectofinal-balanceadors2 Usuario1/proyectofinal-balanceadors2
sudo docker push Usuario1/proyectofinal-balanceadors2

docker tag proyectofinal-microclientes Usuario1/proyectofinal-microclientes
sudo docker push Usuario1/proyectofinal-microclientes

docker tag proyectofinal-microusuarios Usuario1/proyectofinal-microusuarios
sudo docker push Usuario1/proyectofinal-microusuarios

docker tag proyectofinal-peticionesdb Usuario1/proyectofinal-peticionesdb
sudo docker push Usuario1/proyectofinal-peticionesdb
```

### Edite el archivo Ejecucion/docker-compose.yml

Edite este archivo para cambiar el usuario. Abra el archivo y cambie la ruta de cada una de las imagenes para direccionarla a las imagenes del repositorio en docker hub

```sh
# Devuelvase un nivel en el directorio
cd ..

# Ingrese a la carpeta de ejecución
cd Ejecucion
vim docker-compose.yml
```

### Configuración de roles de despliegue

El archivo de Docker compose tiene la configuración para desplegar los contenedores en determinados roles.

Capa | Servicio | Role | Maquina
------------ | ------------ | ------------- | -------------
Presentación | Portal | cliente | clienteUbuntu
Servicios | micropeticiones | servidor | servidorUbuntu
Servicios | microclientes | servidor | servidorUbuntu
Servicios | microusuarios | servidor | servidorUbuntu
Base de Datos | Peticionesdb | servidor | servidorUbuntu
Balanceador | Balanceadorw | cliente | clienteUbuntu
Balanceador | Balanceadors1 | cliente | clienteUbuntu
Balanceador | Balanceadors2 | cliente | clienteUbuntu

Para la configuración anterior, ejecute los siguientes comandos.

Ejecute los siguientes comandos en el cluster Manager
```sh
docker node update --label-add role=servidor servidorUbuntu
docker node update --label-add role=cliente clienteUbuntu
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

# Monitoree que todos los servicios suban
docker service ls 

# Consultar los contenedores en el servicio especificado
docker service ps proyectofinal_microusuarios
docker service ps proyectofinal_portal
```

Consulte el id del contenedor de la base de datos ingrese a ella y realice una consulta;

```sh
# Consultar el id del servicio debe iniciar por "proyectofinal-peticionesdb"
docker ps

# Ingrese al contenedor
docker exec -it 168d22990ac1 /bin/bash

# Ingrese a la base de datos y realice una consulta para verificar que se insertaron los registros

# Ingrese a mysql con el pwd root
mysql -u root -p
use peticiones;
select * from usuarios;
select * from clientes;
```

Compruebe en que maquina se desplegaron los servicios

```sh
# Para el Portal
docker service ps proyectofinal_portal

# Para el servicio de usuarios
docker service ps proyectofinal_microusuarios

```

### Pruebas de desempeño

El archivo de Jmeter **Pruebas de Carga.jmx** tiene un ejemplo configurado con la consulta de todos los usuarios de la base de datos. Dependiendo de las capacidades de su maquina, escale el servicio para lograr que este responda correctamente.

Ejecute el siguiente comando en el servidorUbuntu para escalar el servicio y lograr que no se obtengan fallos al realizar la prueba de carga.

```sh
# Ejemplo para escalar un servicio
docker service scale proyectofinal_microusuarios=2 
```

### Estadísticas de los balanceadores
Para ver las estadisticas de las peticiones que ingresan mediante los balanceadores puede utilizar las siguientes URLs.

Servicio | Url |  Descripción
------------ | ------------ | -------------
Balanceadorw | http://192.168.100.2:5080/haproxy?stats | Estadisticas del Balanceador del Portal
Balanceadors1 | http://192.168.100.2:5081/haproxy?stats | Estadísticas del Balanceador de los servicios de peticiones y clientes
Balanceadors2 | http://192.168.100.2:5082/haproxy?stats | Estadísticas del Balanceador del servicio de usuarios


### Elimine el Stack

```sh
# Escale un servicio
docker stack rm proyectofinal 
```



