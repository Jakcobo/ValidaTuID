// usuariosModel.js
const mysql = require('mysql2/promise');

const connection = mysql.createPool({
    host: 'peticionesdb',
    user: 'root',
    password: 'root',
    database: 'peticiones',
    port: 3306
});

// Traer todos los usuarios
async function traerUsuarios() {
    const [rows] = await connection.query('SELECT * FROM usuarios');
    return rows;
}

// Traer un usuario específico
async function traerUsuario(usuario) {
    const [rows] = await connection.query('SELECT * FROM usuarios WHERE usuario = ?', [usuario]);
    return rows;
}

// Crear un nuevo usuario
async function crearUsuario(usuario, nombre, rol, password) {
    try {
        const [result] = await connection.query(
            'INSERT INTO usuarios (usuario, nombre, rol, password) VALUES (?, ?, ?, ?)',
            [usuario, nombre, rol, password]
        );
        return result;
    } catch (error) {
        throw error; // Propagar el error para que el controlador lo maneje
    }
}

// Actualizar un usuario
async function actualizarUsuario(usuario, nombre, rol, password) {
    try {
        const [result] = await connection.query(
            'UPDATE usuarios SET nombre = ?, rol = ?, password = ? WHERE usuario = ?',
            [nombre, rol, password, usuario]
        );
        return result;
    } catch (error) {
        throw error;
    }
}

// Eliminar un usuario
async function eliminarUsuario(usuario) {
    try {
        const [result] = await connection.query(
            'DELETE FROM usuarios WHERE usuario = ?',
            [usuario]
        );
        return result;
    } catch (error) {
        throw error;
    }
}

module.exports = {
    traerUsuarios,
    traerUsuario,
    crearUsuario,
    actualizarUsuario,
    eliminarUsuario
};
