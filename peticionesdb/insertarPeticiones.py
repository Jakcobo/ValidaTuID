import pandas as pd
import mysql.connector
import random
from mysql.connector import errorcode

# Ruta al archivo CSV
ruta_csv = 'dataFrameProyecto.csv'

# Leer el archivo CSV
try:
    df = pd.read_csv(ruta_csv)
    print("Archivo CSV leído correctamente.")
except FileNotFoundError:
    print(f"El archivo CSV no se encontró en la ruta especificada: {ruta_csv}")
    exit()
except Exception as e:
    print(f"Error al leer el archivo CSV: {e}")
    exit()

# Seleccionar solo las primeras 8000 filas y eliminar duplicados internos
df = df.head(7000).copy().drop_duplicates(subset=['NumeroDocumento'])
print(f"Se seleccionaron {len(df)} registros únicos para la inserción.")

# Convertir las columnas de fecha y hora a los formatos de MySQL
df['FechaCreacion'] = pd.to_datetime(df['FechaCreacion']).dt.date  # Formato DATE
df['HoraCreacion'] = pd.to_datetime(df['HoraCreacion'], format="%H:%M:%S", errors='coerce').dt.time  # Formato TIME
df['FechaRevision'] = pd.to_datetime(df['FechaRevision']).dt.date  # Formato DATE
df['HoraRevision'] = pd.to_datetime(df['HoraRevision'], format="%H:%M:%S", errors='coerce').dt.time  # Formato TIME

# Lista de usuarios excluidos
usuarios_excluidos = ['chernandez', 'dalvarez_5', 'lgalindo', 'lrodriguez_5', 'hcarrillo']
opciones_tiposervicio = ['CDT', 'Cuenta Ahorros', 'Credito']
opciones_ccarchivo = [
    'ccarchivo_6724233053a8d5.03486841.jpg',
    'ccarchivo_672427bf652e74.88223465.pdf',
    'ccarchivo_672427e00a8230.48292646.jpeg',
    'ccarchivo_6724233913d190.95667274.jpg'
]

# Conexión a la base de datos de 'peticiones'
try:
    conexion = mysql.connector.connect(
        host="peticionesdb",
        user="root",
        password="root",
        database="peticiones"
    )
    print("Conexión a la base de datos establecida.")
except mysql.connector.Error as err:
    if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
        print("Error: Usuario o contraseña incorrectos.")
    elif err.errno == errorcode.ER_BAD_DB_ERROR:
        print("Error: La base de datos 'peticiones' no existe.")
    else:
        print(err)
    exit()

cursor = conexion.cursor()

# Obtener todos los 'cccliente' existentes en la base de datos para evitar duplicados
try:
    cursor.execute("SELECT cccliente FROM peticiones")
    cc_existentes = set([str(item[0]) for item in cursor.fetchall()])
    print(f"Se encontraron {len(cc_existentes)} documentos existentes en la base de datos.")
except mysql.connector.Error as err:
    print(f"Error al obtener documentos existentes: {err}")
    cursor.close()
    conexion.close()
    exit()

# Filtrar documentos que ya existen
df_nuevos = df[~df['NumeroDocumento'].astype(str).isin(cc_existentes)].copy()
print(f"Se intentarán insertar {len(df_nuevos)} nuevas peticiones.")

# Crear lista de tuplas para la inserción
data_to_insert = []

for _, fila in df_nuevos.iterrows():
    tiposervicio = random.choice(opciones_tiposervicio)
    ccarchivo = random.choice(opciones_ccarchivo)
    if fila['Usuario'] in usuarios_excluidos:
        usuariovalidador = 'nsoler'
        nombrevalidador = 'Nicol Tatiana Soler Valencia'
    else:
        usuariovalidador = fila['Usuario']
        nombrevalidador = fila['Nombre']
    
    valores = (
        fila['NumeroDocumento'],  # cccliente
        ccarchivo,  # ccarchivo
        tiposervicio,  # tiposervicio aleatorio
        fila['Cliente'],  # bancocliente
        fila['FechaCreacion'],  # fechasolicitud
        fila['HoraCreacion'],   # horasolicitud
        usuariovalidador,
        nombrevalidador,
        fila['Aprobado/Rechazado'],  # estado
        fila['FechaCreacion'],  # fechacreacion
        fila['HoraCreacion'],   # horacreacion
        fila['FechaRevision'],  # fecharevision
        fila['HoraRevision']    # horarevision
    )
    data_to_insert.append(valores)

# Preparar la consulta de inserción con manejo de duplicados
insert_query = """
    INSERT IGNORE INTO peticiones (
        cccliente, ccarchivo, tiposervicio, bancocliente,
        fechasolicitud, horasolicitud, usuariovalidador, nombrevalidador,
        estado, fechacreacion, horacreacion, fecharevision, horarevision
    ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
"""

# Insertar los datos en la tabla 'peticiones'
if data_to_insert:
    try:
        cursor.executemany(insert_query, data_to_insert)
        conexion.commit()
        print(f"{cursor.rowcount} registros insertados en 'peticiones' correctamente.")
    except mysql.connector.Error as err:
        print(f"Error al insertar en 'peticiones': {err}")
        conexion.rollback()
    finally:
        cursor.close()
        conexion.close()
        print("Conexión a la base de datos cerrada.")
else:
    print("No hay nuevas peticiones para insertar.")
    cursor.close()
    conexion.close()
