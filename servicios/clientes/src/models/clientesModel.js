const mysql = require('mysql2/promise');

const connection = mysql.createPool({
    host: 'peticionesdb',
    user: 'root',
    password: 'root',
    database: 'peticiones',
    port: 3306
});

async function traerClientes() {
    try {
        const result = await connection.query('SELECT * FROM clientes');
        return result[0];
    } catch (error) {
        throw error;
    }
}

async function traerCliente(cc) {
    try {
        const result = await connection.query('SELECT * FROM clientes WHERE cc = ?', [cc]);
        return result[0];
    } catch (error) {
        throw error;
    }
}

async function validarCliente(cc, password) {
    try {
        const result = await connection.query('SELECT * FROM clientes WHERE cc = ? AND password = ?', [cc, password]);
        return result[0];
    } catch (error) {
        throw error;
    }
}

async function crearCliente(cc, password) {
    try {
        const result = await connection.query('INSERT INTO clientes (cc, password) VALUES(?,?)', [cc, password]);
        return result;
    } catch (error) {
        if (error.code === 'ER_DUP_ENTRY') {
            throw new Error('DUPLICATE_ENTRY');
        } else {
            throw error;
        }
    }
}

async function eliminarCliente(cc) {
    try {
        const result = await connection.query('DELETE FROM clientes WHERE cc = ?', [cc]);
        return result;
    } catch (error) {
        throw error;
    }
}

async function actualizarCliente(cc, password) {
    try {
        const result = await connection.query(
            'UPDATE clientes SET password = ? WHERE cc = ?',
            [password, cc]
        );
        return result;
    } catch (error) {
        throw error;
    }
}

module.exports = {
    traerClientes, traerCliente, validarCliente, crearCliente, eliminarCliente, actualizarCliente
};
