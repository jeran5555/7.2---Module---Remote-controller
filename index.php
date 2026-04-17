<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Scherm - Remote Controller</title>
    <style>
        body { 
            margin: 0; 
            overflow: hidden; 
            background: #1e1e2e; 
            color: #cdd6f4; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        #game-area { 
            width: 100vw; 
            height: 100vh; 
            position: relative; 
            background-image: radial-gradient(circle at center, #313244 0%, #1e1e2e 100%);
        }
        #character {
            width: 60px;
            height: 60px;
            background-color: #a6e3a1;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 20px #a6e3a1;
            transition: top 0.1s linear, left 0.1s linear;
        }
        .info { 
            padding: 20px; 
            position: absolute; 
            top: 0; 
            left: 0; 
            pointer-events: none;
            z-index: 10;
        }
        .qrcode-container { 
            position: absolute; 
            right: 20px; 
            top: 20px; 
            text-align: center; 
            background: rgba(255, 255, 255, 0.95); 
            padding: 15px; 
            border-radius: 12px; 
            color: #11111b; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            z-index: 10;
        }
        h1 { margin-top: 0; color: #89b4fa; }
        #status { font-weight: bold; color: #f9e2af; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="info">
        <h1>Remote Player Display</h1>
        <p>Aangesloten op Socket.IO server op poort 3000...</p>
        <div id="status">Bezig met verbinden...</div>
    </div>
    
    <div class="qrcode-container">
        <p style="margin-top:0; font-weight:bold; font-size:16px;">Scan om te spelen!</p>
        <?php
            // We halen het lokale IP of de hostname op (bijv. localhost bij XAMPP).
            $host = $_SERVER['SERVER_NAME'];
            if ($host == 'localhost' || $host == '127.0.0.1') {
                $host = getHostByName(getHostName());
            }
            $controllerUrl = "http://" . $host . "/7.2---Module---Remote-controller/controller.php";
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($controllerUrl);
        ?>
        <img src="<?= $qrUrl ?>" alt="QR Code" width="180" height="180"/>
        <br>
        <small style="display:block; margin-top: 10px; word-break:break-all; max-width:180px;"><?= htmlspecialchars($controllerUrl) ?></small>
    </div>

    <div id="game-area">
        <div id="character"></div>
    </div>

    <script src="http://<?= htmlspecialchars($host) ?>:3000/socket.io/socket.io.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusDiv = document.getElementById('status');
            const character = document.getElementById('character');
            
            // Controleer of object ge-initializeerd is
            if(typeof io === 'undefined') {
                statusDiv.innerText = 'Kan socket.io niet laden. Draait de NodeJS server (opnieuw opstarten)? Run: node server.js';
                statusDiv.style.color = '#f38ba8';
                return;
            }

            try {
                // Verbind met NodeJS op poort 3000
                const socket = io('http://<?= htmlspecialchars($host) ?>:3000');
                
                socket.on('connect', () => {
                    statusDiv.innerText = '✅ Verbonden met de Node.js server!';
                    statusDiv.style.color = '#a6e3a1';
                    socket.emit('register_display'); 
                });
                
                socket.on('disconnect', () => {
                    statusDiv.innerText = '❌ Verbinding verbroken met server!';
                    statusDiv.style.color = '#f38ba8';
                });

                // Speler positie
                let posX = window.innerWidth / 2;
                let posY = window.innerHeight / 2;

                // Snelheid van de beweging
                const speed = 12;

                socket.on('move', (data) => {
                    // Update positie
                    posX += data.x * speed;
                    posY += data.y * speed;

                    // Voorkom dat het poppetje uit het scherm rent (60px groot)
                    posX = Math.max(30, Math.min(window.innerWidth - 30, posX));
                    posY = Math.max(30, Math.min(window.innerHeight - 30, posY));

                    character.style.left = posX + 'px';
                    character.style.top = posY + 'px';
                });

            } catch (error) {
                console.error(error);
                statusDiv.innerText = 'Fout bij het verbinden...';
            }
        });
    </script>
</body>
</html>
