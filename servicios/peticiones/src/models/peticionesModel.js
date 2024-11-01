// peticionesModel.js
const mysql = require('mysql2/promise');

const connection = mysql.createPool({
    host: 'peticionesdb',
    user: 'root',
    password: 'root',
    database: 'peticiones',
    port: 3306
});

const connectionUsuarios = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'usuarios',
    port: 3306
});

async function traerPeticiones() {
    const [result] = await connection.query('SELECT * FROM peticiones');
    return result;
}

async function traerPeticionespendiente() {
    const [result] = await connection.query('SELECT * FROM peticiones WHERE estado = ?', ['pendiente']);
    return result;
}

async function traerPeticion(id) {
    const [result] = await connection.query('SELECT * FROM peticiones WHERE id = ?', [id]);
    return result;
}

async function crearPeticion(cccliente, ccarchivo, tiposervicio, bancocliente) {
    const [result] = await connection.query(
        'INSERT INTO peticiones (cccliente, ccarchivo, tiposervicio, bancocliente, fechasolicitud, horasolicitud, estado) VALUES (?, ?, ?, ?, CURRENT_DATE, CURRENT_TIME, "pendiente")',
        [cccliente, ccarchivo, tiposervicio, bancocliente]
    );
    return result;
}

async function consultarUsuario(usuario) {
    const [result] = await connectionUsuarios.query(
        'SELECT * FROM usuarios WHERE usuario = ?', 
        [usuario]
    );
    return result;
}

async function actualizarEstadoPeticion(id, usuariovalidador, nombrevalidador, estado) {
    const [result] = await connection.query(
        'UPDATE peticiones SET usuariovalidador = ?, nombrevalidador = ?, estado = ?, fecharevision = CURRENT_DATE, horarevision = CURRENT_TIME WHERE id = ?', 
        [usuariovalidador, nombrevalidador, estado, id]
    );
    return result; // Retorna el objeto result
}


async function traerPeticionesPorCliente(cccliente) {
    try {
        const [result] = await connection.query('SELECT * FROM peticiones WHERE cccliente = ?', [cccliente]);
        return result;
    } catch (error) {
        throw error;
    }
}

async function actualizarPeticion(id, usuariovalidador, nombrevalidador, estado) {
    try {
        const [result] = await connection.query(
            'UPDATE peticiones SET usuariovalidador = ?, nombrevalidador = ?, estado = ? WHERE id = ?',
            [usuariovalidador, nombrevalidador, estado, id]
        );
        return result;
    } catch (error) {
        throw error;
    }
}

async function filtrarPeticiones(filtros) {
    let query = 'SELECT * FROM peticiones WHERE 1=1';
    const params = [];

    if (filtros.estado) {
        query += ' AND estado = ?';
        params.push(filtros.estado);
    }
    if (filtros.fechaInicio) {
        query += ' AND fechasolicitud >= ?';
        params.push(filtros.fechaInicio);
    }
    if (filtros.fechaFin) {
        query += ' AND fechasolicitud <= ?';
        params.push(filtros.fechaFin);
    }
    if (filtros.cliente) {
        query += ' AND cccliente = ?';
        params.push(filtros.cliente);
    }
    if (filtros.tiposervicio) {
        query += ' AND tiposervicio = ?';
        params.push(filtros.tiposervicio);
    }

    const [rows] = await connection.query(query, params);
    return rows;
}

module.exports = {
    traerPeticiones,
    traerPeticionespendiente,
    traerPeticion,
    crearPeticion,
    consultarUsuario,
    actualizarEstadoPeticion,
    traerPeticionesPorCliente,
    actualizarPeticion,
    filtrarPeticiones
};
