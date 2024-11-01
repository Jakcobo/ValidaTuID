const express = require('express');
const peticionesController = require('./controllers/peticionesController');
const morgan = require('morgan'); 
const app = express();
app.use(morgan('dev'));
app.use(express.json());

app.use(peticionesController);

app.listen(3003, () => {
  console.log('Microservicio peticiones ejecutandose en el puerto 3003');
});