FROM python:3.9-slim

# Instalar mysql-client
RUN apt-get update && apt-get install -y default-mysql-client && rm -rf /var/lib/apt/lists/*

WORKDIR /peticionesdir

COPY dataFrameProyecto.csv /peticionesdir/dataFrameProyecto.csv
COPY insertarClientes.py /peticionesdir/insertarClientes.py
COPY insertarPeticiones.py /peticionesdir/insertarPeticiones.py
COPY procesar_e_insertar_usuarios.py /peticionesdir/procesar_e_insertar_usuarios.py
COPY requirements.txt /peticionesdir/requirements.txt
COPY run.sh /peticionesdir/run.sh

RUN chmod +x run.sh
RUN pip install --no-cache-dir -r requirements.txt

CMD ["./run.sh"]