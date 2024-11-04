import pandas as pd
import mysql.connector
from mysql.connector import errorcode
import random
import string

# Función para generar contraseñas simples en texto plano
def generar_contraseña():
    # Puedes personalizar esta función para generar contraseñas según tus necesidades
    opciones = ['123', '12345', 'password', 'abc123', 'qwerty', 'letmein']
    return random.choice(opciones)

# Ruta al archivo CSV
ruta_csv = r'dataFrameProyecto.csv'

# Leer el archivo CSV y seleccionar los primeros 5000 registros únicos basados en 'Usuario'
try:
    dataFrameProyecto = pd.read_csv(ruta_csv)
    print("Archivo CSV leído correctamente.")
except FileNotFoundError:
    print(f"El archivo no se encontró en la ruta especificada: {ruta_csv}")
    exit()
except Exception as e:
    print(f"Error al leer el archivo CSV: {e}")
    exit()

# Seleccionar los primeros 5000 registros únicos basados en 'Usuario'
df = dataFrameProyecto.drop_duplicates(subset=['Usuario']).head(5000).copy()

# Renombrar las columnas para que coincidan con la tabla 'usuarios'
df = df.rename(columns={
    'Usuario': 'usuario',
    'Nombre': 'nombre'
})

# Verificar que las columnas necesarias existan
required_columns = ['usuario', 'nombre']
for col in required_columns:
    if col not in df.columns:
        print(f"La columna '{col}' no se encuentra en el archivo CSV.")
        exit()

# Asignar roles: los primeros 5 como 'admin' y el resto como 'validador'
total_registros = len(df)
if total_registros < 5:
    print("No hay suficientes registros para asignar roles. Se necesitan al menos 5 usuarios.")
    exit()

df['rol'] = ['admin'] * 5 + ['validador'] * (total_registros - 5)

# Generar contraseñas simples en texto plano
df['password'] = [generar_contraseña() for _ in range(total_registros)]

# Reorganizar las columnas para que coincidan con la tabla 'usuarios'
df = df[['usuario', 'nombre', 'rol', 'password']]

# Conexión a la base de datos de 'usuarios'
try:
    conexion_usuarios = mysql.connector.connect(
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
        print("Error: La base de datos no existe.")
    else:
        print(err)
    exit()

cursor_usuarios = conexion_usuarios.cursor()

# Obtener todos los 'usuario' existentes en la base de datos para evitar duplicados
try:
    cursor_usuarios.execute("SELECT usuario FROM usuarios")
    usuarios_existentes = set([item[0] for item in cursor_usuarios.fetchall()])
    print(f"Se encontraron {len(usuarios_existentes)} usuarios existentes en la base de datos.")
except mysql.connector.Error as err:
    print(f"Error al obtener usuarios existentes: {err}")
    cursor_usuarios.close()
    conexion_usuarios.close()
    exit()

# Filtrar los usuarios que ya existen
df_nuevos = df[~df['usuario'].isin(usuarios_existentes)].copy()
print(f"Se insertarán {len(df_nuevos)} nuevos usuarios.")

# Crear lista de tuplas para inserción
data_to_insert = list(df_nuevos[['usuario', 'nombre', 'rol', 'password']].itertuples(index=False, name=None))

# Preparar la consulta de inserción para 'usuarios'
insert_query_usuarios = """
    INSERT INTO usuarios (usuario, nombre, rol, password)
    VALUES (%s, %s, %s, %s)
"""

# Insertar los datos en la tabla 'usuarios'
if data_to_insert:
    try:
        cursor_usuarios.executemany(insert_query_usuarios, data_to_insert)
        conexion_usuarios.commit()
        print(f"{cursor_usuarios.rowcount} registros insertados en 'usuarios' correctamente.")
    except mysql.connector.Error as err:
        print(f"Error al insertar en 'usuarios': {err}")
        conexion_usuarios.rollback()
    finally:
        cursor_usuarios.close()
        conexion_usuarios.close()
        print("Conexión a la base de datos cerrada.")
else:
    print("No hay nuevos usuarios para insertar.")
    cursor_usuarios.close()
    conexion_usuarios.close()
