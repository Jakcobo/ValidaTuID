// peticionesController.js
const { Router } = require('express');
const router = Router();
const peticionesModel = require('../models/peticionesModel');
const axios = require('axios'); // Asegúrate de haber instalado axios con `npm install axios`

// Obtener todas las peticiones
router.get('/peticiones', async (req, res) => {
    try {
        const result = await peticionesModel.traerPeticiones();
        res.json({
            status: 'success',
            data: result
        });
    } catch (error) {
        console.error("Error al obtener peticiones:", error);
        res.status(500).json({ status: 'error', message: "Error al obtener peticiones" });
    }
});

// Obtener peticiones filtradas
router.get('/peticiones_filtradas', async (req, res) => {
    const { estado, fechaInicio, fechaFin, cliente, tiposervicio } = req.query;
    try {
        const peticiones = await peticionesModel.filtrarPeticiones({ estado, fechaInicio, fechaFin, cliente, tiposervicio });
        res.json(peticiones);
    } catch (error) {
        console.error("Error al filtrar peticiones:", error);
        res.status(500).json({ status: 'error', message: "Error al filtrar peticiones" });
    }
});

// Obtener una petición específica por ID
router.get('/peticiones/:id', async (req, res) => {
    const id = req.params.id;

    try {
        const result = await peticionesModel.traerPeticion(id);
        if (result.length > 0) {
            res.json({
                status: 'success',
                data: result[0]
            });
        } else {
            res.status(404).json({ status: 'fail', message: "Petición no encontrada" });
        }
    } catch (error) {
        console.error("Error al obtener la petición:", error);
        res.status(500).json({ status: 'error', message: "Error al obtener la petición" });
    }
});

// Obtener peticiones con un estado pendiente
router.get('/peticiones_pendiente', async (req, res) => {
    try {
        const result = await peticionesModel.traerPeticionespendiente();
        res.json(result); // Devolver un array (vacío o con objetos)
    } catch (error) {
        console.error("Error al obtener peticiones pendientes:", error);
        res.status(500).json({ status: 'error', message: "Error al obtener peticiones pendientes" });
    }
});

// Crear una nueva petición (Cliente)
router.post('/peticiones', async (req, res) => {
    const { cccliente, ccarchivo, tiposervicio, bancocliente } = req.body;

    try {
        const result = await peticionesModel.crearPeticion(cccliente, ccarchivo, tiposervicio, bancocliente);
        res.status(201).json({ status: 'success', message: "Petición creada" });
    } catch (error) {
        console.error("Error al crear la petición:", error);
        res.status(500).json({ status: 'error', message: "Error al crear la petición" });
    }
});

// Consultar el id del usuario a través del microservicio de usuarios
router.get('/usuarios/:usuario', async (req, res) => {
    const usuario = req.params.usuario;

    try {
        // Hacer una solicitud GET al microservicio de usuarios para obtener el usuario
        const usuarioResponse = await axios.get(`http://balanceadors2:3002/usuarios/${usuario}`);
        const result = usuarioResponse.data;

        // Si el usuario se encuentra
        if (result) {
            res.json({
                status: 'success',
                data: result
            });
        } else {
            res.status(404).json({ status: 'fail', message: "Usuario no encontrado" });
        }
    } catch (error) {
        console.error("Error al obtener el usuario:", error);

        // Manejo de error si la llamada a otro microservicio falla
        if (error.response) {
            res.status(error.response.status).json({ status: 'error', message: error.response.data });
        } else {
            res.status(500).json({ status: 'error', message: "Error al obtener usuario" });
        }
    }
});

// Actualizar el estado de una petición (validador)
router.put('/peticiones/:id', async (req, res) => { // Ruta corregida
    const id = req.params.id;
    const { usuariovalidador, nombrevalidador, estado } = req.body;

    try {
        // Realizar una solicitud al microservicio de usuarios para verificar el usuario validador
        const usuarioResponse = await axios.get(`http://balanceadors2:3002/usuarios/${usuariovalidador}`);
        const usuario = usuarioResponse.data;

        if (!usuario) {
            return res.status(404).json({ status: 'fail', message: "Usuario validador no encontrado" });
        }

        // Verificar el rol del usuario validador
        if (usuario.rol !== 'validador') {
            return res.status(403).json({ status: 'fail', message: "El usuario no tiene permisos de validador" });
        }

        // Proceder a actualizar la petición
        const result = await peticionesModel.actualizarEstadoPeticion(id, usuariovalidador, nombrevalidador, estado);
        console.log("Resultado de actualizarEstadoPeticion:", result); // Agrega este log para depuración

        if (result.affectedRows > 0) {
            res.json({ status: 'success', message: "Estado de la petición actualizado" });
        } else {
            res.status(404).json({ status: 'fail', message: "Petición no encontrada" });
        }
    } catch (error) {
        console.error("Error al actualizar la petición:", error);

        // Manejo de error si la llamada al microservicio de usuarios falla
        if (error.response) {
            res.status(error.response.status).json({ status: 'error', message: error.response.data });
        } else {
            res.status(500).json({ status: 'error', message: "Error al actualizar la petición" });
        }
    }
});

// Obtener peticiones de un cliente específico
router.get('/peticiones/cliente/:cc', async (req, res) => {
    const cc = req.params.cc;
    try {
        const peticiones = await peticionesModel.traerPeticionesPorCliente(cc);
        res.status(200).json(peticiones);
    } catch (error) {
        console.error("Error al obtener peticiones del cliente:", error);
        res.status(500).send("Error al obtener peticiones del cliente");
    }
});

module.exports = router;
