FROM python:3.9-slim

# Instalar mysql-client, bash y dos2unix
RUN apt-get update && apt-get install -y default-mysql-client bash dos2unix && rm -rf /var/lib/apt/lists/*

WORKDIR /peticionesdir

COPY dataFrameProyecto.csv /peticionesdir/dataFrameProyecto.csv
COPY insertarClientes.py /peticionesdir/insertarClientes.py
COPY insertarPeticiones.py /peticionesdir/insertarPeticiones.py
COPY procesar_e_insertar_usuarios.py /peticionesdir/procesar_e_insertar_usuarios.py
COPY requirements.txt /peticionesdir/requirements.txt
COPY run.sh /peticionesdir/run.sh

RUN dos2unix /peticionesdir/run.sh
RUN chmod +x /peticionesdir/run.sh
RUN pip install --no-cache-dir -r requirements.txt

CMD ["./run.sh"]
