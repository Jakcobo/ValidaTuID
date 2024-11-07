from pyspark.sql import SparkSession
from pyspark.sql.functions import col, count, hour
import matplotlib.pyplot as plt

# Iniciar sesión en Spark
print("Iniciando sesión de Spark...")
spark = SparkSession.builder \
    .appName("Analisis de Peticiones") \
    .getOrCreate()

# Ruta del archivo CSV y carga de datos
csv_path = "peticiones.csv"
print(f"Cargando datos desde {csv_path}...")
df = spark.read.csv(csv_path, header=True, inferSchema=True)

# 1. Gráfica de Conteo de Peticiones por Usuario
print("Generando gráfica de Conteo de Peticiones por Validador...")
df_usuarios = df.groupBy("usuariovalidador").agg(count("id").alias("cantidad_peticiones"))
df_usuarios = df_usuarios.filter(df_usuarios["usuariovalidador"].isNotNull())

df_usuarios_pd = df_usuarios.toPandas()
df_usuarios_pd = df_usuarios_pd.sort_values(by="cantidad_peticiones", ascending=False)

plt.figure(figsize=(10, 6))
plt.bar(df_usuarios_pd["usuariovalidador"], df_usuarios_pd["cantidad_peticiones"], color="skyblue")
plt.xlabel("Usuario")
plt.ylabel("Cantidad de Peticiones")
plt.title("Cantidad de Peticiones por Usuario")
plt.xticks(rotation=45, ha="right")

output_path_usuario = "Por_validador.png"
print(f"Guardando gráfica de usuarios en {output_path_usuario}...")
plt.tight_layout()
plt.savefig(output_path_usuario)
plt.clf()  # Limpiar la figura para la siguiente gráfica

# 2. Gráfica de Conteo de Peticiones por Hora
print("Generando gráfica de Conteo de Peticiones por Hora...")
df_hora = df.withColumn("hora", hour(col("horacreacion"))) \
            .groupBy("hora") \
            .agg(count("id").alias("cantidad_peticiones"))

df_hora_pd = df_hora.toPandas()
df_hora_pd = df_hora_pd.sort_values(by="hora")

plt.figure(figsize=(10, 6))
plt.bar(df_hora_pd["hora"], df_hora_pd["cantidad_peticiones"], color="salmon")
plt.xlabel("Hora del Día")
plt.ylabel("Cantidad de Peticiones")
plt.title("Cantidad de Peticiones por Hora de Creación")
plt.xticks(range(0, 24), [f"{h}:00" for h in range(0, 24)], rotation=45)

output_path_hora = "Por_Hora.png"
print(f"Guardando gráfica de horas en {output_path_hora}...")
plt.tight_layout()
plt.savefig(output_path_hora)

# Cerrar la sesión de Spark
print("Finalizando sesión de Spark.")
spark.stop()
print("Ejecución del script finalizada.")

