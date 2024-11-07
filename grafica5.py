# Importar librerías necesarias
from pyspark.sql import SparkSession
from pyspark.sql.functions import col, concat_ws, to_timestamp, unix_timestamp, avg, substring
import matplotlib.pyplot as plt
import pandas as pd

# Crear una sesión de Spark
spark = SparkSession.builder.appName("TiempoRespuestaPorValidador").getOrCreate()

# Leer el dataset desde el archivo CSV
df = spark.read.option("header", True).csv("peticiones.csv")

# Extraer la parte de la fecha de 'fecharevision' (primeros 10 caracteres)
df = df.withColumn('fecharevision_clean', substring(col('fecharevision'), 1, 10))

# Combinar 'fechasolicitud' y 'horasolicitud' para crear 'timestamp_solicitud'
df = df.withColumn("timestamp_solicitud", to_timestamp(concat_ws(' ', col('fechasolicitud'), col('horasolicitud')), 'yyyy-MM-dd HH:mm:ss'))

# Combinar 'fecharevision_clean' y 'horarevision' para crear 'timestamp_revision'
df = df.withColumn("timestamp_revision", to_timestamp(concat_ws(' ', col('fecharevision_clean'), col('horarevision')), 'yyyy-MM-dd HH:mm:ss'))

# Calcular el tiempo de respuesta en minutos
df = df.withColumn("tiempo_respuesta_minutos", (unix_timestamp(col("timestamp_revision")) - unix_timestamp(col("timestamp_solicitud"))) / 60)

# Filtrar registros con tiempos de respuesta válidos y mayores a cero
df_filtered = df.filter((col("tiempo_respuesta_minutos").isNotNull()) & (col("tiempo_respuesta_minutos") > 0))

# Calcular el tiempo promedio de respuesta por validador
df_validador = df_filtered.groupBy("usuariovalidador").agg(avg("tiempo_respuesta_minutos").alias("tiempo_promedio"))

# Convertir a Pandas DataFrame para graficar
pdf_validador = df_validador.toPandas()

# Ordenar los validadores por tiempo promedio
pdf_validador = pdf_validador.sort_values(by='tiempo_promedio')

# Revisar estadísticos descriptivos para identificar valores atípicos
print(pdf_validador['tiempo_promedio'].describe())

# Crear el gráfico de tiempo de respuesta por validador
plt.figure(figsize=(10,6))
plt.bar(pdf_validador['usuariovalidador'], pdf_validador['tiempo_promedio'], color='green')
plt.xlabel('Nombre del Validador')
plt.ylabel('Tiempo Promedio de Respuesta (minutos)')
plt.title('Tiempo de Respuesta Promedio por Validador')
plt.xticks(rotation=45, ha='right')
plt.tight_layout()

# Guardar el gráfico como PNG
plt.savefig('tiempo_respuesta_por_validador.png', dpi=300)
plt.close()

# Cerrar la sesión de Spark
spark.stop()
