#!/bin/bash
set -e

# Esperar hasta que la base de datos esté lista
until mysqladmin ping -h peticionesdb -u root -proot --silent; do
    echo "Esperando a que la base de datos esté lista..."
    sleep 5
done

python insertarClientes.py
python insertarPeticiones.py
python procesar_e_insertar_usuarios.py 