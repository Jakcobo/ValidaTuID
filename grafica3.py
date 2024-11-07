from pyspark.sql import SparkSession
from pyspark.sql.functions import col, count, hour, to_date, to_timestamp
import matplotlib.pyplot as plt

# Iniciar sesión en Spark
print("Iniciando sesión de Spark...")
spark = SparkSession.builder \
    .appName("Análisis de Peticiones") \
    .getOrCreate()

# Ruta del archivo CSV y carga de datos
csv_path = "peticiones.csv"
print(f"Cargando datos desde {csv_path}...")

# Especificar el separador correcto
df = spark.read.csv(csv_path, header=True, inferSchema=True)

# Mostrar algunas filas para verificar que se cargaron correctamente
df.show(5)

# 2. Gráfica de Conteo de Peticiones por Tipo de Servicio como pastel
print("Generando gráfica de Conteo de Peticiones por Tipo de Servicio...")
df_servicio = df.groupBy("tiposervicio").agg(count("id").alias("cantidad_peticiones"))
df_servicio = df_servicio.filter(df_servicio["tiposervicio"].isNotNull())

df_servicio_pd = df_servicio.toPandas()
df_servicio_pd = df_servicio_pd.sort_values(by="cantidad_peticiones", ascending=False)

plt.figure(figsize=(10, 6))
plt.pie(df_servicio_pd["cantidad_peticiones"], labels=df_servicio_pd["tiposervicio"], autopct='%1.1f%%', startangle=140)
plt.title("Cantidad de Peticiones por Tipo de Servicio")

output_path_servicio = "Por_TipoDeServicio_Pastel.png"
print(f"Guardando gráfica de tipo de servicio en {output_path_servicio}...")
plt.tight_layout()
plt.savefig(output_path_servicio)
plt.clf()

# 3. Gráfica de Conteo de Peticiones por Estado como pastel
print("Generando gráfica de Conteo de Peticiones por Estado...")
df_estado = df.groupBy("estado").agg(count("id").alias("cantidad_peticiones"))
df_estado = df_estado.filter(df_estado["estado"].isNotNull())

df_estado_pd = df_estado.toPandas()
df_estado_pd = df_estado_pd.sort_values(by="cantidad_peticiones", ascending=False)

plt.figure(figsize=(10, 6))
plt.pie(df_estado_pd["cantidad_peticiones"], labels=df_estado_pd["estado"], autopct='%1.1f%%', startangle=140)
plt.title("Cantidad de Peticiones por Estado")

output_path_estado = "Por_Estado_Pastel.png"
print(f"Guardando gráfica de estado en {output_path_estado}...")
plt.tight_layout()
plt.savefig(output_path_estado)

# Cerrar la sesión de Spark
print("Finalizando sesión de Spark.")
spark.stop()
print("Ejecución del script finalizada.")
