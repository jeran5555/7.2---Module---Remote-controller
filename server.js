const express = require('express');
const app = express();
const http = require('http').createServer(app);
const io = require('socket.io')(http, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

const PORT = 3000;

io.on('connection', (socket) => {
    console.log('Een gebruiker is verbonden:', socket.id);

    // Registreer het beeldscherm (index.php) in een "displays" kanaal
    socket.on('register_display', () => {
        socket.join('displays');
        console.log('Display geregistreerd:', socket.id);
    });

    // Ontvang input van de mobiele controller (controller.php)
    socket.on('move', (data) => {
        // En stuur het door naar iedereen in het 'displays' kanaal
        io.to('displays').emit('move', data);
    });

    socket.on('disconnect', () => {
        console.log('Gebruiker is gedisconnect:', socket.id);
    });
});

http.listen(PORT, '0.0.0.0', () => {
    console.log(`Socket.IO Server draait op poort ${PORT} en luistert naar alle netwerk interfaces.`);
    console.log('Controleer of je poort 3000 geopend is op je firewall voor mobiele toegang!');
});
