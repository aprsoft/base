mysql -u root -p aprsoftc_produccion < ~/Descargas/aprsoftc_produccion.sql

lerd db:import ~/Descargas/aprsoftc_produccion2.sql

Importar respaldo, forzando las llaves

sed -e '1i SET FOREIGN_KEY_CHECKS=0;' -e '$a SET FOREIGN_KEY_CHECKS=1;' ~/Descargas/aprsoftc_produccion2.sql > ~/Descargas/importacion_forzada.sql && lerd db:import ~/Descargas/importacion_forzada.sql

Muestra el error entre rangos, a la linea que señala el error
sed -n '289648,289658p' ~/Descargas/importacion_forzada.sql | cut -c1-300