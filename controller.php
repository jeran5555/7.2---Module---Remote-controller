<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <!-- Meta tag voor mobiele telefoons (voorkomt inzoomen) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Controller - Remote Controller</title>
    <style>
        body {
            margin: 0;
            background: #11111b;
            color: #cdd6f4;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            touch-action: manipulation;
            user-select: none;
            -webkit-user-select: none;
        }

        h1 {
            text-align: center;
            color: #89b4fa;
            margin-bottom: 5px;
        }

        #status {
            margin-bottom: 40px;
            font-size: 16px;
            color: #f9e2af;
            font-weight: bold;
        }

        .d-pad {
            display: grid;
            grid-template-columns: repeat(3, 90px);
            grid-template-rows: repeat(3, 90px);
            gap: 15px;
            background: #1e1e2e;
            padding: 20px;
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), inset 0 2px 5px rgba(255, 255, 255, 0.1);
        }

        .btn {
            background: #313244;
            border: none;
            border-radius: 18px;
            font-size: 40px;
            color: #cdd6f4;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 6px 0 #181825, 0 10px 15px rgba(0, 0, 0, 0.4);
            transition: transform 0.05s linear, box-shadow 0.05s linear;
        }

        /* Touch en active feedback */
        .btn:active,
        .btn.active-touch {
            transform: translateY(6px);
            box-shadow: 0 0px 0 #181825, 0 2px 5px rgba(0, 0, 0, 0.4);
            background: #45475a;
            color: #a6e3a1;
        }

        .up {
            grid-column: 2;
            grid-row: 1;
        }

        .left {
            grid-column: 1;
            grid-row: 2;
        }

        .right {
            grid-column: 3;
            grid-row: 2;
        }

        .down {
            grid-column: 2;
            grid-row: 3;
        }

        .center-dot {
            grid-column: 2;
            grid-row: 2;
            background: #1e1e2e;
            border-radius: 50%;
            margin: 20px;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>

<body>

    <h1>Controller</h1>
    <div id="status">Verbinden met Node.js server...</div>

    <div class="d-pad">
        <div class="btn up" id="btn-up">▲</div>
        <div class="btn left" id="btn-left">◀</div>
        <div class="center-dot"></div>
        <div class="btn right" id="btn-right">▶</div>
        <div class="btn down" id="btn-down">▼</div>
    </div>

    <?php
    $host = $_SERVER['SERVER_NAME'];
    if ($host == 'localhost' || $host == '127.0.0.1') {
        $host = getHostByName(getHostName());
    }
    ?>
    <script src="http://<?= htmlspecialchars($host) ?>:3000/socket.io/socket.io.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusDiv = document.getElementById('status');

            if (typeof io === 'undefined') {
                statusDiv.innerText = 'Kan niet verbinden met server op poort 3000.';
                statusDiv.style.color = '#f38ba8';
                return;
            }

            try {
                const socket = io('http://<?= htmlspecialchars($host) ?>:3000');

                socket.on('connect', () => {
                    statusDiv.innerText = '🎮 Verbonden! Speel maar!';
                    statusDiv.style.color = '#a6e3a1';
                });

                socket.on('disconnect', () => {
                    statusDiv.innerText = '❌ Verbinding verbroken...';
                    statusDiv.style.color = '#f38ba8';
                });

                let input = { x: 0, y: 0 };
                let intervalId = null;

                // Deze functie stuurt herhaaldelijk de input zolang een knop wordt vastgehouden
                function sendMovement() {
                    if (input.x !== 0 || input.y !== 0) {
                        socket.emit('move', input);
                    }
                }

                // Wanneer er op een knop geduwd wordt
                function handleInputStart(x, y, btnElement) {
                    input = { x, y };
                    btnElement.classList.add('active-touch');

                    if (!intervalId) {
                        sendMovement(); // Stuur direct de eerste tik
                        intervalId = setInterval(sendMovement, 40); // 40ms interval voor soepele beweging
                    }
                }

                // Wanneer knop wordt losgelaten
                function handleInputEnd(btnElement) {
                    input = { x: 0, y: 0 };
                    btnElement.classList.remove('active-touch');

                    if (intervalId) {
                        clearInterval(intervalId);
                        intervalId = null;
                    }
                }

                // Koppel events (werkt op zowel PC met muis, als telefoons met touch)
                const setupButton = (id, x, y) => {
                    const btn = document.getElementById(id);
                    // Voorkom standaard contextmenu/zoom gedrag op mobiel
                    btn.addEventListener('contextmenu', e => e.preventDefault());

                    // Touch events
                    btn.addEventListener('touchstart', (e) => { e.preventDefault(); handleInputStart(x, y, btn); }, { passive: false });
                    btn.addEventListener('touchend', (e) => { e.preventDefault(); handleInputEnd(btn); });
                    btn.addEventListener('touchcancel', (e) => { e.preventDefault(); handleInputEnd(btn); });

                    // Muis events
                    btn.addEventListener('mousedown', (e) => { handleInputStart(x, y, btn); });
                    btn.addEventListener('mouseup', (e) => { handleInputEnd(btn); });
                    btn.addEventListener('mouseleave', (e) => { handleInputEnd(btn); });
                };

                setupButton('btn-up', 0, -1);
                setupButton('btn-down', 0, 1);
                setupButton('btn-left', -1, 0);
                setupButton('btn-right', 1, 0);

            } catch (error) {
                statusDiv.innerText = 'Fout in Socket connectie.';
            }
        });
    </script>
</body>

</html>