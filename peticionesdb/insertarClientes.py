import pandas as pd
import mysql.connector
from mysql.connector import errorcode

# Función para generar contraseñas simples en texto plano
def generar_contraseña():
    return '12345'  # Contraseña fija como solicitaste

# Ruta al archivo CSV
ruta_csv = r'dataFrameProyecto.csv'

# Leer el archivo CSV y seleccionar la columna de NumeroDocumento
try:
    dataFrameProyecto = pd.read_csv(ruta_csv)
    print("Archivo CSV leído correctamente.")
except FileNotFoundError:
    print(f"El archivo CSV no se encontró en la ruta especificada: {ruta_csv}")
    exit()
except Exception as e:
    print(f"Error al leer el archivo CSV: {e}")
    exit()

# Verificar que la columna 'NumeroDocumento' exista
if 'NumeroDocumento' not in dataFrameProyecto.columns:
    print("La columna 'NumeroDocumento' no existe en el archivo CSV.")
    exit()

# Seleccionar solo la columna NumeroDocumento y renombrarla a cc
df = dataFrameProyecto[['NumeroDocumento']].rename(columns={'NumeroDocumento': 'cc'})

# Seleccionar los primeros 1500 registros
df = df.head(1500).copy()

# Eliminar duplicados internos en 'cc'
df = df.drop_duplicates(subset=['cc'])
print(f"Se seleccionaron {len(df)} registros únicos para la inserción.")

# Asignar una contraseña simple "12345" para todos los clientes
df['password'] = '12345'

# Conexión a la base de datos de 'clientes'
try:
    conexion_clientes = mysql.connector.connect(
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
        print("Error: La base de datos 'clientes' no existe.")
    else:
        print(err)
    exit()

cursor_clientes = conexion_clientes.cursor()

# Obtener todos los 'cc' existentes en la base de datos para evitar duplicados
try:
    cursor_clientes.execute("SELECT cc FROM clientes")
    cc_existentes = set([str(item[0]) for item in cursor_clientes.fetchall()])
    print(f"Se encontraron {len(cc_existentes)} cédulas existentes en la base de datos.")
except mysql.connector.Error as err:
    print(f"Error al obtener cédulas existentes: {err}")
    cursor_clientes.close()
    conexion_clientes.close()
    exit()

# Filtrar los cédulas que ya existen
df_nuevos = df[~df['cc'].astype(str).isin(cc_existentes)].copy()
print(f"Se intentarán insertar {len(df_nuevos)} nuevos clientes.")

# Verificar duplicados dentro de df_nuevos
duplicados = df_nuevos.duplicated(subset=['cc'], keep=False)
if duplicados.any():
    print("Advertencia: Se encontraron duplicados en los datos a insertar. Se eliminarán.")
    df_nuevos = df_nuevos.drop_duplicates(subset=['cc'])

print(f"Después de eliminar duplicados internos, se insertarán {len(df_nuevos)} nuevos clientes.")

# Crear lista de tuplas para inserción
data_to_insert = list(df_nuevos[['cc', 'password']].itertuples(index=False, name=None))

# Preparar la consulta de inserción para 'clientes' con manejo de duplicados
# Usaremos INSERT IGNORE para saltar duplicados y evitar errores
insert_query_clientes = """
    INSERT IGNORE INTO clientes (cc, password)
    VALUES (%s, %s)
"""

# Insertar los datos en la tabla 'clientes'
if data_to_insert:
    try:
        cursor_clientes.executemany(insert_query_clientes, data_to_insert)
        conexion_clientes.commit()
        print(f"{cursor_clientes.rowcount} registros insertados en 'clientes' correctamente.")
    except mysql.connector.Error as err:
        print(f"Error al insertar en 'clientes': {err}")
        conexion_clientes.rollback()
    finally:
        cursor_clientes.close()
        conexion_clientes.close()
        print("Conexión a la base de datos cerrada.")
else:
    print("No hay nuevos clientes para insertar.")
    cursor_clientes.close()
    conexion_clientes.close()
