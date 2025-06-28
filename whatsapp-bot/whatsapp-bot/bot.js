const venom = require('venom-bot');
const express = require('express');
const app = express();

app.use(express.json());

let clientGlobal = null;
let botReady = false; // NUEVO

venom
    .create({ session: 'consultorio' })
    .then((client) => {
        clientGlobal = client;
        botReady = true; // MARCA QUE ESTÁ LISTO
        console.log('Bot de WhatsApp iniciado ✅');
    })
    .catch((error) => {
        console.error('Error al iniciar Venom:', error);
    });

// Verificar si el bot está listo
app.get('/status', (req, res) => {
    if (botReady) {
        return res.send('ready');
    }
    res.status(503).send('not ready');
});

// Enviar mensaje
app.post('/send-message', async (req, res) => {
    const { phone, message } = req.body;

    if (!clientGlobal || !botReady) {
        return res.status(503).send('Bot no está listo para enviar mensajes');
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

app.listen(3000, () => {
    console.log('Servidor escuchando en http://localhost:3000');
});
