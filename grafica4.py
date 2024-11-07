from pyspark.sql import SparkSession
from pyspark.sql.functions import col, count
import matplotlib.pyplot as plt

# Iniciar sesión en Spark
print("Iniciando sesión de Spark...")
spark = SparkSession.builder \
    .appName("Top 10 Clientes por Peticiones") \
    .getOrCreate()

# Ruta del archivo CSV y carga de datos
csv_path = "peticiones.csv"
print(f"Cargando datos desde {csv_path}...")

# Especificar el separador correcto y leer el CSV
df = spark.read.csv(csv_path, header=True, inferSchema=True)

# Mostrar algunas filas para verificar que se cargaron correctamente
df.show(10)
df.printSchema()

# 1. Seleccionar los 10 Clientes con Más Peticiones
print("Calculando el top 10 de clientes con más peticiones...")
df_clientes = df.groupBy("cccliente").agg(count("id").alias("cantidad_peticiones"))
df_top10_clientes = df_clientes.orderBy(col("cantidad_peticiones").desc()).limit(10)

# Convertir a Pandas para la visualización
df_top10_clientes_pd = df_top10_clientes.toPandas()

# 2. Generar Gráfica de Barras
plt.figure(figsize=(10, 6))
plt.bar(df_top10_clientes_pd["cccliente"].astype(str), df_top10_clientes_pd["cantidad_peticiones"], color="#D4A017")
plt.xlabel("Cliente (cccliente)")
plt.ylabel("Cantidad de Peticiones")
plt.title("Top 10 Clientes con Más Peticiones")
plt.xticks(rotation=45, ha="right")

# Guardar la gráfica
output_path = "Top10_Clientes_Peticiones.png"
print(f"Guardando gráfica en {output_path}...")
plt.tight_layout()
plt.savefig(output_path)
plt.clf()

# Cerrar la sesión de Spark
print("Finalizando sesión de Spark.")
spark.stop()
print("Ejecución del script finalizada.")
