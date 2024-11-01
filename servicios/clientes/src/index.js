const express = require('express');
const clientesController = require('./controllers/clientesController');
const morgan = require('morgan'); 
const app = express();
app.use(morgan('dev'));
app.use(express.json());


app.use(clientesController);


app.listen(3001, () => {
  console.log('Microservicio clientes ejecutandose en el puerto 3001');
});