// clientesController.js
const { Router } = require('express');
const router = Router();
const clientesModel = require('../models/clientesModel');

// Obtener todos los clientes
router.get('/clientes', async (req, res) => {
    try {
        const result = await clientesModel.traerClientes();
        res.json({
            status: 'success',
            data: result
        });
    } catch (error) {
        console.error("Error al traer clientes:", error);
        res.status(500).send("Error al obtener clientes");
    }
});

// Validar cliente por cédula (cc) y password
router.get('/clientes/:cc/:password', async (req, res) => {
    const cc = req.params.cc;
    const password = req.params.password;

    try {
        // Llamar al modelo para validar al cliente
        const cliente = await clientesModel.validarCliente(cc, password);

        // Si el cliente existe y coincide la contraseña
        if (cliente.length > 0) {
            res.status(200).json(cliente[0]);
        } else {
            res.status(404).send("Cliente no encontrado o contraseña incorrecta");
        }
    } catch (error) {
        console.error("Error al validar cliente:", error);
        res.status(500).send("Error al validar cliente");
    }
});

// Validar cliente (autenticación)
router.post('/clientes/validar', async (req, res) => {
    const { cliente, password } = req.body;
    try {
        const result = await clientesModel.validarCliente(cliente, password);
        if (result.length > 0) {
            res.json({ status: 'success', message: "Cliente validado correctamente" });
        } else {
            res.status(401).json({ status: 'fail', message: "Credenciales inválidas" });
        }
    } catch (error) {
        console.error("Error al validar cliente:", error);
        res.status(500).send("Error al validar cliente");
    }
});

// Crear un nuevo cliente
router.post('/clientes', async (req, res) => {
    const { cc, password } = req.body;
    try {
        const result = await clientesModel.crearCliente(cc, password);
        res.status(201).send("Cliente creado");
    } catch (error) {
        if (error.message === 'DUPLICATE_ENTRY') {
            res.status(409).send("Cliente ya existe");
        } else {
            res.status(500).send("Error al crear cliente");
        }
    }
});

// Eliminar un cliente
router.delete('/clientes/:cliente', async (req, res) => {
    const cliente = req.params.cliente;
    try {
        const result = await clientesModel.eliminarCliente(cliente);
        if (result[0].affectedRows > 0) {
            res.json({ status: 'success', message: "Cliente eliminado" });
        } else {
            res.status(404).json({ status: 'fail', message: "Cliente no encontrado" });
        }
    } catch (error) {
        console.error("Error al eliminar cliente:", error);
        res.status(500).send("Error al eliminar cliente");
    }
});

// Actualizar un cliente
router.put('/clientes/:cliente', async (req, res) => {
    const cc = req.params.cliente;
    const { password } = req.body;
    try {
        const result = await clientesModel.actualizarCliente(cc, password);
        if (result[0].affectedRows > 0) {
            res.json({ status: 'success', message: "Cliente actualizado" });
        } else {
            res.status(404).json({ status: 'fail', message: "Cliente no encontrado" });
        }
    } catch (error) {
        console.error("Error al actualizar cliente:", error);
        res.status(500).send("Error al actualizar cliente");
    }
});

module.exports = router;
