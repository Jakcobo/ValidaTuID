// usuariosController.js

const { Router } = require('express');
const router = Router();
const usuariosModel = require('../models/usuariosModel');

// Obtener todos los usuarios
router.get('/usuarios', async (req, res) => {
    try {
        const result = await usuariosModel.traerUsuarios();
        res.json(result);
    } catch (error) {
        console.error("Error al obtener usuarios:", error);
        res.status(500).send("Error al obtener usuarios");
    }
});

// Obtener un usuario específico por nombre de usuario
router.get('/usuarios/:usuario', async (req, res) => {
    const usuario = req.params.usuario;
    
    try {
        const result = await usuariosModel.traerUsuario(usuario);
        if (result.length > 0) {
            res.json(result[0]);
        } else {
            res.status(404).send("Usuario no encontrado");
        }
    } catch (error) {
        console.error("Error al obtener usuario:", error);
        res.status(500).send("Error al obtener usuario");
    }
});

// Crear un nuevo usuario (admin o validador)
router.post('/usuarios', async (req, res) => {
    const { usuario, nombre, rol, password } = req.body;

    try {
        const result = await usuariosModel.crearUsuario(usuario, nombre, rol, password);
        res.status(201).json({ status: 'success', message: "Usuario creado" });
    } catch (error) {
        console.error("Error al crear usuario:", error);
        
        // Verificar si el error es debido a una entrada duplicada
        if (error.code === 'ER_DUP_ENTRY') { // Código de error de MySQL para entradas duplicadas
            res.status(409).json({ status: 'fail', message: "Usuario ya existe" });
        } else {
            res.status(500).json({ status: 'error', message: "Error al crear usuario" });
        }
    }
});

// Actualizar un usuario
router.put('/usuarios/:usuario', async (req, res) => {
    const usuario = req.params.usuario;
    const { nombre, rol, password } = req.body;

    try {
        // Ejecutar la actualización del usuario
        const result = await usuariosModel.actualizarUsuario(usuario, nombre, rol, password);

        // Log para depuración
        console.log("Resultado de la actualización:", result);

        // Verificar si se afectó alguna fila
        if (result.affectedRows > 0) {
            res.status(200).json({ status: 'success', message: "Usuario actualizado" });
        } else {
            res.status(404).json({ status: 'fail', message: "Usuario no encontrado" });
        }
    } catch (error) {
        console.error("Error al actualizar usuario:", error);
        res.status(500).json({ status: 'error', message: "Error al actualizar usuario" });
    }
});

// Eliminar un usuario
router.delete('/usuarios/:usuario', async (req, res) => {
    const usuario = req.params.usuario;

    try {
        console.log(`Intentando eliminar el usuario: ${usuario}`);

        // Ejecuta la eliminación
        const result = await usuariosModel.eliminarUsuario(usuario); // Aquí desestructuramos solo el primer valor

        // Log para ver qué devuelve result
        console.log("Resultado de la eliminación:", result);

        // Verificar si la eliminación afectó alguna fila
        if (result.affectedRows > 0) {
            console.log("Usuario eliminado correctamente.");
            return res.status(200).send("Usuario eliminado");
        } else {
            console.log("Usuario no encontrado.");
            return res.status(404).send("Usuario no encontrado");
        }
    } catch (error) {
        console.error("Error al eliminar usuario:", error);
        return res.status(500).send("Error al eliminar usuario");
    }
});

module.exports = router;
