const venom = require('venom-bot');
const express = require('express');
const app = express();

app.use(express.json());

let clientGlobal = null;

// Crear sesión
venom
    .create({ session: 'consultorio' })
    .then((client) => {
        clientGlobal = client;
        console.log('Bot de WhatsApp iniciado ✅');
    })
    .catch((error) => {
        console.error('Error al iniciar Venom:', error);
    });

// Ruta POST para recibir mensajes desde Laravel
app.post('/send-message', async (req, res) => {
    const { phone, message } = req.body;

    if (!clientGlobal) {
        return res.status(500).send('Bot no iniciado');
    }

    try {
        await clientGlobal.sendText(`${phone}@c.us`, message);
        console.log(`Mensaje enviado a ${phone}: ${message}`);
        res.send('Mensaje enviado');
    } catch (error) {
        console.error('Error enviando mensaje:', error);
        res.status(500).send('Error al enviar mensaje');
    }
});

// Iniciar servidor en puerto 3000
app.listen(3000, () => {
    console.log('Servidor escuchando en http://localhost:3000');
});
